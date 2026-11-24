<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · KPI Kedisiplinan</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif; }

        body.halaman-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 148, 28, .16) 0, transparent 38%),
                radial-gradient(circle at 88% 85%, rgba(23, 43, 77, .14) 0, transparent 42%),
                #f4f6f9;
        }
        /* titik-titik halus sebagai tekstur latar */
        body.halaman-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(23, 43, 77, .09) 1.2px, transparent 1.2px);
            background-size: 26px 26px;
            -webkit-mask-image: radial-gradient(circle at center, #000 0, transparent 75%);
            mask-image: radial-gradient(circle at center, #000 0, transparent 75%);
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 980px;
            max-width: 100%;
            min-height: 590px;
            background: #fff;
            border-radius: 28px;
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            box-shadow: 0 30px 70px rgba(16, 24, 40, .16), 0 2px 6px rgba(16, 24, 40, .06);
            overflow: hidden;
            animation: muncul .5s ease both;
        }
        @keyframes muncul { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

        /* ---------- sisi kiri: identitas ---------- */
        .login-sidebar {
            position: relative;
            padding: 44px 42px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #fff;
            background: linear-gradient(155deg, #1d3560 0%, #172b4d 45%, #0e1c38 100%);
            overflow: hidden;
        }
        .login-sidebar::before, .login-sidebar::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }
        .login-sidebar::before { width: 320px; height: 320px; right: -110px; top: -110px; background: rgba(255, 148, 28, .22); }
        .login-sidebar::after { width: 240px; height: 240px; left: -90px; bottom: -80px; border: 38px solid rgba(255, 148, 28, .14); }
        .login-sidebar > * { position: relative; z-index: 1; }

        .brand-blok { display: flex; align-items: center; gap: 14px; }
        .logo-kartu {
            width: 68px; height: 68px; flex: none;
            background: #fff; border-radius: 18px; padding: 9px;
            display: grid; place-items: center;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .22);
            font-weight: 800; color: #ff941c; font-size: 20px;
        }
        .logo-kartu img { width: 100%; height: 100%; object-fit: contain; }
        .brand-blok h1 { font-size: 21px; font-weight: 800; letter-spacing: -.2px; }
        .brand-blok p { font-size: 12px; color: rgba(255, 255, 255, .72); margin-top: 3px; }

        .sapaan h2 { font-size: 30px; line-height: 1.25; font-weight: 800; letter-spacing: -.5px; margin-bottom: 12px; }
        .sapaan h2 span { color: #ff941c; }
        .sapaan p { font-size: 14px; line-height: 1.6; color: rgba(255, 255, 255, .78); max-width: 340px; }

        .fitur { list-style: none; display: flex; flex-direction: column; gap: 14px; margin-top: 26px; }
        .fitur li { display: flex; align-items: center; gap: 12px; font-size: 13.5px; font-weight: 500; color: rgba(255, 255, 255, .92); }
        .fitur .ikon {
            width: 34px; height: 34px; flex: none; border-radius: 10px;
            background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .14);
            display: grid; place-items: center; color: #ffb25a;
        }
        .fitur svg { width: 17px; height: 17px; }

        .sidebar-kaki { font-size: 11.5px; color: rgba(255, 255, 255, .55); }

        /* ---------- sisi kanan: formulir ---------- */
        .login-main { padding: 56px 52px; display: flex; flex-direction: column; justify-content: center; }
        .logo-mobile { display: none; }
        .form-header { margin-bottom: 26px; }
        .form-header .salam {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 700; color: #c26a00; background: rgba(255, 148, 28, .13);
            padding: 5px 12px; border-radius: 999px; margin-bottom: 14px;
        }
        .form-header h2 { font-size: 26px; font-weight: 800; color: #101828; letter-spacing: -.4px; margin-bottom: 8px; }
        .form-header p { font-size: 13.5px; color: #667085; line-height: 1.55; }

        .alert-login {
            display: flex; align-items: flex-start; gap: 10px;
            background: #fef3f2; border: 1px solid #fecdca; color: #b42318;
            border-radius: 12px; padding: 12px 14px; font-size: 13px; font-weight: 500; margin-bottom: 18px;
        }
        .alert-login svg { width: 18px; height: 18px; flex: none; margin-top: 1px; }

        .bidang { margin-bottom: 18px; }
        .bidang label { display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px; }
        .input-group { position: relative; display: flex; align-items: center; }
        .input-group .icon-left { position: absolute; left: 15px; width: 18px; height: 18px; color: #98a2b3; pointer-events: none; transition: color .2s; }
        .input-group input {
            width: 100%; height: 48px; padding: 0 46px 0 44px;
            border: 1.5px solid #e1e7ef; border-radius: 12px; background: #f9fafb;
            font-size: 14px; color: #101828; outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .input-group input::placeholder { color: #98a2b3; }
        .input-group input:focus { background: #fff; border-color: #ff941c; box-shadow: 0 0 0 4px rgba(255, 148, 28, .14); }
        .input-group:focus-within .icon-left { color: #ff941c; }
        .input-group.salah input { border-color: #f04438; background: #fffbfa; }

        .btn-toggle-password {
            position: absolute; right: 8px; width: 34px; height: 34px;
            background: transparent; border: 0; border-radius: 8px; cursor: pointer; color: #667085;
            display: grid; place-items: center;
        }
        .btn-toggle-password:hover { background: #eef2f6; color: #101828; }
        .btn-toggle-password svg { width: 18px; height: 18px; }

        .petunjuk { font-size: 12px; margin-top: 6px; color: #b54708; display: none; align-items: center; gap: 6px; }
        .petunjuk.tampil { display: flex; }
        .galat { font-size: 12px; color: #d92d20; margin-top: 6px; }

        .tombol-masuk {
            width: 100%; height: 48px; margin-top: 6px;
            border: 0; border-radius: 12px; cursor: pointer;
            background: linear-gradient(180deg, #ffa53d 0%, #ff941c 100%); color: #fff;
            font-size: 14.5px; font-weight: 700;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 8px 18px rgba(255, 148, 28, .32);
            transition: transform .15s, box-shadow .2s, filter .2s;
        }
        .tombol-masuk:hover { filter: brightness(.96); box-shadow: 0 10px 22px rgba(255, 148, 28, .4); }
        .tombol-masuk:active { transform: translateY(1px); }
        .tombol-masuk:disabled { opacity: .75; cursor: wait; }
        .putar { width: 16px; height: 16px; border: 2.5px solid rgba(255, 255, 255, .4); border-top-color: #fff; border-radius: 50%; animation: putar .7s linear infinite; display: none; }
        .tombol-masuk.memuat .putar { display: inline-block; }
        @keyframes putar { to { transform: rotate(360deg); } }

        .form-footer-info {
            margin-top: 22px; padding-top: 18px; border-top: 1px solid #eef2f6;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            font-size: 12px; color: #667085;
        }
        .form-footer-info svg { width: 14px; height: 14px; color: #98a2b3; }

        @media (max-width: 860px) {
            .login-wrapper { grid-template-columns: 1fr; min-height: 0; max-width: 460px; }
            .login-sidebar { display: none; }
            .login-main { padding: 36px 26px; }
            .logo-mobile { display: flex; align-items: center; gap: 12px; margin-bottom: 22px; }
            .logo-mobile .logo-kartu { width: 52px; height: 52px; border-radius: 14px; padding: 7px; box-shadow: 0 4px 12px rgba(16, 24, 40, .12); border: 1px solid #eef2f6; }
            .logo-mobile strong { display: block; font-size: 16px; color: #101828; }
            .logo-mobile span { font-size: 11.5px; color: #667085; }
        }
    </style>
</head>
<body class="halaman-login">

<div class="login-wrapper">
    <!-- Sisi kiri: logo, sapaan, dan keunggulan sistem -->
    <aside class="login-sidebar">
        <div class="brand-blok">
            <div class="logo-kartu">
                <img src="{{ asset('images/logo.png') }}" alt="Logo PT. Intanwijaya" onerror="this.remove();">
            </div>
            <div>
                <h1>KPI Kedisiplinan</h1>
                <p>PT. Intanwijaya Internasional Tbk.</p>
            </div>
        </div>

        <div class="sapaan">
            <h2>Disiplin terukur,<br><span>kinerja makin maju.</span></h2>
            <p>Pantau kehadiran, nilai KPI, dan pengajuan lembur dalam satu sistem yang rapi dan transparan.</p>
            <ul class="fitur">
                <li>
                    <span class="ikon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
                    Rekap absensi dan konfirmasi kehadiran
                </li>
                <li>
                    <span class="ikon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-6"/></svg></span>
                    Penilaian KPI per triwulan dan semester
                </li>
                <li>
                    <span class="ikon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
                    Pengajuan dan persetujuan lembur
                </li>
            </ul>
        </div>

        <div class="sidebar-kaki">&copy; {{ date('Y') }} PT. Intanwijaya Internasional Tbk.</div>
    </aside>

    <!-- Sisi kanan: formulir -->
    <main class="login-main">
        <div class="logo-mobile">
            <div class="logo-kartu">
                <img src="{{ asset('images/logo.png') }}" alt="Logo PT. Intanwijaya" onerror="this.remove();">
            </div>
            <div><strong>KPI Kedisiplinan</strong><span>PT. Intanwijaya Internasional Tbk.</span></div>
        </div>

        <div class="form-header">
            <span class="salam">&#128075; Selamat datang kembali</span>
            <h2>Masuk ke akun Anda</h2>
            <p>Silakan masukkan username dan password untuk melanjutkan.</p>
        </div>

        @if ($errors->any())
            <div class="alert-login" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.proses') }}" id="form-login" novalidate>
            @csrf

            <div class="bidang">
                <label for="username">Username</label>
                <div class="input-group {{ $errors->has('username') ? 'salah' : '' }}">
                    <svg class="icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" autofocus required>
                </div>
            </div>

            <div class="bidang">
                <label for="password">Password</label>
                <div class="input-group {{ $errors->has('password') ? 'salah' : '' }}">
                    <svg class="icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input id="password" name="password" type="password" placeholder="Masukkan password" autocomplete="current-password" required>
                    <button type="button" class="btn-toggle-password" onclick="togglePassword()" title="Tampilkan/Sembunyikan password" aria-label="Tampilkan atau sembunyikan password">
                        <svg id="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                <div class="petunjuk" id="petunjuk-caps">&#9888; Caps Lock sedang aktif</div>
            </div>

            <button type="submit" class="tombol-masuk" id="tombol-masuk">
                <span class="putar"></span>
                <span id="teks-masuk">Masuk</span>
            </button>

            <div class="form-footer-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Akses hanya untuk pengguna yang terdaftar
            </div>
        </form>
    </main>
</div>

<script>
    function togglePassword() {
        var input = document.getElementById('password');
        var icon = document.getElementById('icon-eye');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    }

    // peringatan Caps Lock saat mengetik password
    document.getElementById('password').addEventListener('keyup', function (e) {
        document.getElementById('petunjuk-caps').classList.toggle('tampil', e.getModifierState && e.getModifierState('CapsLock'));
    });

    // tombol menampilkan "Memproses..." dan dikunci agar tidak terkirim dua kali
    document.getElementById('form-login').addEventListener('submit', function () {
        var tombol = document.getElementById('tombol-masuk');
        tombol.classList.add('memuat');
        tombol.disabled = true;
        document.getElementById('teks-masuk').textContent = 'Memproses...';
    });
</script>

</body>
</html>