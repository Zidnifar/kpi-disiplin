<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Attendance\AttendanceRules as R;
use PHPUnit\Framework\TestCase;

class AttendanceRulesTest extends TestCase
{
    private function pagi(?string $masuk, ?string $pulang): array
    {
        return R::evaluasi($masuk, $pulang, '08:00:00', '17:00:00');
    }

    private function malam(?string $masuk, ?string $pulang): array
    {
        return R::evaluasi($masuk, $pulang, '17:00:00', '20:00:00');
    }

    public function test_pagi_tepat_waktu_hadir(): void
    {
        $this->assertSame(R::HADIR, $this->pagi('08:00:00', '17:00:00')['status']);
        $this->assertSame(R::HADIR, $this->pagi('07:45:00', '17:05:00')['status']);
    }

    public function test_pagi_lewat_jam_8_terlambat(): void
    {
        $this->assertSame(R::TERLAMBAT, $this->pagi('08:00:01', '17:00:00')['status']);
        $this->assertSame(R::TERLAMBAT, $this->pagi('08:21:00', '17:05:00')['status']);
    }

    public function test_pagi_pulang_satu_jam_lebih_awal(): void
    {
        $this->assertSame(R::PULANG_AWAL, $this->pagi('07:50:00', '16:00:00')['status']);
        $this->assertSame(R::PULANG_AWAL, $this->pagi('07:50:00', '15:30:00')['status']);
        $this->assertSame(R::HADIR, $this->pagi('07:50:00', '16:00:01')['status']);
    }

    public function test_terlambat_dan_pulang_awal(): void
    {
        $this->assertSame(R::TERLAMBAT_PULANG_AWAL, $this->pagi('08:30:00', '15:00:00')['status']);
    }

    public function test_shift_malam_17_sampai_20(): void
    {
        $this->assertSame(R::HADIR, $this->malam('16:58:00', '20:03:00')['status']);
        $this->assertSame(R::HADIR, $this->malam('17:00:00', '20:00:00')['status']);
        $this->assertSame(R::TERLAMBAT, $this->malam('17:05:00', '20:00:00')['status']);
        $this->assertSame(R::PULANG_AWAL, $this->malam('16:55:00', '19:00:00')['status']);
    }

    public function test_data_kosong_perlu_dicek(): void
    {
        $hasil = $this->pagi('07:50:00', null);
        $this->assertSame(R::PERLU_DICEK, $hasil['status']);
        $this->assertContains('Jam pulang kosong', $hasil['masalah']);

        $hasil = $this->pagi(null, '17:02:00');
        $this->assertSame(R::PERLU_DICEK, $hasil['status']);
        $this->assertContains('Jam masuk kosong', $hasil['masalah']);
    }

    public function test_toleransi_menit_dihormati(): void
    {
        $this->assertSame(
            R::HADIR,
            R::evaluasi('08:10:00', '17:00:00', '08:00:00', '17:00:00', 15, 60)['status']
        );
    }

    public function test_pelanggaran_dari_status(): void
    {
        $this->assertSame(['TMK' => 1], R::pelanggaranDariStatus(R::TERLAMBAT));
        $this->assertSame(['TMK' => 1], R::pelanggaranDariStatus(R::PULANG_AWAL));
        $this->assertSame(['TMK' => 2], R::pelanggaranDariStatus(R::TERLAMBAT_PULANG_AWAL));
        $this->assertSame(['A' => 1], R::pelanggaranDariStatus(R::ALPA));
        $this->assertSame(['ITMTP' => 1], R::pelanggaranDariStatus(R::IZIN_TANPA_PROSEDUR));
        $this->assertSame(['STSK' => 1], R::pelanggaranDariStatus(R::SAKIT_TANPA_SKD));
    }

    public function test_cuti_izin_resmi_sakit_skd_tidak_dihitung(): void
    {
        foreach ([R::CUTI, R::IZIN_RESMI, R::SAKIT_SKD, R::HADIR, R::PERLU_DICEK] as $status) {
            $this->assertSame([], R::pelanggaranDariStatus($status), $status);
        }
    }
}
