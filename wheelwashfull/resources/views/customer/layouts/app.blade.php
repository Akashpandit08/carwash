<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WashMate')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3f37c9;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f4f6fb;
            padding-bottom: 75px;
        }
        .app-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            padding: 14px 16px 14px;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 2px 12px rgba(67,97,238,.18);
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
            transition: color .2s;
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
        .btn-outline-primary { border-radius: 10px; }
        .btn-outline-secondary { border-radius: 10px; }
        .badge { padding: 5px 11px; border-radius: 20px; font-weight: 600; font-size: 11px; }
        .service-card { cursor: pointer; transition: transform .18s; }
        .service-card:hover { transform: translateY(-3px); }
        .alert { border-radius: 12px; border: none; }
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding: 10px 14px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67,97,238,.1);
        }
        .page-content { padding: 14px 14px 0; }
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
                    <h1>@yield('header-title', 'WashMate')</h1>
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
            <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show mt-2" role="alert">
                <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    @auth
    <nav class="bottom-nav">
        <a href="{{ route('customer.home') }}" class="{{ request()->routeIs('customer.home') ? 'active' : '' }}">
            <i class="bi bi-house-door{{ request()->routeIs('customer.home') ? '-fill' : '' }}"></i>Home
        </a>
        <a href="{{ route('customer.services.index') }}" class="{{ request()->routeIs('customer.services.*') ? 'active' : '' }}">
            <i class="bi bi-droplet{{ request()->routeIs('customer.services.*') ? '-fill' : '' }}"></i>Services
        </a>
        <a href="{{ route('customer.bookings.index') }}" class="{{ request()->routeIs('customer.bookings.*') ? 'active' : '' }}">
            <i class="bi bi-calendar{{ request()->routeIs('customer.bookings.*') ? '-check-fill' : '-check' }}"></i>Bookings
        </a>
        <a href="{{ route('customer.vehicles.index') }}" class="{{ request()->routeIs('customer.vehicles.*') ? 'active' : '' }}">
            <i class="bi bi-car-front{{ request()->routeIs('customer.vehicles.*') ? '-fill' : '' }}"></i>Vehicles
        </a>
        <a href="{{ route('customer.profile.index') }}" class="{{ request()->routeIs('customer.profile.*') ? 'active' : '' }}">
            <i class="bi bi-person{{ request()->routeIs('customer.profile.*') ? '-fill' : '' }}"></i>Profile
        </a>
    </nav>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
