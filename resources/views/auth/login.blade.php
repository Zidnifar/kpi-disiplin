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
        :root {
            --navy: #172b4d;
            --biru: #2f5fa8;
            --biru-muda: #eaf1fb;
            --oranye: #ff941c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif; }

        body.halaman-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(160deg, #eef3fb 0%, #f6f8fc 55%, #fdf3e6 100%);
        }

        /* kotak login dibuat ringkas */
        .login-wrapper {
            width: 720px;
            max-width: 100%;
            background: #fff;
            border: 1px solid #dfe6f2;
            border-radius: 18px;
            display: grid;
            grid-template-columns: 1fr 1.05fr;
            box-shadow: 0 14px 36px rgba(23, 43, 77, .10);
            overflow: hidden;
        }

        /* sisi kiri: biru muda, logo tanpa latar putih */
        .login-sidebar {
            position: relative;
            padding: 36px 32px;
            background: linear-gradient(165deg, #dfeafb 0%, #eef4fc 60%, #f8fafe 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 28px;
            overflow: hidden;
        }
        .login-sidebar::before, .login-sidebar::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }
        .login-sidebar::before { 
            width: 190px; 
            height: 190px; 
            left: -70px; 
            bottom: -70px; 
            background: rgba(47, 95, 168, .10); 
        }
        .login-sidebar::after { 
            width: 120px; 
            height: 120px; 
            right: -40px; 
            top: -40px; 
            background: rgba(255, 148, 28, .12); 
        }
        .login-sidebar > * { 
            position: relative; 
            z-index: 1; 
        }

        .brand img { 
            width: 92px; 
            height: 92px; 
            object-fit: contain; 
            display: block; 
            margin-bottom: 16px; 
        }
        .brand h1 { 
            font-size: 22px; 
            font-weight: 800; 
            color: var(--navy); 
            letter-spacing: -.3px; 
        }
        .brand p { 
            font-size: 14px; 
            color: var(--biru); 
            font-weight: 500; 
            margin-top: 3px; 
        }
        .tagline { 
            font-size: 12.5px; 
            line-height: 1.55; 
            color: #5b6b86; 
            max-width: 210px; 
        }

        /* sisi kanan: formulir */
        .login-main { 
            padding: 38px 36px; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            gap: 12px; 
        }
        .logo-mobile { 
            display: none; 
        }
        .form-header { 
            margin-bottom: 20px; 
        }
        .form-header h2 { 
            font-size: 19px; 
            font-weight: 800; 
            color: var(--navy); 
            margin-bottom: 5px; 
        }
        .form-header p { 
            font-size: 12.5px; 
            color: #667085; 
            line-height: 1.5; 
        }

        .alert-login {
            background: #fef3f2; 
            border: 1px solid #fecdca; 
            color: #b42318;
            border-radius: 9px; 
            padding: 9px 12px; 
            font-size: 12.5px; 
            margin-bottom: 14px;
        }

        .bidang { 
            margin-bottom: 14px; 
        }
        .bidang label { 
            display: block; 
            font-size: 12.5px; 
            font-weight: 700; 
            color: var(--navy); 
            margin-bottom: 6px; 
        }
        .input-group { 
            position: relative; 
            display: flex; 
            align-items: center; 
        }
        .input-group .icon-left { 
            position: absolute; 
            left: 12px; 
            width: 16px; 
            height: 16px; 
            color: #7b8aa5; 
            pointer-events: none; 
            transition: color .2s; 
        }
        .input-group input {
            width: 100%; 
            height: 40px; 
            padding: 0 40px 0 36px;
            border: 1px solid #d3ddee; 
            border-radius: 9px; 
            background: #f2f6fc;
            font-size: 13px; 
            color: #101828; 
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .input-group input::placeholder { 
            color: #97a4bb; 
        }
        .input-group input:focus { 
            background: #fff; 
            border-color: var(--biru); 
            box-shadow: 0 0 0 3px rgba(47, 95, 168, .15); 
        }
        .input-group:focus-within .icon-left { 
            color: var(--biru); 
        }
        .input-group.salah input { 
            border-color: #f04438; 
        }

        .btn-toggle-password {
            position: absolute; 
            right: 5px; 
            width: 30px; 
            height: 30px;
            background: transparent; 
            border: 0; 
            border-radius: 7px; 
            cursor: pointer; 
            color: #7b8aa5;
            display: grid; 
            place-items: center;
        }
        .btn-toggle-password:hover { 
            color: var(--navy); 
        }
        .btn-toggle-password svg { 
            width: 16px; 
            height: 16px; 
        }

        .tombol-masuk {
            width: 100%; 
            height: 40px; 
            margin-top: 4px;
            border: 0; 
            border-radius: 9px; 
            cursor: pointer;
            background: var(--oranye); 
            color: #fff; 
            font-size: 13.5px; 
            font-weight: 700;
            box-shadow: 0 5px 12px rgba(255, 148, 28, .28);
            transition: background .2s;
        }
        .tombol-masuk:hover { 
            background: #e58316; 
        }
        .tombol-masuk:disabled { 
            opacity: .75; 
            cursor: wait; 
        }

        .form-footer-info { 
            text-align: center; 
            margin-top: 16px; 
            font-size: 11.5px; 
            color: #7b8aa5; 
        }

        @media (max-width: 700px) {
            .login-wrapper { 
                grid-template-columns: 1fr; 
                max-width: 400px; 
            }
            .login-sidebar { 
                display: none; 
            }
            .login-main { 
                padding: 30px 24px; 
            }
            .logo-mobile { 
                display: block; 
                margin-bottom: 16px; 
            }
            .logo-mobile img { 
                width: 56px; 
                height: 56px; 
                object-fit: contain; 
            }
        }
    </style>
</head>
<body class="halaman-login">

<div class="login-wrapper">
    <aside class="login-sidebar">
        <div class="brand">
            <img src="{{ asset('images/logo.png') }}" alt="Logo PT. Intanwijaya" onerror="this.remove();">
            <h1>KPI Kedisiplinan</h1>
            <p>Departemen Formalin</p>
        </div>
        <p class="tagline">Bersama membangun disiplin untuk kinerja yang lebih baik.</p>
    </aside>

    <main class="login-main">
        <div class="logo-mobile">
            <img src="{{ asset('images/logo.png') }}" alt="Logo PT. Intanwijaya" onerror="this.remove();">
        </div>

        <div class="form-header">
            <h2>Masuk ke akun Anda</h2>
            <p>Silakan masukkan username dan password<br>untuk melanjutkan.</p>
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

            <div class="form-footer-info">Akses hanya untuk pengguna yang terdaftar.</div>
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

    // cegah kirim ganda: tombol dikunci setelah ditekan
    document.getElementById('form-login').addEventListener('submit', function () {
        var tombol = document.getElementById('tombol-masuk');
        tombol.disabled = true;
        tombol.textContent = 'Memproses...';
    });
</script>

</body>
</html>