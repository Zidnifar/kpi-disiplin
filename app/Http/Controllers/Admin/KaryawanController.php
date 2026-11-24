<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Departemen;
use App\Models\DetailPenilaianKpi;
use App\Models\HasilKpi;
use App\Models\Karyawan;
use App\Models\KesalahanAbsensi;
use App\Models\NilaiSemester;
use App\Models\PelanggaranKaryawan;
use App\Support\Format;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    private const JABATAN_UMUM = ['Operator', 'Staff', 'Administrasi', 'Kepala Unit', 'Kepala Produksi', 'Operasional Manager', 'General Manager'];

    public function index(Request $request)
    {
        $daftar = Karyawan::with('departemen')
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = Format::like((string) $request->input('q'));
                $q->where(fn ($w) => $w->where('nama_karyawan', 'like', $kata)
                    ->orWhere('nip', 'like', $kata)
                    ->orWhereHas('departemen', fn ($d) => $d->where('nama_dept', 'like', $kata)));
            })
            ->when($request->filled('dept'), fn ($q) => $q->where('id_dept', $request->input('dept')))
            ->when($request->filled('status'), fn ($q) => $q->where('status_karyawan', $request->input('status')))
            ->orderBy('nama_karyawan')
            ->paginate(15)
            ->withQueryString();

        $ringkasan = [
            'total' => Karyawan::count(),
            'aktif' => Karyawan::where('status_karyawan', 'Aktif')->count(),
            'nonaktif' => Karyawan::where('status_karyawan', '!=', 'Aktif')->count(),
            'departemen' => Departemen::count(),
        ];

        return view('admin.karyawan.index', [
            'daftar' => $daftar,
            'ringkasan' => $ringkasan,
            'departemen' => Departemen::orderBy('nama_dept')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.karyawan.form', $this->dataForm(null));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->aturan(null), $this->pesan());
        Karyawan::create($data);

        return redirect()->route('admin.karyawan.index')->with('sukses', 'Data karyawan berhasil ditambahkan.');
    }

    public function edit(Karyawan $karyawan)
    {
        return view('admin.karyawan.form', $this->dataForm($karyawan));
    }

    public function update(Request $request, Karyawan $karyawan): RedirectResponse
    {
        $data = $request->validate($this->aturan($karyawan), $this->pesan());
        $karyawan->update($data);

        // karyawan nonaktif tidak boleh masuk ke sistem
        if (isset($data['status_karyawan']) && $data['status_karyawan'] !== 'Aktif') {
            if ($karyawan->pengguna) {
                $karyawan->pengguna()->update(['status_akun' => 'Nonaktif']);
            }
        }

        return redirect()->route('admin.karyawan.index')->with('sukses', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Menghapus data karyawan.
     *
     * Karyawan yang sudah punya absensi/KPI hanya dihapus bila admin memilih "hapus beserta data"
     * (field hapus_beserta_data = 1). Semua proses dibungkus transaction supaya batal bersama bila gagal.
     */
    public function destroy(Request $request, Karyawan $karyawan): RedirectResponse
    {
        $id = $karyawan->id_karyawan;
        $paksa = $request->boolean('hapus_beserta_data');

        $terpakai = Absensi::where('id_karyawan', $id)->exists()
            || HasilKpi::where('id_karyawan', $id)->exists()
            || PelanggaranKaryawan::where('id_karyawan', $id)->exists();

        if ($terpakai && ! $paksa) {
            return redirect()->route('admin.karyawan.index')
                ->with('galat', 'Karyawan masih punya data absensi atau KPI. Ubah statusnya menjadi Nonaktif, atau hapus beserta datanya.');
        }

        try {
            DB::transaction(function () use ($karyawan, $id, $paksa) {
                if ($paksa) {
                    $idHasil = HasilKpi::where('id_karyawan', $id)->pluck('id_hasil');
                    $idAbsensi = Absensi::where('id_karyawan', $id)->pluck('id_absensi');

                    NilaiSemester::where('id_karyawan', $id)->delete();
                    DetailPenilaianKpi::whereIn('id_hasil', $idHasil)->delete();
                    HasilKpi::where('id_karyawan', $id)->delete();
                    PelanggaranKaryawan::where('id_karyawan', $id)->delete();
                    KesalahanAbsensi::whereIn('id_absensi', $idAbsensi)->delete();
                    Absensi::where('id_karyawan', $id)->delete();
                }

                // akun yang pernah memeriksa/memvalidasi/mencatat data orang lain: lepaskan dulu
                if ($akun = $karyawan->pengguna) {
                    HasilKpi::where('id_pemeriksa', $akun->id_pengguna)->update(['id_pemeriksa' => null]);
                    HasilKpi::where('id_pemvalidasi', $akun->id_pengguna)->update(['id_pemvalidasi' => null]);
                    PelanggaranKaryawan::where('id_pencatat', $akun->id_pengguna)->update(['id_pencatat' => null]);
                    $akun->delete();
                }

                $karyawan->delete();
            });
        } catch (QueryException $e) {
            report($e); // detailnya ada di storage/logs/laravel.log

            return redirect()->route('admin.karyawan.index')
                ->with('galat', 'Data karyawan gagal dihapus karena masih terikat dengan data lain.');
        }

        return redirect()->route('admin.karyawan.index')->with('sukses', 'Data karyawan berhasil dihapus.');
    }

    /** @return array<string,mixed> */
    private function dataForm(?Karyawan $karyawan): array
    {
        $jabatan = Karyawan::query()->distinct()->pluck('jabatan')->merge(self::JABATAN_UMUM)->unique()->sort()->values();

        return [
            'karyawan' => $karyawan,
            'departemen' => Departemen::orderBy('nama_dept')->get(),
            'jabatan' => $jabatan,
        ];
    }

    /** @return array<string,mixed> */
    private function aturan(?Karyawan $karyawan): array
    {
        $unikNip = Rule::unique('karyawan', 'nip');
        if ($karyawan) {
            $unikNip->ignore($karyawan->id_karyawan, 'id_karyawan');
        }

        return [
            'nip' => ['nullable', 'string', 'max:20', $unikNip],
            'nama_karyawan' => ['required', 'string', 'max:100'],
            'jabatan' => ['required', 'string', 'max:100'],
            'id_dept' => ['required', Rule::exists('departemen', 'id_dept')],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'status_karyawan' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email', 'max:100'],
        ];
    }

    /** @return array<string,string> */
    private function pesan(): array
    {
        return ['nip.unique' => 'NIP sudah dipakai karyawan lain.'];
    }
}