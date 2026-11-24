<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Laporan') · KPI Kedisiplinan</title>
    <link rel="stylesheet" href="{{ asset('css/kpi.css') }}">
</head>
<body class="halaman-cetak">
    <div class="tanpa-cetak" style="display:flex; gap:10px; margin-bottom:20px;">
        <button type="button" class="tombol tombol-utama" data-cetak><x-icon nama="print" :ukuran="18" /> Cetak / Simpan PDF</button>
        <a href="javascript:history.back()" class="tombol tombol-sekunder">Kembali</a>
    </div>
    @yield('isi')
    <script src="{{ asset('js/kpi.js') }}"></script>
</body>
</html>
