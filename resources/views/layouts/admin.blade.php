<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon.png') }}"><link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}"><link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <title>@yield('title', 'Administration') · Premium Business Den</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    @php
        $adminUser = auth()->user();
        $adminRoleLabel = match ($adminUser->role?->value) {
            'super_administrator' => 'Super administrator',
            'risk_team' => 'Risk team',
            'membership_administrator' => 'Membership admin',
            'content_administrator' => 'Content admin',
            default => 'Administrator',
        };
        $adminInitials = collect(explode(' ', trim($adminUser->name)))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
    @endphp
    <div class="admin-shell">
        <aside class="admin-sidebar" aria-label="Administration navigation">
            <a class="admin-sidebar-brand" href="{{ $adminUser->hasRole('content_administrator') && !$adminUser->hasRole('super_administrator') ? route('admin.articles.index') : route('admin.dashboard') }}" aria-label="Premium Business Den administration">
                <img src="{{ asset('images/pbd-crest.png') }}" alt="Premium Business Den">
                <span><strong>PREMIUM BUSINESS DEN</strong><small>ADMIN CONSOLE</small></span>
            </a>

            <nav class="admin-sidebar-nav">
                @if($adminUser->hasRole('super_administrator') || $adminUser->hasRole('risk_team') || $adminUser->hasRole('membership_administrator'))
                    <p class="admin-nav-label">Member services</p>
                    <a class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Overview</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.applications.*') ? 'is-active' : '' }}" href="{{ route('admin.applications.index') }}" @if(request()->routeIs('admin.applications.*')) aria-current="page" @endif>Applications</a>
                @endif
                @if($adminUser->hasRole('super_administrator') || $adminUser->hasRole('membership_administrator'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.members.*') ? 'is-active' : '' }}" href="{{ route('admin.members.index') }}" @if(request()->routeIs('admin.members.*')) aria-current="page" @endif>Members</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.payments.*') ? 'is-active' : '' }}" href="{{ route('admin.payments.index') }}" @if(request()->routeIs('admin.payments.*')) aria-current="page" @endif>Payments</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.packages.*') ? 'is-active' : '' }}" href="{{ route('admin.packages.index') }}" @if(request()->routeIs('admin.packages.*')) aria-current="page" @endif>Packages</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.welcome-packages.*') ? 'is-active' : '' }}" href="{{ route('admin.welcome-packages.index') }}" @if(request()->routeIs('admin.welcome-packages.*')) aria-current="page" @endif>Welcome kits</a>
                @endif
                @if($adminUser->hasRole('super_administrator') || $adminUser->hasRole('risk_team') || $adminUser->hasRole('membership_administrator'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.audit.*') ? 'is-active' : '' }}" href="{{ route('admin.audit.index') }}" @if(request()->routeIs('admin.audit.*')) aria-current="page" @endif>Audit history</a>
                @endif
                @if($adminUser->hasRole('super_administrator') || $adminUser->hasRole('content_administrator') || $adminUser->hasRole('membership_administrator'))
                    <p class="admin-nav-label">Opportunities</p>
                    <a class="admin-nav-link {{ request()->routeIs('admin.deals.*') ? 'is-active' : '' }}" href="{{ route('admin.deals.index') }}" @if(request()->routeIs('admin.deals.*')) aria-current="page" @endif>Member deals</a>
                @endif
                @if($adminUser->hasRole('super_administrator') || $adminUser->hasRole('content_administrator'))
                    <p class="admin-nav-label">Publishing</p>
                    <a class="admin-nav-link {{ request()->routeIs('admin.articles.*') ? 'is-active' : '' }}" href="{{ route('admin.articles.index') }}" @if(request()->routeIs('admin.articles.*')) aria-current="page" @endif>Articles</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.authors.*') ? 'is-active' : '' }}" href="{{ route('admin.authors.index') }}" @if(request()->routeIs('admin.authors.*')) aria-current="page" @endif>Authors</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.taxonomy.*') ? 'is-active' : '' }}" href="{{ route('admin.taxonomy.index') }}" @if(request()->routeIs('admin.taxonomy.*')) aria-current="page" @endif>Categories</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.activities.*') ? 'is-active' : '' }}" href="{{ route('admin.activities.index') }}" @if(request()->routeIs('admin.activities.*')) aria-current="page" @endif>Activities</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.events.*') ? 'is-active' : '' }}" href="{{ route('admin.events.index') }}" @if(request()->routeIs('admin.events.*')) aria-current="page" @endif>Events</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}" href="{{ route('admin.announcements.index') }}" @if(request()->routeIs('admin.announcements.*')) aria-current="page" @endif>Notices</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.enquiries.*') ? 'is-active' : '' }}" href="{{ route('admin.enquiries.index') }}" @if(request()->routeIs('admin.enquiries.*')) aria-current="page" @endif>Enquiries</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}" href="{{ route('admin.settings.edit') }}" @if(request()->routeIs('admin.settings.*')) aria-current="page" @endif>Settings</a>
                @endif
            </nav>

            <div class="admin-sidebar-footer">
                <div class="admin-sidebar-identity">
                    <span class="admin-avatar" aria-hidden="true">{{ $adminInitials }}</span>
                    <span class="admin-sidebar-user"><strong>{{ $adminUser->name }}</strong><small>{{ $adminRoleLabel }}</small></span>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><span>Sign out</span><span aria-hidden="true">↗</span></button></form>
            </div>
        </aside>

        <div class="admin-workspace">
            <header class="admin-topbar">
                <div class="admin-topbar-heading">
                    <p class="admin-topbar-eyebrow">ADMINISTRATION <span>·</span> PREMIUM BUSINESS DEN</p>
                    <h1 class="admin-topbar-title">@yield('title', 'Administration')</h1>
                </div>
                <div class="admin-topbar-account"><span class="admin-topbar-secure"><i aria-hidden="true"></i> STAFF CONSOLE</span><span class="admin-avatar" aria-hidden="true">{{ $adminInitials }}</span><span class="admin-topbar-user"><strong>{{ $adminUser->name }}</strong><small>{{ $adminRoleLabel }}</small></span></div>
            </header>
            @if(session('status'))<div class="admin-flash" role="status">{{ session('status') }}</div>@endif
            <main class="admin-main">@yield('content')</main>
        </div>
    </div>
</body>
</html>
