<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Supply Management System</title>
    <meta name="description" content="Sign in to the Supply Management System">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563EB;
            --primary-dark: #1D4ED8;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0F172A 0%, #1E3A5F 50%, #0F172A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(37,99,235,.2) 0%, transparent 70%);
            top: -200px; right: -200px;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(16,185,129,.15) 0%, transparent 70%);
            bottom: -100px; left: -100px;
            pointer-events: none;
        }
        .login-wrapper {
            width: 100%; max-width: 440px;
            padding: 24px;
            position: relative; z-index: 1;
        }
        .login-card {
            background: rgba(255,255,255,.05);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 24px 80px rgba(0,0,0,.4);
        }
        .brand-logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, var(--primary), #4F46E5);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            color: #fff;
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(37,99,235,.4);
        }
        h1 { color: #fff; font-size: 24px; font-weight: 800; text-align: center; margin-bottom: 4px; }
        .subtitle { color: rgba(255,255,255,.5); font-size: 13px; text-align: center; margin-bottom: 32px; }
        .form-label { color: rgba(255,255,255,.7); font-size: 12px; font-weight: 600; margin-bottom: 6px; }
        .form-control {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 10px;
            color: #fff;
            padding: 11px 14px;
            font-size: 14px;
            transition: all .2s;
        }
        .form-control:focus {
            background: rgba(255,255,255,.1);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,.25);
            color: #fff;
        }
        .form-control::placeholder { color: rgba(255,255,255,.3); }
        .input-group-text {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.1);
            border-right: none;
            color: rgba(255,255,255,.4);
        }
        .input-group .form-control { border-left: none; }
        .btn-login {
            background: linear-gradient(135deg, var(--primary), #4F46E5);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            padding: 12px;
            width: 100%;
            cursor: pointer;
            transition: all .2s;
            box-shadow: 0 4px 16px rgba(37,99,235,.4);
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(37,99,235,.5);
        }
        .forgot-link { color: rgba(255,255,255,.5); font-size: 12px; text-decoration: none; }
        .forgot-link:hover { color: rgba(255,255,255,.9); }
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, .75);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            padding: 11px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, .15);
            background: rgba(255, 255, 255, .05);
            transition: all .2s;
            width: 100%;
            margin-top: 10px;
        }
        .btn-back-home:hover {
            color: #fff;
            background: rgba(255, 255, 255, .12);
            border-color: rgba(255, 255, 255, .3);
            transform: translateY(-1px);
        }
        .alert-danger {
            background: rgba(239,68,68,.15);
            border: 1px solid rgba(239,68,68,.3);
            color: #FCA5A5;
            border-radius: 10px;
            font-size: 13px;
        }
        .demo-creds {
            background: rgba(37,99,235,.1);
            border: 1px solid rgba(37,99,235,.2);
            border-radius: 10px;
            padding: 12px 16px;
            margin-top: 20px;
        }
        .demo-creds .label { color: rgba(255,255,255,.4); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 8px; }
        .demo-cred-item {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: rgba(255,255,255,.65);
            padding: 3px 0;
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
            transition: background .1s;
        }
        .demo-cred-item:hover { background: rgba(255,255,255,.05); }
        .demo-cred-item strong { color: rgba(255,255,255,.9); }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="brand-logo">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <h1>Supply MS</h1>
            <p class="subtitle">Supply Unit Management System</p>

            @if($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif
            @if(session('status'))
                <div class="alert alert-success mb-3" style="background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#6EE7B7;border-radius:10px;font-size:13px">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="you@office.local"
                               value="{{ old('email') }}" required autocomplete="email" id="emailInput">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Password</label>
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required id="passwordInput">
                        <button type="button" class="input-group-text" style="border-left:none;cursor:pointer" onclick="togglePw()">
                            <i class="bi bi-eye" id="pwEye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2)">
                        <label class="form-check-label" for="remember" style="color:rgba(255,255,255,.6);font-size:13px">Remember me</label>
                    </div>
                </div>
                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
                <a href="{{ route('home') }}" class="btn-back-home">
                    <i class="bi bi-house-door me-2"></i>Back to Homepage
                </a>
            </form>

            <div class="demo-creds">
                <div class="label">Demo Accounts (click to fill)</div>
                <div class="demo-cred-item" onclick="fillCreds('admin@supply.local','Admin@1234')">
                    <strong>Administrator</strong><span>admin@supply.local / Admin@1234</span>
                </div>
                <div class="demo-cred-item" onclick="fillCreds('officer@supply.local','Officer@1234')">
                    <strong>Supply Officer</strong><span>officer@supply.local</span>
                </div>
                <div class="demo-cred-item" onclick="fillCreds('staff@supply.local','Staff@1234')">
                    <strong>Staff</strong><span>staff@supply.local</span>
                </div>
                <div class="demo-cred-item" onclick="fillCreds('auditor@supply.local','Auditor@1234')">
                    <strong>Auditor</strong><span>auditor@supply.local</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePw() {
            const inp = document.getElementById('passwordInput');
            const eye = document.getElementById('pwEye');
            if (inp.type === 'password') { inp.type = 'text'; eye.className = 'bi bi-eye-slash'; }
            else { inp.type = 'password'; eye.className = 'bi bi-eye'; }
        }
        function fillCreds(email, pw) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = pw;
        }
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
