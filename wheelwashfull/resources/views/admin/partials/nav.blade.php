@php
    $cityQuery = request('service_city_id') ? ['service_city_id' => request('service_city_id')] : [];
    $items = [
        ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
    ];

    if (auth()->user()?->isSuperAdmin()) {
        $items = array_merge($items, [
            ['label' => 'Cities', 'icon' => 'bi-buildings', 'route' => 'admin.cities.index', 'active' => 'admin.cities.*'],
            ['label' => 'Zones', 'icon' => 'bi-map', 'route' => 'admin.zones.index', 'active' => 'admin.zones.*'],
            ['label' => 'City Admins', 'icon' => 'bi-person-gear', 'route' => 'admin.city-admins.index', 'active' => 'admin.city-admins.*'],
        ]);
    }

    $items = array_merge($items, [
        ['label' => 'Bookings', 'icon' => 'bi-calendar-check', 'route' => 'admin.bookings.index', 'active' => 'admin.bookings.*'],
        ['label' => 'Assign Team', 'icon' => 'bi-diagram-3', 'route' => 'admin.assign-team.index', 'active' => 'admin.assign-team.*'],
        ['label' => 'Team Management', 'icon' => 'bi-people', 'route' => 'admin.team-management.index', 'active' => 'admin.team-management.*'],
        ['label' => 'Partners', 'icon' => 'bi-person-badge', 'route' => 'admin.team.index', 'params' => ['type' => 'partners'], 'active' => 'admin.team.*'],
        ['label' => 'Workers', 'icon' => 'bi-tools', 'route' => 'admin.team.index', 'params' => ['type' => 'workers'], 'active' => 'admin.team.*'],
        ['label' => 'Pickup Drivers', 'icon' => 'bi-truck', 'route' => 'admin.team.index', 'params' => ['type' => 'pickup-drivers'], 'active' => 'admin.team.*'],
        ['label' => 'Subscriptions', 'icon' => 'bi-repeat', 'route' => 'admin.subscriptions.index', 'active' => 'admin.subscriptions.*'],
        ['label' => 'Subscription Plans', 'icon' => 'bi-card-checklist', 'route' => 'admin.subscription-plans.index', 'active' => 'admin.subscription-plans.*'],
        ['label' => 'Service Categories', 'icon' => 'bi-tags', 'route' => 'admin.service-categories.index', 'active' => 'admin.service-categories.*'],
        ['label' => 'Services', 'icon' => 'bi-tools', 'route' => 'admin.services.index', 'active' => 'admin.services.*'],
        ['label' => 'Slots', 'icon' => 'bi-clock', 'route' => 'admin.slots.index', 'active' => 'admin.slots.*'],
        ['label' => 'Coupons', 'icon' => 'bi-ticket-perforated', 'route' => 'admin.coupons.index', 'active' => 'admin.coupons.*'],
        ['label' => 'Carousel Banners', 'icon' => 'bi-images', 'route' => 'admin.banners.index', 'active' => 'admin.banners.*'],
        ['label' => 'Notifications', 'icon' => 'bi-bell', 'route' => 'admin.notifications.index', 'active' => 'admin.notifications.*'],
        ['label' => 'Reports', 'icon' => 'bi-graph-up', 'route' => 'admin.reports.index', 'active' => 'admin.reports.*'],
        ['label' => 'Earnings', 'icon' => 'bi-cash-coin', 'route' => 'admin.earnings.index', 'active' => 'admin.earnings.*'],
        ['label' => 'Settings', 'icon' => 'bi-gear', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
    ]);
@endphp

@foreach($items as $item)
    <a href="{{ route($item['route'], ($item['params'] ?? []) + $cityQuery) }}" class="nav-link {{ Route::is($item['active']) && (empty($item['params']['type']) || request('type') === $item['params']['type']) ? 'active' : '' }}">
        <i class="bi {{ $item['icon'] }}"></i>
        <span>{{ $item['label'] }}</span>
    </a>
@endforeach
