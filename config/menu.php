<?php

$dashboard = ['label' => 'Dashboard', 'ikon' => 'grid', 'rute' => 'dashboard'];

$karyawan = [
    ['label' => 'Rekap Kehadiran', 'ikon' => 'calendar', 'rute' => 'saya.kehadiran'],
    ['label' => 'Konfirmasi Kehadiran', 'ikon' => 'checkc', 'rute' => 'saya.konfirmasi', 'pola' => 'saya.konfirmasi.*'],
    ['label' => 'Status Lembur', 'ikon' => 'clock', 'rute' => 'saya.lembur'],
    ['label' => 'Nilai KPI', 'ikon' => 'chart', 'rute' => 'saya.kpi'],
    ['label' => 'Riwayat KPI', 'ikon' => 'file', 'rute' => 'saya.kpi.riwayat'],
];

$saya = [
    ['label' => 'Kehadiran Saya', 'ikon' => 'calendar', 'rute' => 'saya.kehadiran'],
    ['label' => 'Konfirmasi Kehadiran', 'ikon' => 'checkc', 'rute' => 'saya.konfirmasi', 'pola' => 'saya.konfirmasi.*'],
    ['label' => 'Status Lembur Saya', 'ikon' => 'clock', 'rute' => 'saya.lembur'],
    ['label' => 'Nilai KPI Saya', 'ikon' => 'chart', 'rute' => 'saya.kpi'],
    ['label' => 'Riwayat KPI Saya', 'ikon' => 'file', 'rute' => 'saya.kpi.riwayat'],
];

$grupSaya = ['judul' => 'MENU KARYAWAN', 'item' => $saya];

return [
    'admin' => [[
        'judul' => 'MENU ADMIN',
        'item' => [
            $dashboard,
            ['label' => 'Pengguna', 'ikon' => 'users', 'rute' => 'admin.pengguna.index', 'pola' => 'admin.pengguna.*'],
            ['label' => 'Data Karyawan', 'ikon' => 'user', 'rute' => 'admin.karyawan.index', 'pola' => 'admin.karyawan.*'],
            ['label' => 'Data Absensi', 'ikon' => 'calendar', 'anak' => [
                ['label' => 'Rekap Absensi', 'rute' => 'admin.absensi.index', 'pola' => 'admin.absensi.*'],
                ['label' => 'Verifikasi Absensi', 'rute' => 'admin.verifikasi.index', 'pola' => 'admin.verifikasi.*'],
            ]],
            ['label' => 'Shift Kerja', 'ikon' => 'clock', 'rute' => 'admin.shift.index', 'pola' => 'admin.shift.*'],
            ['label' => 'Data Lembur', 'ikon' => 'clipboard', 'rute' => 'admin.lembur.index'],
            ['label' => 'Penilaian KPI', 'ikon' => 'checkc', 'rute' => 'admin.kpi.penilaian.index', 'pola' => 'admin.kpi.penilaian.*'],
            ['label' => 'Hasil KPI', 'ikon' => 'award', 'rute' => 'admin.kpi.hasil.index', 'pola' => 'admin.kpi.hasil.*'],
            ['label' => 'Pengaturan KPI', 'ikon' => 'sliders', 'anak' => [
                // halaman Indikator KPI belum dibuat: item tampil redup sampai rute ini ada
                ['label' => 'Indikator KPI', 'rute' => 'admin.kpi.indikator.index', 'pola' => 'admin.kpi.indikator.*'],
                ['label' => 'Periode KPI', 'rute' => 'admin.kpi.periode.index', 'pola' => 'admin.kpi.periode.*'],
            ]],
        ],
    ]],

    'karyawan' => [['judul' => 'MENU KARYAWAN', 'item' => array_merge([$dashboard], $karyawan)]],

    'kepala_unit' => [
        [
            'judul' => 'MENU KEPALA UNIT',
            'item' => [
                $dashboard,
                ['label' => 'Data Karyawan', 'ikon' => 'users', 'rute' => 'unit.karyawan.index', 'pola' => 'unit.karyawan.*'],
                ['label' => 'Rekap Kehadiran Unit', 'ikon' => 'calendar', 'rute' => 'unit.kehadiran'],
                ['label' => 'KPI', 'ikon' => 'chart', 'anak' => [
                    ['label' => 'Pemeriksaan KPI', 'ikon' => 'clipboard', 'rute' => 'unit.kpi.pemeriksaan', 'pola' => 'unit.kpi.pemeriksaan.*'],
                    ['label' => 'Nilai KPI', 'rute' => 'unit.kpi.nilai', 'pola' => 'unit.kpi.nilai*'],
                ]],
                ['label' => 'Lembur', 'ikon' => 'clock', 'anak' => [
                    ['label' => 'Rencana Lembur', 'rute' => 'unit.lembur.index', 'pola' => 'unit.lembur.*'],
                    ['label' => 'Realisasi Lembur', 'rute' => 'unit.realisasi.index', 'pola' => 'unit.realisasi.*'],
                ]],
            ],
        ],
        $grupSaya,
    ],

    'kepala_produksi' => [
        ['judul' => 'MENU KEPALA PRODUKSI', 'item' => [
            $dashboard,
            ['label' => 'Pemeriksaan Lembur', 'ikon' => 'clipboard', 'rute' => 'lembur.periksa.index', 'pola' => 'lembur.periksa.*'],
        ]],
        $grupSaya,
    ],

    'operasional_manager' => [
        ['judul' => 'MENU OPR. MANAGER', 'item' => [
            $dashboard,
            ['label' => 'Info Rencana Lembur', 'ikon' => 'clock', 'rute' => 'lembur.ketahui.index', 'pola' => 'lembur.ketahui.*'],
        ]],
        $grupSaya,
    ],

    'general_manager' => [
        ['judul' => 'MENU GENERAL MANAGER', 'item' => [
            $dashboard,
            ['label' => 'Persetujuan Lembur', 'ikon' => 'checkc', 'rute' => 'lembur.putuskan.index', 'pola' => 'lembur.putuskan.*'],
        ]],
        $grupSaya,
    ],
];