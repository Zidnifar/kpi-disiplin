<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · KPI Kedisiplinan</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif; }

        body.halaman-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #f4f6f9;
        }

        .login-wrapper {
            width: 860px;
            max-width: 100%;
            min-height: 500px;
            background: #fff;
            border: 1px solid #e4e7ec;
            border-radius: 20px;
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            box-shadow: 0 10px 30px rgba(16, 24, 40, .07);
            overflow: hidden;
        }

        .login-sidebar {
            background: #172b4d;
            color: #fff;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 18px;
        }
        .login-sidebar img { width: 120px; height: 120px; object-fit: contain; }
        .login-sidebar h1 { font-size: 22px; font-weight: 700; }
        .login-sidebar p { font-size: 13px; color: rgba(255, 255, 255, .7); line-height: 1.5; }

        /* sisi kanan: formulir */
        .login-main { padding: 52px 48px; display: flex; flex-direction: column; justify-content: center; }
        .logo-mobile { display: none; }
        .form-header { margin-bottom: 26px; }
        .form-header h2 { font-size: 24px; font-weight: 700; color: #101828; margin-bottom: 6px; }
        .form-header p { font-size: 13.5px; color: #667085; }

        .alert-login {
            background: #fef3f2; border: 1px solid #fecdca; color: #b42318;
            border-radius: 10px; padding: 11px 14px; font-size: 13px; margin-bottom: 18px;
        }

        .bidang { margin-bottom: 18px; }
        .bidang label { display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px; }
        .input-group { position: relative; display: flex; align-items: center; }
        .input-group .icon-left { position: absolute; left: 14px; width: 18px; height: 18px; color: #98a2b3; pointer-events: none; }
        .input-group input {
            width: 100%; height: 46px; padding: 0 44px 0 42px;
            border: 1px solid #d0d5dd; border-radius: 10px; background: #fff;
            font-size: 14px; color: #101828; outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-group input::placeholder { color: #98a2b3; }
        .input-group input:focus { border-color: #ff941c; box-shadow: 0 0 0 3px rgba(255, 148, 28, .15); }
        .input-group.salah input { border-color: #f04438; }

        .btn-toggle-password {
            position: absolute; right: 6px; width: 34px; height: 34px;
            background: transparent; border: 0; border-radius: 8px; cursor: pointer; color: #667085;
            display: grid; place-items: center;
        }
        .btn-toggle-password:hover { color: #101828; }
        .btn-toggle-password svg { width: 18px; height: 18px; }

        .tombol-masuk {
            width: 100%; height: 46px; margin-top: 6px;
            border: 0; border-radius: 10px; cursor: pointer;
            background: #ff941c; color: #fff; font-size: 14.5px; font-weight: 600;
            transition: background .2s;
        }
        .tombol-masuk:hover { background: #e58316; }
        .tombol-masuk:disabled { opacity: .75; cursor: wait; }

        @media (max-width: 800px) {
            .login-wrapper { grid-template-columns: 1fr; min-height: 0; max-width: 420px; }
            .login-sidebar { display: none; }
            .login-main { padding: 34px 26px; }
            .logo-mobile { display: block; margin-bottom: 20px; }
            .logo-mobile img { width: 64px; height: 64px; object-fit: contain; }
        }
    </style>
</head>
<body class="halaman-login">

<div class="login-wrapper">
    <aside class="login-sidebar">
        <img src="{{ asset('images/logo.png') }}" alt="Logo PT. Intanwijaya" onerror="this.remove();">
        <div>
            <h1>KPI Kedisiplinan</h1>
            <p>PT. Intanwijaya Internasional Tbk.</p>
        </div>
    </aside>

    <main class="login-main">
        <div class="logo-mobile">
            <img src="{{ asset('images/logo.png') }}" alt="Logo PT. Intanwijaya" onerror="this.remove();">
        </div>

        <div class="form-header">
            <h2>Masuk ke akun Anda</h2>
            <p>Silakan masukkan username dan password untuk melanjutkan.</p>
        </div>

        @if ($errors->any())
            <div class="alert-login" role="alert">{{ $errors->first() }}</div>
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
            </div>

            <button type="submit" class="tombol-masuk" id="tombol-masuk">Masuk</button>
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

    document.getElementById('form-login').addEventListener('submit', function () {
        var tombol = document.getElementById('tombol-masuk');
        tombol.disabled = true;
        tombol.textContent = 'Memproses...';
    });
</script>

</body>
</html>