<?php

namespace App\Http\Controllers;

use App\Domain\Attendance\AttendanceRules;
use App\Http\Controllers\Concerns\DaftarLembur;
use App\Models\Absensi;
use App\Models\Departemen;
use App\Models\HasilKpi;
use App\Models\Karyawan;
use App\Models\KesalahanAbsensi;
use App\Models\PeriodePenilaian;
use App\Models\Pengguna;
use App\Models\RencanaLembur;
use App\Services\KpiService;
use App\Services\LaporanKpiService;
use App\Support\Format;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use DaftarLembur;

    public function index(Request $request, LaporanKpiService $laporan)
    {
        $pengguna = $request->user()->load('karyawan.departemen');

        if ($pengguna->role === Pengguna::ROLE_ADMIN) {
            return view('dashboard', $this->dataAdmin($laporan) + ['pengguna' => $pengguna]);
        }

        if ($pengguna->role === Pengguna::ROLE_GENERAL_MANAGER) {
            return view('dashboard.gm', $this->dataGm($pengguna) + ['pengguna' => $pengguna]);
        }

        if ($pengguna->role === Pengguna::ROLE_OPERASIONAL_MANAGER) {
            return view('dashboard.operasional-manager', $this->dataOperasionalManager($pengguna) + ['pengguna' => $pengguna]);
        }

        if ($pengguna->role === Pengguna::ROLE_KEPALA_PRODUKSI) {
            return view('dashboard.kepala-produksi', $this->dataKepalaProduksi($pengguna) + ['pengguna' => $pengguna]);
        }

        if ($pengguna->role === Pengguna::ROLE_KEPALA_UNIT) {
            return view('dashboard.kepala-unit', $this->kepalaUnit($pengguna, $laporan) + ['pengguna' => $pengguna]);
        }

        if ($pengguna->role === Pengguna::ROLE_KARYAWAN) {
            return view('dashboard.karyawan', $this->karyawan($pengguna) + ['pengguna' => $pengguna]);
        }

        return view('dashboard.karyawan', $this->karyawan($pengguna) + ['pengguna' => $pengguna]);
    }

    /** @return array<string,mixed> */
    private function dataGm(Pengguna $pengguna): array
    {
        $jmlMenunggu = RencanaLembur::where('status_rencana', RencanaLembur::MENUNGGU_GM)->count();
        $jmlDisetujui = RencanaLembur::whereIn('status_rencana', [RencanaLembur::DISETUJUI, RencanaLembur::SELESAI])->count();
        $jmlDitolak = RencanaLembur::where('status_rencana', RencanaLembur::DITOLAK)->count();
        $jamDisetujuiBulan = (float) $this->jamDisetujuiBulanIni();
        $labelBulanSingkat = now()->locale('id')->translatedFormat('M Y');

        $menunggu = RencanaLembur::with(['departemen', 'pengaju', 'detail'])
            ->where('status_rencana', RencanaLembur::MENUNGGU_GM)
            ->orderBy('tanggal_lembur')
            ->limit(5)
            ->get();

        $periodeKpi = $this->periodeTerbaru();
        $konfirmasi = $pengguna->karyawan ? $this->jumlahBelumKonfirmasi($pengguna->karyawan) : 0;

        return [
            'judulAntrean' => 'Menunggu keputusan',
            'labelAksi' => 'Tinjau',
            'judulPintasanLembur' => 'Persetujuan Lembur',
            'judulGrafik' => 'Jam Lembur Disetujui',
            'ruteIndex' => 'lembur.putuskan.index',
            'ruteDetail' => 'lembur.putuskan.show',
            'menunggu' => $menunggu,
            'dataGrafik' => $this->rekapJamDisetujuiPerBulan(4),
            'labelPeriodeKpi' => $periodeKpi?->nama_periode ?? 'Periode Aktif',
            'jumlahKonfirmasi' => $konfirmasi,
            'statistik' => [
                ['label' => 'Menunggu Keputusan', 'nilai' => $jmlMenunggu, 'ikon' => 'clock'],
                ['label' => 'Disetujui', 'nilai' => $jmlDisetujui, 'ikon' => 'check'],
                ['label' => 'Ditolak', 'nilai' => $jmlDitolak, 'ikon' => 'x'],
                ['label' => 'Lembur Disetujui ' . $labelBulanSingkat, 'nilai' => Format::jumlahJam($jamDisetujuiBulan), 'ikon' => 'chart'],
            ],
        ];
    }

    /** @return array<string,mixed> */
    private function dataOperasionalManager(Pengguna $pengguna): array
    {
        $sudahDiperiksa = fn () => RencanaLembur::whereHas('persetujuan', fn ($q) => $q->where('tahap', 'periksa')->where('status_persetujuan', 'Diperiksa'));

        $jmlMenunggu = RencanaLembur::where('status_rencana', RencanaLembur::MENUNGGU_OPERASIONAL)->count();
        $jmlSudahDiketahui = $sudahDiperiksa()->whereHas('persetujuan', fn ($q) => $q->where('tahap', 'ketahui'))->count();
        $jmlTotalBulanIni = $sudahDiperiksa()->whereBetween('tanggal_lembur', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->count();
        $jamBulanIni = (float) $this->jamDisetujuiBulanIni();
        $labelBulanSingkat = now()->locale('id')->translatedFormat('M Y');

        $menunggu = RencanaLembur::with(['departemen', 'pengaju', 'detail'])
            ->where('status_rencana', RencanaLembur::MENUNGGU_OPERASIONAL)
            ->orderBy('tanggal_lembur')
            ->limit(5)
            ->get();

        $periodeKpi = $this->periodeTerbaru();
        $konfirmasi = $pengguna->karyawan ? $this->jumlahBelumKonfirmasi($pengguna->karyawan) : 0;

        return [
            'judulAntrean' => 'Menunggu diketahui',
            'labelAksi' => 'Lihat',
            'judulPintasanLembur' => 'Info Rencana Lembur',
            'judulGrafik' => 'Jam Lembur per Unit',
            'ruteIndex' => 'lembur.ketahui.index',
            'ruteDetail' => 'lembur.ketahui.show',
            'menunggu' => $menunggu,
            'dataGrafik' => $this->rekapJamPerUnitBulanIni(),
            'labelPeriodeKpi' => $periodeKpi?->nama_periode ?? 'Periode Aktif',
            'jumlahKonfirmasi' => $konfirmasi,
            'statistik' => [
                ['label' => 'Menunggu Diketahui', 'nilai' => $jmlMenunggu, 'ikon' => 'clock'],
                ['label' => 'Sudah Diketahui', 'nilai' => $jmlSudahDiketahui, 'ikon' => 'check'],
                ['label' => 'Total Bulan Ini', 'nilai' => $jmlTotalBulanIni, 'ikon' => 'doc'],
                ['label' => 'Total Lembur ' . $labelBulanSingkat, 'nilai' => Format::jumlahJam($jamBulanIni), 'ikon' => 'chart'],
            ],
        ];
    }

    /** @return array<string,mixed> */
    private function dataKepalaProduksi(Pengguna $pengguna): array
    {
        $jmlMenunggu = RencanaLembur::where('status_rencana', RencanaLembur::MENUNGGU_PRODUKSI)->count();
        $jmlSudahDiperiksa = RencanaLembur::whereHas('persetujuan', fn ($q) => $q->where('tahap', 'periksa')->where('status_persetujuan', 'Diperiksa'))->count();
        $jmlDikembalikan = RencanaLembur::where('status_rencana', RencanaLembur::DIKEMBALIKAN)->count();
        $jamBulanIni = (float) $this->jamDisetujuiBulanIni();
        $labelBulanSingkat = now()->locale('id')->translatedFormat('M Y');

        $menunggu = RencanaLembur::with(['departemen', 'pengaju', 'detail'])
            ->where('status_rencana', RencanaLembur::MENUNGGU_PRODUKSI)
            ->orderBy('tanggal_lembur')
            ->limit(5)
            ->get();

        $periodeKpi = $this->periodeTerbaru();
        $konfirmasi = $pengguna->karyawan ? $this->jumlahBelumKonfirmasi($pengguna->karyawan) : 0;

        return [
            'judulAntrean' => 'Menunggu pemeriksaan',
            'labelAksi' => 'Periksa',
            'judulPintasanLembur' => 'Periksa Rencana Lembur',
            'judulGrafik' => 'Jam Lembur per Unit',
            'ruteIndex' => 'lembur.periksa.index',
            'ruteDetail' => 'lembur.periksa.show',
            'menunggu' => $menunggu,
            'dataGrafik' => $this->rekapJamPerUnitBulanIni(),
            'labelPeriodeKpi' => $periodeKpi?->nama_periode ?? 'Periode Aktif',
            'jumlahKonfirmasi' => $konfirmasi,
            'statistik' => [
                ['label' => 'Menunggu Pemeriksaan', 'nilai' => $jmlMenunggu, 'ikon' => 'clock'],
                ['label' => 'Sudah Diperiksa', 'nilai' => $jmlSudahDiperiksa, 'ikon' => 'check'],
                ['label' => 'Dikembalikan', 'nilai' => $jmlDikembalikan, 'ikon' => 'x'],
                ['label' => 'Total Lembur ' . $labelBulanSingkat, 'nilai' => Format::jumlahJam($jamBulanIni), 'ikon' => 'chart'],
            ],
        ];
    }

    /** @return array<int,array{label:string,nilai:float}> */
    private function rekapJamDisetujuiPerBulan(int $jumlahBulan = 4): array
    {
        $hasil = [];
        for ($i = $jumlahBulan - 1; $i >= 0; $i--) {
            $tgl = now()->subMonthsNoOverflow($i);
            $awal = $tgl->copy()->startOfMonth()->toDateString();
            $akhir = $tgl->copy()->endOfMonth()->toDateString();

            $daftar = RencanaLembur::with(['detail'])
                ->whereIn('status_rencana', [RencanaLembur::DISETUJUI, RencanaLembur::SELESAI])
                ->whereBetween('tanggal_lembur', [$awal, $akhir])
                ->get();

            $jam = $daftar->sum(function (RencanaLembur $r) {
                $real = method_exists($r, 'totalJamRealisasi') ? $r->totalJamRealisasi() : null;
                if ($real !== null && (float) $real > 0) {
                    return (float) $real;
                }
                return method_exists($r, 'totalJamRencana') ? (float) $r->totalJamRencana() : 0.0;
            });

            $hasil[] = [
                'label' => ucfirst((string) $tgl->locale('id')->translatedFormat('M')),
                'nilai' => round((float) $jam, 1),
            ];
        }

        return $hasil;
    }

    /** @return array<int,array{label:string,nilai:float}> */
    private function rekapJamPerUnitBulanIni(): array
    {
        $awalBulan = now()->startOfMonth()->toDateString();
        $akhirBulan = now()->endOfMonth()->toDateString();

        $lemburBulanIni = RencanaLembur::with(['departemen', 'detail'])
            ->whereBetween('tanggal_lembur', [$awalBulan, $akhirBulan])
            ->get();

        return Departemen::orderBy('nama_dept')->get()->map(function (Departemen $dept) use ($lemburBulanIni) {
            $itemDept = $lemburBulanIni->where('id_dept', $dept->id_dept);
            $totalJam = $itemDept->sum(function (RencanaLembur $r) {
                $real = method_exists($r, 'totalJamRealisasi') ? $r->totalJamRealisasi() : null;
                if ($real !== null && (float) $real > 0) {
                    return (float) $real;
                }
                return method_exists($r, 'totalJamRencana') ? (float) $r->totalJamRencana() : 0.0;
            });

            return [
                'label' => $dept->nama_dept,
                'nilai' => round((float) $totalJam, 1),
            ];
        })->values()->all();
    }

    private function dataAdmin(LaporanKpiService $laporan): array
    {
        $periode = $this->periodeTerbaru();

        $adaAbsensi = false;
        $kesalahanPeriode = 0;
        $jumlahHasil = 0;
        $semuaValid = false;
        if ($periode) {
            $awal = $periode->tanggal_mulai->toDateString();
            $akhir = $periode->tanggal_selesai->toDateString();
            $adaAbsensi = Absensi::whereBetween('tanggal_absen', [$awal, $akhir])->exists();
            $kesalahanPeriode = KesalahanAbsensi::where('status_perbaikan', '!=', 'Selesai')
                ->whereIn('id_absensi', Absensi::whereBetween('tanggal_absen', [$awal, $akhir])->select('id_absensi'))
                ->count();
            $jumlahHasil = HasilKpi::where('id_periode', $periode->id_periode)->count();
            $semuaValid = $jumlahHasil > 0
                && ! HasilKpi::where('id_periode', $periode->id_periode)->where('status_validasi', '!=', KpiService::VALIDASI_OK)->exists();
        }

        $totalKaryawan = Karyawan::where('status_karyawan', 'Aktif')->count();
        $sudahDihitung = $periode
            ? HasilKpi::where('id_periode', $periode->id_periode)
                ->whereHas('karyawan', fn ($q) => $q->where('status_karyawan', 'Aktif'))->count()
            : 0;

        $daftarKesalahan = KesalahanAbsensi::with(['absensi.karyawan', 'absensi.konfirmasi'])
            ->where('status_perbaikan', '!=', 'Selesai')
            ->orderBy(Absensi::select('tanggal_absen')->whereColumn('absensi.id_absensi', 'kesalahan_absensi.id_absensi'))
            ->limit(5)
            ->get();

        $ringkasan = $periode ? $laporan->ringkasanPeriode($periode->id_periode) : null;
        $jumlahDinilai = $ringkasan['jumlah'] ?? 0;
        $jml = fn (string $nama) => (int) ($ringkasan['sebaran'][$nama] ?? 0);
        $persen = fn (string $nama) => $jumlahDinilai > 0 ? round($jml($nama) / $jumlahDinilai * 100) : 0;

        return [
            'extra' => [
                'periode' => $periode,
                'tahapAbsensi' => $adaAbsensi,
                'tahapKonfirmasi' => $adaAbsensi && $kesalahanPeriode === 0,
                'tahapKpi' => $jumlahHasil > 0,
                'tahapValidasi' => $semuaValid,
                'tahapAkhir' => $periode?->status_periode === 'Selesai',
            ],
            'totalKaryawan' => $totalKaryawan,
            'belumDihitung' => max(0, $totalKaryawan - $sudahDihitung),
            'belumDikonfirmasi' => KesalahanAbsensi::where('status_perbaikan', '!=', 'Selesai')->distinct()->count('id_absensi'),
            'menungguValidasi' => HasilKpi::where('status_validasi', '!=', KpiService::VALIDASI_OK)
                ->when($periode, fn ($q) => $q->where('id_periode', $periode->id_periode))->count(),
            'daftarKesalahan' => $daftarKesalahan,
            'kpiSudahAda' => $jumlahDinilai > 0,
            'rataRataKpi' => $ringkasan ? Format::nilai($ringkasan['rata']) : '0,0',
            'subJudulKpi' => $periode ? $periode->nama_periode : 'Rata-rata nilai karyawan',
            'jmlSangatBaik' => $jml('Sangat Baik'), 'persenSangatBaik' => $persen('Sangat Baik'),
            'jmlBaik' => $jml('Baik'), 'persenBaik' => $persen('Baik'),
            'jmlCukup' => $jml('Cukup'), 'persenCukup' => $persen('Cukup'),
            'jmlKurang' => $jml('Kurang'), 'persenKurang' => $persen('Kurang'),
        ];
    }

    private function karyawan(Pengguna $pengguna): array
    {
        $saya = $pengguna->karyawan;
        if (! $saya) {
            return ['kartu' => [], 'tugas' => [], 'bagian' => null, 'extra' => []];
        }

        $terakhir = Absensi::where('id_karyawan', $saya->id_karyawan)->max('tanggal_absen');
        $bulan = $terakhir ? substr((string) \Carbon\Carbon::parse($terakhir)->toDateString(), 0, 7) : now()->format('Y-m');
        $awal = \Carbon\Carbon::parse($bulan . '-01');
        $status = Absensi::where('id_karyawan', $saya->id_karyawan)
            ->whereBetween('tanggal_absen', [$awal->toDateString(), $awal->copy()->endOfMonth()->toDateString()])
            ->pluck('status_kehadiran');
        $hitung = ['hadir' => 0, 'terlambat' => 0];
        foreach ($status as $s) {
            $k = AttendanceRules::kelompok($s);
            if (isset($hitung[$k])) {
                $hitung[$k]++;
            }
        }

        $hasil = HasilKpi::with('periode')->where('id_karyawan', $saya->id_karyawan)
            ->where('status_validasi', KpiService::VALIDASI_OK)->get()
            ->sortByDesc(fn (HasilKpi $h) => $h->periode->tahun * 10 + $h->periode->triwulan)->first();

        $konfirmasi = $this->jumlahBelumKonfirmasi($saya);

        $lembur = RencanaLembur::with(['pengaju', 'persetujuan.pengguna.karyawan'])
            ->whereHas('detail', fn ($q) => $q->where('id_karyawan', $saya->id_karyawan))
            ->where('status_rencana', '!=', RencanaLembur::DRAF)
            ->orderByDesc('tanggal_lembur')->first();

        return [
            'kartu' => [
                ['label' => 'Hadir (' . Format::bulan($bulan . '-01') . ')', 'nilai' => $hitung['hadir'], 'ikon' => 'checkc'],
                ['label' => 'Terlambat', 'nilai' => $hitung['terlambat'], 'ikon' => 'clock'],
                ['label' => 'Belum Dikonfirmasi', 'nilai' => $konfirmasi, 'ikon' => 'alert'],
                ['label' => 'Nilai KPI Terakhir', 'nilai' => $hasil ? Format::nilai($hasil->nilai_triwulan) : '—', 'ikon' => 'award'],
            ],
            'tugas' => [$this->tugasKonfirmasi($konfirmasi)],
            'bagian' => 'karyawan',
            'extra' => ['hasil' => $hasil, 'lembur' => $lembur],
        ];
    }

    private function kepalaUnit(Pengguna $pengguna, LaporanKpiService $laporan): array
    {
        $saya = $pengguna->karyawan;
        if (! $saya) {
            return ['kartu' => [], 'tugas' => [], 'bagian' => null, 'extra' => []];
        }

        $periode = $this->periodeTerbaru();
        $dept = $saya->id_dept;

        $belumPeriksa = $periode
            ? HasilKpi::where('id_periode', $periode->id_periode)
                ->where('status_periksa', KpiService::PERIKSA_BELUM)
                ->whereHas('karyawan', fn ($q) => $q->where('id_dept', $dept)->where('id_karyawan', '!=', $saya->id_karyawan))
                ->count()
            : 0;
        $proses = RencanaLembur::where('id_dept', $dept)
            ->whereIn('status_rencana', [RencanaLembur::MENUNGGU_PRODUKSI, RencanaLembur::MENUNGGU_OPERASIONAL, RencanaLembur::MENUNGGU_GM])->count();
        $realisasi = RencanaLembur::where('id_dept', $dept)->where('status_rencana', RencanaLembur::DISETUJUI)->count();
        $perluPerbaikan = RencanaLembur::where('id_dept', $dept)->whereIn('status_rencana', [RencanaLembur::DRAF, RencanaLembur::DIKEMBALIKAN])->count();
        $konfirmasi = $this->jumlahBelumKonfirmasi($saya);

        return [
            'kartu' => [
                ['label' => 'Karyawan Unit', 'nilai' => Karyawan::where('id_dept', $dept)->where('status_karyawan', 'Aktif')->count(), 'ikon' => 'users'],
                ['label' => 'Hadir Hari Ini', 'nilai' => Absensi::where('tanggal_absen', today()->toDateString())->whereNotNull('jam_masuk')->whereHas('karyawan', fn ($q) => $q->where('id_dept', $dept))->count(), 'ikon' => 'checkc'],
                ['label' => 'KPI Belum Diperiksa', 'nilai' => $belumPeriksa, 'ikon' => 'chart'],
                ['label' => 'Lembur Dalam Proses', 'nilai' => $proses, 'ikon' => 'clock'],
            ],
            'tugas' => [
                ['teks' => 'Hasil KPI menunggu pemeriksaan Anda', 'jumlah' => $belumPeriksa, 'url' => route('unit.kpi.pemeriksaan')],
                ['teks' => 'Realisasi lembur belum diisi', 'jumlah' => $realisasi, 'url' => route('unit.realisasi.index')],
                ['teks' => 'Rencana lembur berstatus draf atau dikembalikan', 'jumlah' => $perluPerbaikan, 'url' => route('unit.lembur.index')],
                $this->tugasKonfirmasi($konfirmasi),
            ],
            'bagian' => 'unit',
            'extra' => [
                'periode' => $periode,
                'ringkasan' => $periode ? $laporan->ringkasanPeriode($periode->id_periode, $dept) : null,
            ],
        ];
    }

    private function periodeTerbaru(): ?PeriodePenilaian
    {
        return PeriodePenilaian::orderByDesc('tahun')->orderByDesc('triwulan')->first();
    }

    private function jumlahBelumKonfirmasi(Karyawan $karyawan): int
    {
        return Absensi::where('id_karyawan', $karyawan->id_karyawan)
            ->whereHas('kesalahan', fn ($q) => $q->where('status_perbaikan', '!=', 'Selesai'))
            ->whereDoesntHave('konfirmasi')
            ->count();
    }

    /** @return array{teks:string,jumlah:int,url:string} */
    private function tugasKonfirmasi(int $jumlah): array
    {
        return ['teks' => 'Data kehadiran menunggu konfirmasi Anda', 'jumlah' => $jumlah, 'url' => route('saya.konfirmasi')];
    }
}