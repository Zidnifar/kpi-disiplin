<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Dashboard') · KPI Kedisiplinan</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/kpi.css') }}">
</head>
<body>
@php
    $pengguna = auth()->user();
    $menu = \App\Support\Menu::untuk($pengguna);
@endphp
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-logo brand-logo-gambar">
                <img src="{{ asset('images/logo.png') }}" alt="Logo PT. Intanwijaya" onerror="this.parentNode.textContent='KPI'">
            </div>
            <div class="brand-teks">
                <strong>KPI Kedisiplinan</strong>
                <span>PT. Intanwijaya Internasional Tbk.</span>
            </div>
        </div>

        @foreach ($menu as $grup)
            <div class="menu-judul">{{ $grup['judul'] }}</div>
            @foreach ($grup['item'] as $m)
                @if (isset($m['anak']))
                    <details class="menu-grup" {{ $m['buka'] ? 'open' : '' }}>
                        <summary class="menu-item menu-induk {{ $m['aktif'] ? 'induk-aktif' : '' }}">
                            <x-icon :nama="$m['ikon']" />
                            <span>{{ $m['label'] }}</span>
                            <span class="menu-panah"><x-icon nama="chev" :ukuran="16" /></span>
                        </summary>
                        <div class="submenu">
                            @foreach ($m['anak'] as $a)
                                @if ($a['url'])
                                    <a href="{{ $a['url'] }}" class="submenu-item {{ $a['aktif'] ? 'aktif' : '' }}">{{ $a['label'] }}</a>
                                @else
                                    <span class="submenu-item nonaktif" title="Segera tersedia">{{ $a['label'] }}</span>
                                @endif
                            @endforeach
                        </div>
                    </details>
                @elseif ($m['url'])
                    <a href="{{ $m['url'] }}" class="menu-item {{ $m['aktif'] ? 'aktif' : '' }}">
                        <x-icon :nama="$m['ikon']" />
                        <span>{{ $m['label'] }}</span>
                    </a>
                @else
                    <span class="menu-item nonaktif" title="Segera tersedia">
                        <x-icon :nama="$m['ikon']" />
                        <span>{{ $m['label'] }}</span>
                    </span>
                @endif
            @endforeach
        @endforeach
    </aside>

    <div class="main">
        <header class="topbar">
            <h1>@yield('judul', 'Dashboard')</h1>
            <div class="topbar-kanan">
                <button type="button" class="ikon-tombol" aria-label="Notifikasi"><x-icon nama="bell" :ukuran="22" /></button>

                <div class="dropdown">
                    <button type="button" class="pengguna-tombol" data-dropdown-tombol aria-haspopup="true">
                        <span class="avatar"><x-icon nama="user" :ukuran="22" /></span>
                        <span class="pengguna-info">
                            <strong>{{ $pengguna->namaTampil() }}</strong>
                            <span>{{ $pengguna->namaPeran() }}</span>
                        </span>
                        <x-icon nama="chev" :ukuran="16" class="panah" />
                    </button>
                    <div class="dropdown-isi">
                        <a href="{{ route('profil') }}"><x-icon nama="user" :ukuran="18" /> Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"><x-icon nama="out" :ukuran="18" /> Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="konten">
            @if (session('sukses'))
                <div class="alert alert-sukses">{{ session('sukses') }}</div>
            @endif
            @if (session('peringatan'))
                <div class="alert alert-peringatan">{{ session('peringatan') }}</div>
            @endif
            @if (session('galat'))
                <div class="alert alert-galat">{{ session('galat') }}</div>
            @endif
            @yield('isi')
        </main>
    </div>
</div>
<script src="{{ asset('js/kpi.js') }}"></script>
@stack('skrip')
</body>
</html>