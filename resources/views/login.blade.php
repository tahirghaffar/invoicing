<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Digital Invoicing</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --auth-primary: #6c5ce7;
            --auth-primary-dark: #5947d6;
            --auth-text: #202238;
            --auth-muted: #8b8fa8;
            --auth-border: #e7e8f1;
            --auth-bg: #f8f9fc;
            --auth-panel: #f1efff;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--auth-text);
            background: #fff;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 47%) minmax(0, 53%);
        }

        .auth-form-side {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
            background: #fff;
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 470px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--auth-text);
            text-decoration: none;
            margin-bottom: 54px;
        }

        .brand-mark {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--auth-primary);
            color: #fff;
            font-size: 20px;
            font-weight: 800;
            box-shadow: 0 10px 25px rgba(108, 92, 231, .22);
        }

        .brand-copy strong {
            display: block;
            font-size: 17px;
            line-height: 1.15;
            letter-spacing: -.02em;
        }

        .brand-copy span {
            display: block;
            margin-top: 3px;
            font-size: 12px;
            color: var(--auth-muted);
        }

        .auth-title {
            margin: 0 0 9px;
            font-size: clamp(30px, 3vw, 42px);
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -.045em;
        }

        .auth-subtitle {
            margin: 0 0 34px;
            color: var(--auth-muted);
            font-size: 15px;
        }

        .form-label {
            margin-bottom: 9px;
            font-size: 14px;
            font-weight: 700;
            color: #3a3d54;
        }

        .input-group.auth-input-group {
            border: 1px solid var(--auth-border);
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .input-group.auth-input-group:focus-within {
            border-color: rgba(108, 92, 231, .55);
            box-shadow: 0 0 0 .24rem rgba(108, 92, 231, .10);
        }

        .auth-input-group .input-group-text {
            border: 0;
            background: transparent;
            padding-left: 16px;
            color: #a0a4b8;
        }

        .auth-input-group .form-control {
            min-height: 52px;
            border: 0;
            box-shadow: none !important;
            padding-left: 5px;
            color: var(--auth-text);
        }

        .auth-input-group .form-control::placeholder {
            color: #b2b5c5;
        }

        .password-toggle {
            border: 0;
            background: transparent;
            color: #9a9db0;
            padding: 0 16px;
        }

        .password-toggle:hover {
            color: var(--auth-primary);
        }

        .invalid-feedback {
            display: block;
            font-size: 12px;
        }

        .auth-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin: 16px 0 25px;
            font-size: 13px;
        }

        .form-check-label {
            color: #6d7188;
            font-weight: 500;
        }

        .form-check-input:checked {
            background-color: var(--auth-primary);
            border-color: var(--auth-primary);
        }

        .auth-link {
            color: var(--auth-primary);
            text-decoration: none;
            font-weight: 700;
        }

        .auth-link:hover {
            color: var(--auth-primary-dark);
        }

        .btn-login {
            min-height: 53px;
            border: 0;
            border-radius: 12px;
            background: var(--auth-primary);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 12px 25px rgba(108, 92, 231, .20);
            transition: transform .2s ease, background .2s ease, box-shadow .2s ease;
        }

        .btn-login:hover,
        .btn-login:focus {
            background: var(--auth-primary-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(108, 92, 231, .26);
        }

        .auth-footer {
            margin-top: 30px;
            text-align: center;
            color: var(--auth-muted);
            font-size: 13px;
        }

        .alert {
            border: 0;
            border-radius: 12px;
            font-size: 14px;
        }

        /* Right visual panel */
        .auth-visual-side {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
            background:
                radial-gradient(circle at 15% 18%, rgba(255,255,255,.9) 0 2px, transparent 3px),
                radial-gradient(circle at 80% 75%, rgba(255,255,255,.8) 0 2px, transparent 3px),
                linear-gradient(135deg, #eeeafd 0%, #f7f5ff 48%, #ece8ff 100%);
        }

        .visual-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(1px);
        }

        .visual-orb.one {
            width: 330px;
            height: 330px;
            right: -115px;
            top: -90px;
            background: rgba(108, 92, 231, .12);
        }

        .visual-orb.two {
            width: 250px;
            height: 250px;
            left: -85px;
            bottom: -70px;
            background: rgba(250, 177, 160, .22);
        }

        .visual-content {
            position: relative;
            z-index: 2;
            width: min(100%, 650px);
        }

        .visual-heading {
            max-width: 520px;
            margin-bottom: 34px;
        }

        .visual-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
            padding: 8px 13px;
            border-radius: 999px;
            background: rgba(255,255,255,.75);
            color: var(--auth-primary);
            font-size: 12px;
            font-weight: 800;
            box-shadow: 0 8px 30px rgba(88, 72, 176, .08);
        }

        .visual-heading h2 {
            margin: 0 0 12px;
            font-size: clamp(30px, 3.2vw, 48px);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -.045em;
        }

        .visual-heading p {
            margin: 0;
            max-width: 500px;
            color: #72758b;
            font-size: 15px;
            line-height: 1.75;
        }

        .invoice-mockup {
            position: relative;
            max-width: 560px;
            padding: 24px;
            border: 1px solid rgba(255,255,255,.85);
            border-radius: 24px;
            background: rgba(255,255,255,.80);
            box-shadow: 0 25px 70px rgba(78, 65, 154, .16);
            backdrop-filter: blur(12px);
        }

        .mockup-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .mockup-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
        }

        .mockup-icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #efeefe;
            color: var(--auth-primary);
        }

        .status-chip {
            padding: 7px 11px;
            border-radius: 999px;
            background: #e9f9ef;
            color: #1a8754;
            font-size: 11px;
            font-weight: 800;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }

        .metric-card {
            padding: 15px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(74, 66, 126, .07);
        }

        .metric-card span {
            display: block;
            margin-bottom: 7px;
            color: #999db0;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .07em;
            font-weight: 800;
        }

        .metric-card strong {
            font-size: 16px;
        }

        .mockup-table {
            overflow: hidden;
            border-radius: 15px;
            background: #fff;
        }

        .mockup-row {
            display: grid;
            grid-template-columns: 1.4fr .7fr .65fr;
            gap: 12px;
            padding: 13px 15px;
            border-bottom: 1px solid #f0f1f6;
            font-size: 12px;
        }

        .mockup-row:last-child {
            border-bottom: 0;
        }

        .mockup-row.header {
            background: #fafafe;
            color: #989caf;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .mockup-row .amount {
            text-align: right;
            font-weight: 700;
        }

        .floating-card {
            position: absolute;
            right: -30px;
            bottom: 34px;
            width: 190px;
            padding: 15px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 40px rgba(72, 60, 139, .18);
        }

        .floating-card .small-label {
            color: #9a9db0;
            font-size: 10px;
            font-weight: 700;
        }

        .floating-card .success-value {
            margin-top: 4px;
            color: #1a8754;
            font-size: 14px;
            font-weight: 800;
        }

        @media (max-width: 991.98px) {
            .auth-shell {
                grid-template-columns: 1fr;
            }

            .auth-visual-side {
                display: none;
            }

            .auth-form-side {
                padding: 32px 24px;
            }

            .auth-form-wrap {
                max-width: 520px;
            }

            .brand {
                margin-bottom: 42px;
            }
        }

        @media (max-width: 575.98px) {
            .auth-form-side {
                align-items: flex-start;
                padding: 28px 20px;
            }

            .brand {
                margin-bottom: 36px;
            }

            .auth-meta {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>

<body>

<div class="auth-shell">

    <section class="auth-form-side">
        <div class="auth-form-wrap">

            <a href="{{ url('/') }}" class="brand">
                <span class="brand-mark">DI</span>
                <span class="brand-copy">
                    <strong>Digital Invoicing</strong>
                    <span>FBR / PRAL Invoice Management</span>
                </span>
            </a>

            <h1 class="auth-title">Welcome back!</h1>
            <p class="auth-subtitle">Please login using your account.</p>

            @if(session('status'))
                <div class="alert alert-success mb-4">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <div class="fw-semibold mb-1">Unable to sign in</div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" autocomplete="on">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>

                    <div class="input-group auth-input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="Enter your email address"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>

                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-2">
                    <label for="password" class="form-label">Password</label>

                    <div class="input-group auth-input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="togglePassword"
                            aria-label="Show or hide password"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-meta">

                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="remember"
                            id="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="auth-link">
                            Forgot your password?
                        </a>
                    @endif

                </div>

                <button type="submit" class="btn btn-login w-100">
                    Login
                    <i class="bi bi-arrow-right ms-2"></i>
                </button>

            </form>

            @if(Route::has('register'))
                <div class="auth-footer">
                    Don’t have an account?
                    <a href="{{ route('register') }}" class="auth-link">Create an Account</a>
                </div>
            @endif

        </div>
    </section>


    <aside class="auth-visual-side">

        <span class="visual-orb one"></span>
        <span class="visual-orb two"></span>

        <div class="visual-content">

            <div class="visual-heading">
                <div class="visual-eyebrow">
                    <i class="bi bi-shield-check"></i>
                    FBR Digital Invoicing
                </div>

                <h2>Manage your invoices with confidence.</h2>

                <p>
                    Create, validate and manage digital invoices through one clean,
                    business-focused workspace while keeping your FBR workflow organized.
                </p>
            </div>

            <div class="invoice-mockup">

                <div class="mockup-top">
                    <div class="mockup-title">
                        <span class="mockup-icon">
                            <i class="bi bi-receipt"></i>
                        </span>
                        Latest Invoice
                    </div>

                    <span class="status-chip">
                        <i class="bi bi-check-circle me-1"></i>
                        Valid
                    </span>
                </div>

                <div class="metric-grid">
                    <div class="metric-card">
                        <span>Invoice</span>
                        <strong>INV-0025</strong>
                    </div>

                    <div class="metric-card">
                        <span>Scenario</span>
                        <strong>SN019</strong>
                    </div>

                    <div class="metric-card">
                        <span>Status</span>
                        <strong>Validated</strong>
                    </div>
                </div>

                <div class="mockup-table">

                    <div class="mockup-row header">
                        <div>Description</div>
                        <div>Tax</div>
                        <div class="amount">Amount</div>
                    </div>

                    <div class="mockup-row">
                        <div>Professional Services</div>
                        <div>18%</div>
                        <div class="amount">75,000</div>
                    </div>

                    <div class="mockup-row">
                        <div>Support Services</div>
                        <div>18%</div>
                        <div class="amount">25,000</div>
                    </div>

                    <div class="mockup-row">
                        <div class="fw-bold">Invoice Total</div>
                        <div></div>
                        <div class="amount">118,000</div>
                    </div>

                </div>

                <div class="floating-card">
                    <div class="small-label">FBR VALIDATION</div>
                    <div class="success-value">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Status Code 00
                    </div>
                </div>

            </div>

        </div>

    </aside>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');

        if (!passwordInput || !toggleButton) {
            return;
        }

        toggleButton.addEventListener('click', function () {
            const icon = toggleButton.querySelector('i');
            const showing = passwordInput.type === 'text';

            passwordInput.type = showing ? 'password' : 'text';

            if (icon) {
                icon.classList.toggle('bi-eye', showing);
                icon.classList.toggle('bi-eye-slash', !showing);
            }
        });
    });
</script>

</body>
</html>
