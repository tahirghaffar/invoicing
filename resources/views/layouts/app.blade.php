<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Digital Invoicing') | FBR</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --app-primary: #6c5ce7;
            --app-primary-dark: #5647cf;
            --app-primary-soft: #f0edff;
            --app-sidebar: #17182f;
            --app-sidebar-muted: #9296b1;
            --app-body: #f7f8fc;
            --app-surface: #ffffff;
            --app-border: #e9ebf3;
            --app-text: #202334;
            --app-muted: #7d8295;
            --app-success: #18a875;
            --app-danger: #e55353;
            --app-warning: #f0ad4e;
            --app-sidebar-width: 272px;
            --app-topbar-height: 78px;
            --app-radius: 16px;
            --app-shadow: 0 10px 35px rgba(31, 36, 58, .06);
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
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--app-body);
            color: var(--app-text);
            font-size: .925rem;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: var(--app-primary);
            text-decoration: none;
        }

        a:hover {
            color: var(--app-primary-dark);
        }

        /* ---------------------------------------------------------
         | Application shell
         |--------------------------------------------------------- */
        .app-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1040;
            width: var(--app-sidebar-width);
            height: 100vh;
            display: flex;
            flex-direction: column;
            background:
                radial-gradient(circle at 15% 0%, rgba(108, 92, 231, .28), transparent 32%),
                var(--app-sidebar);
            color: #fff;
            transition: transform .25s ease;
        }

        .sidebar-brand {
            min-height: var(--app-topbar-height);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 24px;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
            border-radius: 13px;
            background: linear-gradient(135deg, #7c6cf2, #5e4ed8);
            box-shadow: 0 8px 20px rgba(108, 92, 231, .30);
            color: #fff;
            font-size: 1.2rem;
        }

        .brand-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -.02em;
        }

        .brand-subtitle {
            display: block;
            margin-top: 3px;
            color: #aeb1c8;
            font-size: .68rem;
            font-weight: 500;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .sidebar-scroll {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 20px 14px 24px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.16) transparent;
        }

        .nav-section-title {
            padding: 16px 12px 8px;
            color: #666b89;
            font-size: .67rem;
            font-weight: 700;
            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .app-nav {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .app-nav-link {
            min-height: 46px;
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 10px 13px;
            border-radius: 12px;
            color: var(--app-sidebar-muted);
            font-size: .875rem;
            font-weight: 500;
            transition: all .18s ease;
        }

        .app-nav-link .nav-icon {
            width: 22px;
            flex: 0 0 22px;
            display: inline-flex;
            justify-content: center;
            font-size: 1.05rem;
        }

        .app-nav-link:hover {
            background: rgba(255,255,255,.06);
            color: #fff;
        }

        .app-nav-link.active {
            background: linear-gradient(135deg, rgba(124,108,242,.95), rgba(94,78,216,.95));
            color: #fff;
            box-shadow: 0 10px 24px rgba(70, 54, 190, .25);
        }

        .app-nav-link .nav-arrow {
            margin-left: auto;
            opacity: .45;
            font-size: .75rem;
        }

        .sidebar-footer {
            margin: 0 14px 18px;
            padding: 14px;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 14px;
            background: rgba(255,255,255,.04);
        }

        .sidebar-footer .status-dot {
            width: 8px;
            height: 8px;
            display: inline-block;
            border-radius: 50%;
            background: #38d39f;
            box-shadow: 0 0 0 4px rgba(56,211,159,.11);
        }

        .sidebar-footer-title {
            color: #f3f4fb;
            font-size: .78rem;
            font-weight: 600;
        }

        .sidebar-footer-text {
            margin-top: 4px;
            color: #858aa5;
            font-size: .68rem;
            line-height: 1.5;
        }

        .app-main {
            min-height: 100vh;
            margin-left: var(--app-sidebar-width);
            transition: margin-left .25s ease;
        }

        .app-topbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            min-height: var(--app-topbar-height);
            display: flex;
            align-items: center;
            padding: 12px 28px;
            background: rgba(255,255,255,.94);
            border-bottom: 1px solid var(--app-border);
            backdrop-filter: blur(12px);
        }

        .min-w-0 {
            min-width: 0;
        }

        .topbar-inner {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .page-heading {
            min-width: 0;
        }

        .page-heading h1 {
            margin: 0;
            color: var(--app-text);
            font-size: 1.22rem;
            font-weight: 700;
            letter-spacing: -.025em;
        }

        .page-heading .page-context {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 4px;
            color: var(--app-muted);
            font-size: .74rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .business-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 260px;
            padding: 8px 12px;
            border: 1px solid var(--app-border);
            border-radius: 11px;
            background: #fff;
            color: #454a5e;
            font-size: .78rem;
            font-weight: 600;
            box-shadow: 0 3px 12px rgba(31,36,58,.03);
        }

        .business-chip span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .topbar-action {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--app-border);
            border-radius: 11px;
            background: #fff;
            color: #5e6378;
            transition: all .18s ease;
        }

        .topbar-action:hover {
            border-color: #dcd9fb;
            background: var(--app-primary-soft);
            color: var(--app-primary);
        }

        .user-menu-button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 8px 5px 5px;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: var(--app-text);
        }

        .user-menu-button:hover,
        .user-menu-button:focus {
            background: #f5f5fa;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: var(--app-primary-soft);
            color: var(--app-primary);
            font-weight: 700;
            font-size: .84rem;
        }

        .user-meta {
            max-width: 160px;
            text-align: left;
        }

        .user-name {
            display: block;
            overflow: hidden;
            color: #35394d;
            font-size: .78rem;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-role {
            display: block;
            margin-top: 1px;
            color: var(--app-muted);
            font-size: .65rem;
        }

        .page-content {
            padding: 26px 28px 38px;
        }

        .content-width {
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* ---------------------------------------------------------
         | Bootstrap / legacy view compatibility
         | Keeps existing Blade pages usable while each page is
         | progressively redesigned later.
         |--------------------------------------------------------- */
        .card {
            border: 1px solid rgba(233,235,243,.82);
            border-radius: var(--app-radius);
            background: var(--app-surface);
            box-shadow: var(--app-shadow);
        }

        .page-content > .content-width > .card,
        .page-content > .content-width > form > .card {
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .card-header {
            padding: 1.1rem 1.35rem;
            border-bottom: 1px solid var(--app-border);
            background: transparent;
        }

        .card-body {
            padding: 1.35rem;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: .84rem;
            padding: .625rem 1rem;
            box-shadow: none !important;
        }

        .btn-primary {
            --bs-btn-bg: var(--app-primary);
            --bs-btn-border-color: var(--app-primary);
            --bs-btn-hover-bg: var(--app-primary-dark);
            --bs-btn-hover-border-color: var(--app-primary-dark);
            --bs-btn-active-bg: var(--app-primary-dark);
            --bs-btn-active-border-color: var(--app-primary-dark);
        }

        .btn-gray {
            background: #eef0f5;
            border-color: #eef0f5;
            color: #4f5467;
        }

        .btn-gray:hover {
            background: #e4e6ed;
            border-color: #e4e6ed;
            color: #34384a;
        }

        .page-content label {
            margin-bottom: .45rem;
            color: #3b4054;
            font-size: .79rem;
            font-weight: 600;
        }

        .page-content input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="submit"]):not([type="button"]),
        .page-content select,
        .page-content textarea {
            width: 100%;
            min-height: 42px;
            padding: .625rem .8rem;
            margin-bottom: 1rem;
            border: 1px solid #dfe2eb;
            border-radius: 10px;
            background-color: #fff;
            color: var(--app-text);
            font: inherit;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .page-content textarea {
            min-height: 100px;
        }

        .page-content input:focus,
        .page-content select:focus,
        .page-content textarea:focus {
            border-color: rgba(108,92,231,.55);
            box-shadow: 0 0 0 .2rem rgba(108,92,231,.10);
        }

        .form-control,
        .form-select {
            border-color: #dfe2eb;
            border-radius: 10px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(108,92,231,.55);
            box-shadow: 0 0 0 .2rem rgba(108,92,231,.10);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            color: #404559;
        }

        th {
            padding: 12px 14px;
            border-bottom: 1px solid var(--app-border);
            background: #fafaff;
            color: #72778b;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .035em;
            text-transform: uppercase;
        }

        td {
            padding: 13px 14px;
            border-bottom: 1px solid #f0f1f6;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .table-responsive {
            border-radius: 12px;
        }

        .badge {
            border-radius: 8px;
            padding: .4rem .6rem;
            font-weight: 600;
        }

        .alert {
            border: 0;
            border-radius: 13px;
            box-shadow: 0 7px 20px rgba(31,36,58,.04);
        }

        .alert-success {
            background: #eaf8f3;
            color: #087653;
        }

        .alert-danger {
            background: #fff0f0;
            color: #b53838;
        }

        .success,
        .error {
            padding: 12px 14px;
            margin-bottom: 1rem;
            border-radius: 12px;
        }

        .success {
            background: #eaf8f3;
            color: #087653;
        }

        .error {
            background: #fff0f0;
            color: #b53838;
        }

        .dropdown-menu {
            padding: .55rem;
            border: 1px solid var(--app-border);
            border-radius: 13px;
            box-shadow: 0 16px 40px rgba(31,36,58,.12);
        }

        .dropdown-item {
            padding: .6rem .7rem;
            border-radius: 9px;
            color: #4d5265;
            font-size: .8rem;
        }

        .dropdown-item:hover {
            background: var(--app-primary-soft);
            color: var(--app-primary-dark);
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            z-index: 1035;
            display: none;
            background: rgba(20,22,40,.46);
            backdrop-filter: blur(2px);
        }

        /* Guest/auth screens that also extend this layout. */
        .guest-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 16px;
            background:
                radial-gradient(circle at 15% 10%, rgba(108,92,231,.13), transparent 28%),
                radial-gradient(circle at 85% 90%, rgba(80,194,171,.10), transparent 28%),
                var(--app-body);
        }

        .guest-content {
            width: 100%;
            max-width: 520px;
        }

        @media (max-width: 1199.98px) {
            .app-sidebar {
                transform: translateX(-100%);
                box-shadow: 18px 0 40px rgba(18,20,38,.18);
            }

            body.sidebar-open .app-sidebar {
                transform: translateX(0);
            }

            body.sidebar-open .sidebar-overlay {
                display: block;
            }

            .app-main {
                margin-left: 0;
            }
        }

        @media (max-width: 767.98px) {
            :root {
                --app-topbar-height: 68px;
            }

            .app-topbar {
                padding: 10px 16px;
            }

            .page-content {
                padding: 18px 15px 30px;
            }

            .page-heading h1 {
                font-size: 1rem;
            }

            .page-heading .page-context,
            .business-chip,
            .user-meta {
                display: none;
            }

            .user-menu-button {
                padding: 3px;
            }

            .user-avatar {
                width: 36px;
                height: 36px;
                border-radius: 10px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

@if(auth()->check())
    @php
        /*
         * Navigation does not define or change any Laravel routes. It uses
         * a conventional existing named route when available and otherwise
         * falls back to the current project URL path. Confirmed route names
         * can be tightened as each individual screen is redesigned.
         */
        $currentBusinessId = session('current_business_id');
        $navUrl = function (string $routeName, string $fallbackPath, array $params=[]) {
            return \Illuminate\Support\Facades\Route::has($routeName)
                ? route($routeName,$params)
                : url($fallbackPath);
        };

if(auth()->check() && auth()->user()->hasSystemRole('super-admin')){
    $navigation = [
        'Workspace' => [
            [
                'label' => 'Dashboard',
                'icon' => 'bi-grid-1x2-fill',
                'url' => $navUrl('admin.dashboard', '/'),
                'active' => request()->is('/') || request()->routeIs('admin.dashboard', 'home'),
            ],
        ],
        'Settings' => [
            [
                'label' => 'Businesses',
                'icon' => 'bi-receipt',
                'url' => $navUrl('admin.businesses', 'admin/businesses'),
                'active' => request()->is('/') || request()->routeIs('admin.businesses'),
            ],
        ],
    ];
} else {
    $navigation = [
            'Workspace' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'bi-grid-1x2-fill',
                    'url' => $navUrl('dashboard', '/'),
                    'active' => request()->is('/') || request()->routeIs('dashboard', 'home'),
                ],
            ],
            'Invoicing' => [
                [
                    'label' => 'Invoices',
                    'icon' => 'bi-receipt',
                    'url' => $navUrl('invoices.index', '/invoices'),
                    'active' => request()->is('invoices*'),
                ],
                [
                    'label' => 'Customers',
                    'icon' => 'bi-people',
                    'url' => $navUrl('customers.index', '/customers'),
                    'active' => request()->is('customers*'),
                ],
                [
                    'label' => 'Products & Services',
                    'icon' => 'bi-box-seam',
                    'url' => $navUrl('products.index', '/products'),
                    'active' => request()->is('products*'),
                ],
            ],
            'FBR / PRAL' => [
                [
                    'label' => 'FBR Configuration',
                    'icon' => 'bi-sliders',
                    'url' => $navUrl('fbr.settings.edit', '/settings/fbr'),
                    'active' => request()->is('settings/fbr*'),
                ],
//                [
//                    'label' => 'FBR References',
//                    'icon' => 'bi-database-check',
//                    'url' => $navUrl('fbr.references.index', '/fbr/references'),
//                    'active' => request()->is('fbr/references*') || request()->is('references*'),
//                ],
            ],
            'Settings' => [
                [
                    'label' => 'Business Profile',
                    'icon' => 'bi-building',
                    'url' => route('business.profile.edit'),
                    'active' => request()->routeIs('business.profile.edit') || request()->is('settings/business-profile*'),
                ],
            ],
        ];
}
        $currentPageTitle = 'Dashboard';
        foreach ($navigation as $items) {
            foreach ($items as $item) {
                if ($item['active']) {
                    $currentPageTitle = $item['label'];
                    break 2;
                }
            }
        }

        $userName = auth()->user()->name ?? 'User';
        $userEmail = auth()->user()->email ?? null;
        $userInitials = collect(preg_split('/\s+/', trim($userName)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <aside class="app-sidebar" id="appSidebar" aria-label="Main navigation">
        <div class="sidebar-brand">
            <div class="brand-mark" aria-hidden="true">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div>
                <div class="brand-title">Digital Invoicing</div>
                <span class="brand-subtitle">FBR / PRAL</span>
            </div>
        </div>

        <div class="sidebar-scroll">
            @foreach($navigation as $section => $items)
                <div class="nav-section-title">{{ $section }}</div>

                <nav class="app-nav">
                    @foreach($items as $item)
                        <a
                            href="{{ $item['url'] }}"
                            class="app-nav-link {{ $item['active'] ? 'active' : '' }}"
                            @if($item['active']) aria-current="page" @endif
                        >
                            <span class="nav-icon"><i class="bi {{ $item['icon'] }}"></i></span>
                            <span>{{ $item['label'] }}</span>
                            <i class="bi bi-chevron-right nav-arrow"></i>
                        </a>
                    @endforeach
                </nav>
            @endforeach
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2">
                <span class="status-dot"></span>
                <span class="sidebar-footer-title">FBR Integration Ready</span>
            </div>
            <div class="sidebar-footer-text">
                Business-specific invoicing workspace with sandbox and production-safe workflows.
            </div>
        </div>
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <div class="topbar-inner">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <button
                        type="button"
                        class="topbar-action d-xl-none"
                        id="sidebarToggle"
                        aria-label="Open navigation"
                        aria-controls="appSidebar"
                        aria-expanded="false"
                    >
                        <i class="bi bi-list fs-5"></i>
                    </button>

                    <div class="page-heading">
                        <h1>@yield('page_title', $currentPageTitle)</h1>
                        <div class="page-context">
                            <span>Digital Invoicing</span>
                            <i class="bi bi-chevron-right" style="font-size:.55rem"></i>
                            <span>@yield('page_subtitle', 'FBR / PRAL Workspace')</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 gap-md-3">
                    @isset($currentBusiness)
                        <div class="business-chip" title="Current business">
                            <i class="bi bi-building-check text-primary"></i>
                            <span>{{ $currentBusiness->name }}</span>
                        </div>
                    @endisset

                    <div class="dropdown">
                        <button
                            class="user-menu-button dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <span class="user-avatar">{{ $userInitials ?: 'U' }}</span>
                            <span class="user-meta">
                                <span class="user-name">{{ $userName }}</span>
                                <span class="user-role">{{ $userEmail ?: 'Account' }}</span>
                            </span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end mt-2" style="min-width: 220px;">
                            @if($userEmail)
                                <li>
                                    <a
                                        href="{{ route('business.profile.edit') }}"
                                        class="d-block px-2 pt-1 pb-2 text-decoration-none"
                                    >
                                        <div class="small fw-semibold text-dark">
                                            Profile
                                        </div>
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
{{--                            <li class="px-2 pt-1 pb-2">--}}
{{--                                <div class="small fw-semibold text-dark">{{ $userName }}</div>--}}
{{--                                @if($userEmail)--}}
{{--                                    <div class="text-muted" style="font-size:.7rem;">{{ $userEmail }}</div>--}}
{{--                                @endif--}}
{{--                            </li>--}}
{{--                            <li><hr class="dropdown-divider"></li>--}}
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger w-100 text-start">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <main class="page-content">
            <div class="content-width">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-2" role="alert">
                        <i class="bi bi-check-circle-fill mt-1"></i>
                        <div class="flex-grow-1">{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-2 fw-semibold">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Please review the following:</span>
                        </div>
                        <ul class="mb-0 ps-4">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
@else
    <main class="guest-shell">
        <div class="guest-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </div>
    </main>
@endif

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    (function () {
        const body = document.body;
        const toggle = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');

        function closeSidebar() {
            body.classList.remove('sidebar-open');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                const isOpen = body.classList.toggle('sidebar-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1200) {
                closeSidebar();
            }
        });
    })();
</script>

@stack('scripts')
</body>
</html>
