# Sistem KPI Kedisiplinan, Absensi, dan Lembur — Tahap 1 (Laravel)

Tahap 1 berisi bagian yang paling berisiko: struktur database, model, aturan absensi, perhitungan KPI,
impor absensi, dan peran. Halaman (login, dashboard, form) dibuat di tahap berikutnya.

> **Versi:** disesuaikan untuk Laravel 13 (daftar perubahan di panduan upgrade 13.x sudah dicek; tidak ada yang mengenai kode di sini).
>
> **Status:** kode ini ditulis tanpa bisa dijalankan (lingkungan penyusun tidak punya PHP).
> Jalankan `php artisan test` dan `php artisan migrate --seed` lebih dulu dan laporkan error bila ada.

## 1. Pemasangan

Syarat: **PHP 8.3+** (syarat Laravel 13; mendukung 8.3 sampai 8.5), Composer, MySQL 8 / MariaDB 10.5+.
Skeleton Laravel 13 sudah memasang PHPUnit 12; tes di folder ini berjalan dengan versi tersebut.

```bash
composer create-project laravel/laravel kpi-disiplin
cd kpi-disiplin
composer require phpoffice/phpspreadsheet

# salin isi folder ini ke dalam proyek (timpa bila ditanya), lalu:
cp .env.example .env
php artisan key:generate
```

Atur `.env`:

```
DB_CONNECTION=mysql
DB_DATABASE=kpi_disiplin
DB_USERNAME=root
DB_PASSWORD=
APP_TIMEZONE=Asia/Makassar     # WITA; sesuaikan dengan lokasi perusahaan
ADMIN_PASSWORD=isi-password-admin
```

Hapus migrasi bawaan Laravel yang membuat tabel `users`, `cache`, `jobs` bila tidak dipakai
(`database/migrations/0001_01_01_*`), karena akun login memakai tabel `pengguna`.

Arahkan login ke model `Pengguna` di `config/auth.php`:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\Pengguna::class,
    ],
],
```

Daftarkan middleware peran di `bootstrap/app.php` (cara yang sama berlaku di Laravel 11, 12, dan 13):

```php
->withMiddleware(function (Illuminate\Foundation\Configuration\Middleware $middleware) {
    $middleware->alias(['role' => App\Http\Middleware\RoleMiddleware::class]);
})
```

Jalankan:

```bash
php artisan migrate --seed     # 19 tabel + 10 kriteria, 4 predikat, 2 shift, 1 akun admin
php artisan test               # tes aturan absensi dan perhitungan KPI
```

## 2. Isi folder

| Bagian | File |
|---|---|
| Aturan murni (tanpa database, mudah diuji) | `app/Domain/Kpi/KpiCalculator.php`, `app/Domain/Attendance/AttendanceRules.php` |
| Layanan | `app/Services/AbsensiImportService.php`, `app/Services/KpiService.php` |
| Model (19 tabel + akun login) | `app/Models/*` |
| Peran | `app/Http/Middleware/RoleMiddleware.php`, `app/Models/Pengguna.php` |
| Database | `database/migrations/*` (4 file), `database/seeders/*` |
| Tes | `tests/Unit/KpiCalculatorTest.php`, `tests/Unit/AttendanceRulesTest.php` |

## 3. Aturan yang diterapkan

**Absensi** (`AttendanceRules`)
- Terlambat: masuk lewat `jam_mulai` shift (toleransi 0). Pagi 08.00, malam 17.00.
- Pulang awal: pulang pada atau sebelum `jam_selesai` dikurangi 60 menit. Pagi 16.00, malam 19.00.
- Data kosong (tanpa jam masuk/pulang), tidak ada jadwal, atau lebih dari 2 scan sehari → status **Perlu Dicek** dan dicatat di `kesalahan_absensi`.
- Cuti, izin resmi, sakit dengan SKD: tidak mengurangi poin.
- Terlambat/pulang awal tanpa keterangan → TMK (1 poin). Alpa → A. Izin tanpa prosedur → ITMTP. Sakit tanpa SKD → STSK.

**KPI** (`KpiCalculator`, `KpiService`)
- `nilai_triwulan = 100 − Σ(frekuensi × bobot_poin)`.
- `nilai_semester = (triwulan pertama + triwulan kedua) ÷ 2` — semester 1: TW1+TW2, semester 2: TW3+TW4.
- Nilai semester dihitung otomatis setelah kedua triwulan **tervalidasi**.
- Predikat dibaca kontinu (≥90 Sangat Baik, ≥80 Baik, ≥70 Cukup, lainnya Kurang).
- Alur status: hitung → kepala unit memeriksa → admin memvalidasi (atau mengembalikan untuk dihitung ulang).

## 4. Cara mencoba (php artisan tinker)

```php
// impor absensi bulan Oktober 2026
app(App\Services\AbsensiImportService::class)
    ->import(storage_path('app/absensi-okt.xlsx'), '2026-10', $idAdmin, 'absensi-okt.xlsx');

// hitung KPI satu periode, lalu periksa dan validasi
$kpi = app(App\Services\KpiService::class);
$kpi->hitungTriwulan($idPeriode);
$kpi->periksa($idHasil, $idKepalaUnit);
$kpi->validasi($idHasil, $idAdmin);   // nilai semester terhitung bila pasangan triwulannya valid
```

## 5. Asumsi yang perlu dikonfirmasi

1. **Pengenal karyawan di file mesin.** NIP tidak wajib, jadi impor mencocokkan lewat NIP lalu nama persis.
   Disarankan menambah kolom `karyawan.id_mesin` (nomor di mesin absensi). Format kolom file juga belum dicek ke file asli.
2. **Nilai tidak negatif.** Bila total pengurang > 100, nilai dibatasi 0 (`KpiCalculator::nilaiTriwulan`).
3. **Terlambat + pulang awal di hari yang sama** dihitung dua kali (2 poin TMK).
4. **Scan tunggal** dianggap masuk atau pulang menurut jam yang lebih dekat ke jam mulai/selesai shift.
5. **Sakit tanpa SKD dan izin tanpa prosedur** dicatat lewat status absensi yang ditetapkan admin saat konfirmasi.
6. Hasil yang sudah tervalidasi **tidak ditimpa** saat hitung ulang; admin harus mengembalikannya dulu.
7. Validasi admin mensyaratkan hasil sudah diperiksa kepala unit (dapat dimatikan dengan parameter `$wajibDiperiksa = false`).

## 6. Tahap berikutnya

Login dan pembatasan peran → halaman Admin (pengguna, karyawan, shift, impor, pemeriksaan kesalahan) →
konfirmasi kehadiran → halaman KPI (hitung, periksa, validasi, laporan PDF/Excel) → alur lembur tiga tahap → dashboard.
