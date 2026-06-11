<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WashMate Partner')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary: #059669; --primary-dark: #047857; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0fdf4;
            padding-bottom: 75px;
        }
        .app-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            padding: 14px 16px;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 2px 12px rgba(5,150,105,.2);
        }
        .app-header h1 { font-size: 20px; font-weight: 700; margin: 0; }
        .app-header .subtitle { font-size: 12px; opacity: .85; }
        .btn-back {
            background: rgba(255,255,255,.15);
            border: none;
            color: #fff;
            width: 34px; height: 34px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            box-shadow: 0 -2px 12px rgba(0,0,0,.09);
            display: flex;
            z-index: 200;
        }
        .bottom-nav a {
            flex: 1;
            text-align: center;
            color: #9aa0ac;
            text-decoration: none;
            padding: 8px 0 6px;
            font-size: 10px;
        }
        .bottom-nav a.active { color: var(--primary); }
        .bottom-nav a i { display: block; font-size: 22px; margin-bottom: 2px; }
        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            margin-bottom: 14px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 10px;
            padding: 11px 22px;
            font-weight: 600;
        }
        .btn-primary:hover, .btn-primary:focus { background: var(--primary-dark); }
        .badge { padding: 5px 11px; border-radius: 20px; font-weight: 600; font-size: 11px; }
        .alert { border-radius: 12px; border: none; }
        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding: 10px 14px;
        }
        .page-content { padding: 14px 14px 0; }
        .job-card { cursor: pointer; transition: transform .15s; }
        .job-card:active { transform: scale(.98); }
        .status-step { font-size: 11px; }
        .status-step.active { color: var(--primary); font-weight: 700; }
        .status-step.done { color: var(--primary); }
    </style>
    @stack('styles')
</head>
<body>
    @auth
    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                @hasSection('back-url')
                    <a href="@yield('back-url')" class="btn-back me-1">
                        <i class="bi bi-arrow-left" style="font-size:16px;"></i>
                    </a>
                @endif
                <div>
                    <h1>@yield('header-title', 'WashMate Partner')</h1>
                    @hasSection('header-subtitle')
                        <div class="subtitle">@yield('header-subtitle')</div>
                    @endif
                </div>
            </div>
            @yield('header-action')
        </div>
    </div>
    @endauth

    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-2">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-2">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </div>

    @auth
    <nav class="bottom-nav">
        <a href="{{ route('partner.jobs.today') }}" class="{{ request()->routeIs('partner.jobs.today') ? 'active' : '' }}">
            <i class="bi bi-calendar-day{{ request()->routeIs('partner.jobs.today') ? '-fill' : '' }}"></i>Today
        </a>
        <a href="{{ route('partner.jobs.upcoming') }}" class="{{ request()->routeIs('partner.jobs.upcoming') ? 'active' : '' }}">
            <i class="bi bi-calendar-week{{ request()->routeIs('partner.jobs.upcoming') ? '-fill' : '' }}"></i>Upcoming
        </a>
        <a href="{{ route('partner.earnings.index') }}" class="{{ request()->routeIs('partner.earnings.*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i>Earnings
        </a>
        <a href="{{ route('partner.reviews.index') }}" class="{{ request()->routeIs('partner.reviews.*') ? 'active' : '' }}">
            <i class="bi bi-star{{ request()->routeIs('partner.reviews.*') ? '-fill' : '' }}"></i>Reviews
        </a>
        <a href="{{ route('partner.profile.index') }}" class="{{ request()->routeIs('partner.profile.*') ? 'active' : '' }}">
            <i class="bi bi-person{{ request()->routeIs('partner.profile.*') ? '-fill' : '' }}"></i>Profile
        </a>
    </nav>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
