<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftKerja;
use App\Models\Absensi; // Sesuaikan dengan model jadwal/absensi di projectmu
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        // 1. Daftar Shift Kerja (Tabel Atas)
        $daftar = ShiftKerja::orderBy('jam_mulai')->paginate(15, ['*'], 'shift_page');

        // 2. Hitung Statistik
        $totalShift = ShiftKerja::count();
        // Contoh menghitung karyawan terjadwal hari ini (sesuaikan relasi/tabelmu jika pakai model Jadwal)
        $totalKaryawanTerjadwal = Absensi::whereDate('tanggal_absen', today())->count(); 
        
        // Menghitung jumlah shift berdasarkan nama (misal mengandung kata 'Pagi' atau 'Malam')
        $totalShiftPagi = ShiftKerja::where('nama_shift', 'like', '%pagi%')->count();
        $totalShiftMalam = ShiftKerja::where('nama_shift', 'like', '%malam%')->count();

        // 3. Data Jadwal Shift Hari Ini (Tabel Bawah, dengan filter shift jika dipilih)
        $jadwalHariIni = Absensi::with(['karyawan', 'jadwal.shift'])
            ->whereDate('tanggal_absen', today())
            ->when($request->filter_shift, function($q, $shiftId) {
                $q->whereHas('jadwal', fn($query) => $query->where('id_shift', $shiftId));
            })
            ->paginate(10, ['*'], 'jadwal_page')
            ->appends($request->all());

        return view('admin.shift.index', compact(
            'daftar', 
            'totalShift', 
            'totalKaryawanTerjadwal', 
            'totalShiftPagi', 
            'totalShiftMalam', 
            'jadwalHariIni'
        ));
    }

    public function create()
    {
        return view('admin.shift.form', ['shift' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        ShiftKerja::create($this->data($request, null));

        return redirect()->route('admin.shift.index')->with('sukses', 'Shift kerja berhasil ditambahkan.');
    }

    public function edit(ShiftKerja $shift)
    {
        return view('admin.shift.form', ['shift' => $shift]);
    }

    public function update(Request $request, ShiftKerja $shift): RedirectResponse
    {
        $shift->update($this->data($request, $shift));

        return redirect()->route('admin.shift.index')->with('sukses', 'Shift kerja berhasil diperbarui.');
    }

    /** @return array<string,mixed> */
    private function data(Request $request, ?ShiftKerja $shift): array
    {
        $unik = Rule::unique('shift_kerja', 'kode_shift');
        if ($shift) {
            $unik->ignore($shift->id_shift, 'id_shift');
        }

        $data = $request->validate([
            'kode_shift' => ['required', 'string', 'max:10', $unik],
            'nama_shift' => ['required', 'string', 'max:50'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'different:jam_mulai'],
            'toleransi_menit' => ['required', 'integer', 'min:0', 'max:240'],
            'batas_pulang_awal_menit' => ['required', 'integer', 'min:0', 'max:480'],
            'status_shift' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
        ], [
            'kode_shift.unique' => 'Kode shift sudah dipakai.',
            'jam_selesai.different' => 'Jam pulang harus berbeda dari jam masuk.',
        ]);

        $data['jam_mulai'] .= ':00';
        $data['jam_selesai'] .= ':00';

        return $data;
    }
}