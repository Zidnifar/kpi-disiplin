<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · KPI Kedisiplinan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body.halaman-login {
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        /* Efek dekorasi lingkaran latar belakang */
        body.halaman-login::before {
            content: '';
            position: absolute;
            top: -150px;
            left: -150px;
            width: 450px;
            height: 450px;
            background: rgba(255, 148, 28, 0.08);
            border-radius: 50%;
            z-index: 0;
        }
        body.halaman-login::after {
            content: '';
            position: absolute;
            bottom: -150px;
            right: -150px;
            width: 450px;
            height: 450px;
            background: rgba(255, 148, 28, 0.06);
            border-radius: 50%;
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 900px;
            max-width: 95%;
            background: #fff;
            border: 1px solid #d0d5dd;
            border-radius: 24px;
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            box-shadow: 0 12px 32px rgba(16, 24, 40, 0.06);
            overflow: hidden;
        }

        /* Sisi Kiri (Branding / Logo / Slogan) */
        .login-sidebar {
            background: linear-gradient(135deg, #f8f9fa 0%, #edf2f7 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            justify-content: space-between;
            border-right: 1px solid #eef2f6;
        }
        .sidebar-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            margin-top: 20px;
        }
        .brand-logo-img {
            width: 110px;
            height: 110px;
            object-fit: contain;
        }
        .brand-title h1 {
            font-size: 22px;
            font-weight: 700;
            color: #101828;
            margin-bottom: 4px;
        }
        .sidebar-footer {
            font-size: 13px;
            color: #667085;
            line-height: 1.5;
            max-width: 260px;
        }

        /* Sisi Kanan (Formulir Login - Rata Kiri) */
        .login-main {
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: left;
        }
        .form-header {
            margin-bottom: 24px;
        }
        .form-header h2 {
            font-size: 22px;
            font-weight: 700;
            color: #101828;
            margin-bottom: 6px;
        }
        .form-header p {
            font-size: 13px;
            color: #667085;
            line-height: 1.4;
        }

        .bidang {
            margin-bottom: 18px;
        }
        .bidang label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #344054;
            margin-bottom: 8px;
        }
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-group .icon-left {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            color: #667085;
        }
        .input-group input {
            width: 100%;
            height: 44px;
            border: 1px solid #d0dbea;
            border-radius: 11px;
            padding: 0 42px 0 42px;
            font-size: 14px;
            color: #101828;
            outline: none;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-group input:focus {
            border-color: #ff941c;
            box-shadow: 0 0 0 3px rgba(255, 148, 28, 0.12);
        }
        
        /* Tombol Ikon Mata Show/Hide Password */
        .btn-toggle-password {
            position: absolute;
            right: 14px;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #667085;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
        }
        .btn-toggle-password:hover {
            color: #101828;
        }
        .btn-toggle-password svg {
            width: 18px;
            height: 18px;
        }

        .galat {
            font-size: 12px;
            color: #d92d20;
            margin-top: 5px;
        }

        .tombol-masuk {
            width: 100%;
            height: 44px;
            border: 0;
            border-radius: 11px;
            background: #ff941c;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
            box-shadow: 0 4px 12px rgba(255, 148, 28, 0.2);
        }
        .tombol-masuk:hover {
            background: #e58316;
        }
        .form-footer-info {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #667085;
        }

        /* Responsif Mobile */
        @media(max-width: 768px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }
            .login-sidebar {
                display: none;
            }
            .login-main {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body class="halaman-login">

<div class="login-wrapper">
    <!-- Sisi Kiri (Logo & Slogan) -->
    <div class="login-sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Logo KPI" class="brand-logo-img">
            <div class="brand-title">
                <h1>KPI Kedisiplinan</h1>
            </div>
        </div>
        <div class="sidebar-footer">
            Bersama membangun disiplin untuk kinerja yang lebih baik.
        </div>
    </div>

    <!-- Sisi Kanan (Formulir Login Rata Kiri) -->
    <div class="login-main">
        <div class="form-header">
            <h2>Masuk ke akun Anda</h2>
            <p>Silakan masukkan username dan password<br>untuk melanjutkan.</p>
        </div>

        <form method="POST" action="{{ route('login.proses') }}" novalidate autocomplete="off">
            @csrf
            
            <div class="bidang">
                <label for="username">Username</label>
                <div class="input-group">
                    <svg class="icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" autofocus required>
                </div>
                @error('username') <div class="galat">{{ $message }}</div> @enderror
            </div>

            <div class="bidang">
                <label for="password">Password</label>
                <div class="input-group">
                    <svg class="icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input id="password" name="password" type="password" placeholder="Masukkan password" autocomplete="current-password" required>
                    <!-- Tombol Fitur Mata Show/Hide Password -->
                    <button type="button" class="btn-toggle-password" onclick="togglePassword()" title="Tampilkan/Sembunyikan Password">
                        <svg id="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                @error('password') <div class="galat">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="tombol-masuk">Masuk</button>
            
            <div class="form-footer-info">
                Akses hanya untuk pengguna yang terdaftar.
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('icon-eye');
        if (input.type === 'password') {
            input.type = 'text';
            // Ubah ikon jadi mata dicoret (Hide)
            icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>`;
        } else {
            input.type = 'password';
            // Kembalikan ke ikon mata normal (Show)
            icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>`;
        }
    }
</script>

</body>
</html>