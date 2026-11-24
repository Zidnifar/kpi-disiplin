<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Models\Pengguna;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PenggunaController extends Controller
{
    public const PERAN = [
        Pengguna::ROLE_ADMIN => 'Administrator',
        Pengguna::ROLE_KARYAWAN => 'Karyawan',
        Pengguna::ROLE_KEPALA_UNIT => 'Kepala Unit',
        Pengguna::ROLE_KEPALA_PRODUKSI => 'Kepala Produksi',
        Pengguna::ROLE_OPERASIONAL_MANAGER => 'Operasional Manager',
        Pengguna::ROLE_GENERAL_MANAGER => 'General Manager',
    ];

    public function index(Request $request)
    {
        $daftar = Pengguna::with('karyawan')
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = Format::like((string) $request->input('q'));
                $q->where(fn ($w) => $w->where('username', 'like', $kata)
                    ->orWhereHas('karyawan', fn ($k) => $k->where('nama_karyawan', 'like', $kata)));
            })
            ->when($request->filled('peran'), fn ($q) => $q->where('role', $request->input('peran')))
            ->when($request->filled('status'), fn ($q) => $q->where('status_akun', $request->input('status')))
            ->orderBy('username')
            ->paginate(15)
            ->withQueryString();

        $ringkasan = [
            'aktif' => Pengguna::where('status_akun', 'Aktif')->count(),
            'nonaktif' => Pengguna::where('status_akun', '!=', 'Aktif')->count(),
            'peran' => Pengguna::query()->distinct()->count('role'),
            'total' => Pengguna::count(),
        ];

        return view('admin.pengguna.index', ['daftar' => $daftar, 'ringkasan' => $ringkasan, 'peran' => self::PERAN]);
    }

    public function create()
    {
        return view('admin.pengguna.form', [
            'pengguna' => null,
            'peran' => self::PERAN,
            'karyawanPilihan' => $this->karyawanTanpaAkun(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->aturan(null), $this->pesan());
        if ($data['peran'] === Pengguna::ROLE_ADMIN) {
            $data['id_karyawan'] = null;
        }

        $pengguna = Pengguna::create([
            'id_karyawan' => $data['id_karyawan'] ?? null,
            'username' => $data['username'],
            'password' => Hash::make($data['password']), // Di-hash agar aman dan terbaca saat login
            'role' => $data['peran'],
            'status_akun' => $data['status_akun'],
        ]);
        $this->tetapkanKepalaUnit($pengguna);

        return redirect()->route('admin.pengguna.index')->with('sukses', 'Akun pengguna berhasil ditambahkan.');
    }

    public function edit(Pengguna $pengguna)
    {
        return view('admin.pengguna.form', [
            'pengguna' => $pengguna->load('karyawan'),
            'peran' => self::PERAN,
            'karyawanPilihan' => $this->karyawanTanpaAkun($pengguna),
        ]);
    }

    public function update(Request $request, Pengguna $pengguna): RedirectResponse
    {
        $data = $request->validate($this->aturan($pengguna), $this->pesan());
        if ($data['peran'] === Pengguna::ROLE_ADMIN) {
            $data['id_karyawan'] = null;
        }

        $diriSendiri = $pengguna->id_pengguna === $request->user()->id_pengguna;
        if ($diriSendiri && ($data['peran'] !== Pengguna::ROLE_ADMIN || $data['status_akun'] !== 'Aktif')) {
            return back()->withInput()->with('galat', 'Anda tidak dapat mengubah peran atau menonaktifkan akun Anda sendiri.');
        }

        $ubah = [
            'id_karyawan' => $data['id_karyawan'] ?? null,
            'username' => $data['username'],
            'role' => $data['peran'],
            'status_akun' => $data['status_akun'],
        ];
        
        if (! empty($data['password'])) {
            $ubah['password'] = Hash::make($data['password']); // Di-hash jika diisi
        }
        
        $pengguna->update($ubah);
        $this->tetapkanKepalaUnit($pengguna);

        return redirect()->route('admin.pengguna.index')->with('sukses', 'Akun pengguna berhasil diperbarui.');
    }

    public function destroy(Pengguna $pengguna): RedirectResponse
    {
        if ($pengguna->id_pengguna === request()->user()->id_pengguna) {
            return redirect()->route('admin.pengguna.index')->with('galat', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $pengguna->delete();

        return redirect()->route('admin.pengguna.index')->with('sukses', 'Akun pengguna berhasil dihapus.');
    }

    /** @return array<string,mixed> */
    private function aturan(?Pengguna $pengguna): array
    {
        $unikKaryawan = Rule::unique('pengguna', 'id_karyawan');
        $unikUsername = Rule::unique('pengguna', 'username');
        if ($pengguna) {
            $unikKaryawan->ignore($pengguna->id_pengguna, 'id_pengguna');
            $unikUsername->ignore($pengguna->id_pengguna, 'id_pengguna');
        }

        return [
            'peran' => ['required', Rule::in(array_keys(self::PERAN))],
            'id_karyawan' => ['nullable', 'required_unless:peran,' . Pengguna::ROLE_ADMIN, Rule::exists('karyawan', 'id_karyawan'), $unikKaryawan],
            'username' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', $unikUsername],
            'password' => [$pengguna ? 'nullable' : 'required', Password::min(8)->mixedCase()->numbers()],
            'status_akun' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
        ];
    }

    /** @return array<string,string> */
    private function pesan(): array
    {
        return [
            'id_karyawan.required_unless' => 'Pilih karyawan pemilik akun ini.',
            'id_karyawan.unique' => 'Karyawan ini sudah memiliki akun.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.',
            'username.unique' => 'Username sudah dipakai.',
        ];
    }

    private function karyawanTanpaAkun(?Pengguna $pengguna = null)
    {
        return Karyawan::with('departemen')
            ->where('status_karyawan', 'Aktif')
            ->where(function ($q) use ($pengguna) {
                $q->whereDoesntHave('pengguna');
                if ($pengguna?->id_karyawan) {
                    $q->orWhere('id_karyawan', $pengguna->id_karyawan);
                }
            })
            ->orderBy('nama_karyawan')
            ->get();
    }

    private function tetapkanKepalaUnit(Pengguna $pengguna): void
    {
        if ($pengguna->role !== Pengguna::ROLE_KEPALA_UNIT || ! $pengguna->id_karyawan) {
            return;
        }
        $karyawan = Karyawan::find($pengguna->id_karyawan);
        if ($karyawan) {
            Departemen::where('id_dept', $karyawan->id_dept)
                ->whereNull('id_kepala_unit')
                ->update(['id_kepala_unit' => $karyawan->id_karyawan]);
        }
    }
}