<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - WashMate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; overflow-x: hidden; }
        #sidebar { min-width: 250px; max-width: 250px; min-height: 100vh; background: #343a40; color: #fff; transition: all 0.3s; }
        #sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; font-weight: 500; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); }
        #sidebar .nav-link i { width: 24px; text-align: center; margin-right: 8px; }
        #content { width: 100%; }
        .topbar { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .card { border: 0; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); border-radius: 0.5rem; }
    </style>
    @stack('styles')
</head>
<body class="d-flex">

    <!-- Sidebar -->
    <nav id="sidebar" class="d-flex flex-column">
        <div class="p-4 text-center border-bottom border-secondary">
            <h4 class="mb-0 fw-bold text-white"><i class="bi bi-droplet-fill text-primary"></i> WheelWash</h4>
            <span class="badge bg-primary mt-2">Admin Portal</span>
        </div>
        <ul class="nav flex-column mb-auto py-3">
            <li class="nav-item">
                <a href="{{ url('/admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/bookings') }}" class="nav-link {{ request()->is('admin/bookings*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Bookings
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/customers') }}" class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/partners') }}" class="nav-link {{ request()->is('admin/partners*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i> Partners
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/services') }}" class="nav-link {{ request()->is('admin/services*') ? 'active' : '' }}">
                    <i class="bi bi-tools"></i> Services
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->is('admin/banners*') ? 'active' : '' }}">
                    <i class="bi bi-images"></i> Banners
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                    <i class="bi bi-bell"></i> Notifications
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/slots') }}" class="nav-link {{ request()->is('admin/slots*') ? 'active' : '' }}">
                    <i class="bi bi-clock"></i> Slots
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/coupons') }}" class="nav-link {{ request()->is('admin/coupons*') ? 'active' : '' }}">
                    <i class="bi bi-ticket-perforated"></i> Coupons
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/payments') }}" class="nav-link {{ request()->is('admin/payments*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i> Payments
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/reviews') }}" class="nav-link {{ request()->is('admin/reviews*') ? 'active' : '' }}">
                    <i class="bi bi-star"></i> Reviews
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/reports') }}" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content" class="d-flex flex-column flex-grow-1 min-vh-100">
        <!-- Topbar -->
        <nav class="navbar navbar-expand-lg topbar py-3 px-4">
            <div class="container-fluid">
                <h5 class="mb-0 fw-semibold text-dark">@yield('header-title', 'Dashboard')</h5>
                <div class="ms-auto d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle border-0" type="button" id="adminDropdown" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> Admin
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <form action="{{ url('/admin/logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
