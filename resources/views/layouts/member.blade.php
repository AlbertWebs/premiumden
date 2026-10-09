<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#100c04">
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <title>@yield('title', 'Member space') · Premium Business Den</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="member-portal-body">
    @php($portalMember = auth()->user())
    @php($unreadNotifications = $portalMember->unreadNotifications()->count())
    <div class="member-portal">
        <aside class="member-sidebar" aria-label="Member portal">
            <a class="portal-brand" href="{{ route('member.dashboard') }}" aria-label="Premium Business Den member dashboard">
                <img src="{{ asset('images/pbd-crest.png') }}" width="44" height="44" decoding="async" alt="">
            </a>
            <div class="member-sidebar-profile">
                <span class="member-sidebar-avatar" aria-hidden="true">{{ str($portalMember->name)->substr(0, 1)->upper() }}</span>
                <div><strong>{{ $portalMember->name }}</strong><span>{{ $portalMember->membershipPackage?->name }} member</span></div>
            </div>
            <div class="member-sidebar-navigation" x-data="{ navigationOpen: window.innerWidth > 800 }">
            <button class="member-sidebar-toggle" type="button" @click="navigationOpen = !navigationOpen" :aria-expanded="navigationOpen.toString()"><span>Member menu</span><span aria-hidden="true" x-text="navigationOpen ? '−' : '+'"></span></button>
            <nav class="member-sidebar-nav" aria-label="Member navigation" x-show="navigationOpen" :class="{ 'is-open': navigationOpen }" x-cloak>
                <p>Member space</p>
                <a class="{{ request()->routeIs('member.dashboard') ? 'is-active' : '' }}" @if(request()->routeIs('member.dashboard')) aria-current="page" @endif href="{{ route('member.dashboard') }}"><span aria-hidden="true">⌂</span>Dashboard</a>
                <a class="{{ request()->routeIs('member.directory.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.directory.*')) aria-current="page" @endif href="{{ route('member.directory.index') }}"><span aria-hidden="true">◎</span>Member directory</a>
                <a class="{{ request()->routeIs('member.events.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.events.*')) aria-current="page" @endif href="{{ route('member.events.index') }}"><span aria-hidden="true">◇</span>Events</a>
                <a class="{{ request()->routeIs('member.deals.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.deals.*')) aria-current="page" @endif href="{{ route('member.deals.index') }}"><span aria-hidden="true">✧</span>Deals</a>
                <a class="{{ request()->routeIs('member.messages.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.messages.*')) aria-current="page" @endif href="{{ route('member.messages.index') }}"><span aria-hidden="true">✉</span>Messages</a>
                <a class="{{ request()->routeIs('member.notifications.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.notifications.*')) aria-current="page" @endif href="{{ route('member.notifications.index') }}"><span aria-hidden="true">◉</span>Notices @if($unreadNotifications)<small class="member-sidebar-count">{{ $unreadNotifications }}</small>@endif</a>
                <a class="{{ request()->routeIs('member.activities.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.activities.*')) aria-current="page" @endif href="{{ route('member.activities.index') }}"><span aria-hidden="true">▤</span>Society activity</a>
                <a href="{{ route('articles.index') }}"><span aria-hidden="true">▧</span>Journal</a>
            </nav>
            </div>
            <div class="member-sidebar-foot"><span class="portal-status-dot"></span>Private member space</div>
            <form class="member-sidebar-logout" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"><span aria-hidden="true">↪</span>Sign out</button>
            </form>
        </aside>
        <div class="portal-workspace">
            <header class="portal-topbar">
                <div class="portal-topbar-brand"><img class="portal-mobile-crest" src="{{ asset('images/pbd-crest.png') }}" width="38" height="38" decoding="async" alt=""><span class="portal-topbar-copy"><span class="portal-topbar-kicker">PREMIUM BUSINESS DEN</span><strong>Member space</strong></span></div>
                <div class="portal-topbar-actions">
                    <span class="portal-date">{{ now()->format('D, j M Y') }}</span>
                    <a class="portal-notice-link" href="{{ route('member.notifications.index') }}" aria-label="Notifications{{ $unreadNotifications ? ', '.$unreadNotifications.' unread' : '' }}">♧ @if($unreadNotifications)<i>{{ $unreadNotifications }}</i>@endif</a>
                    <details class="portal-account-menu">
                        <summary aria-label="Open account links"><span class="portal-account-avatar" aria-hidden="true">{{ str($portalMember->name)->substr(0, 1)->upper() }}</span><span class="portal-account-name">{{ str($portalMember->name)->before(' ')->toString() }}</span><span class="portal-account-chevron" aria-hidden="true">⌄</span></summary>
                        <div class="portal-account-dropdown" aria-label="Account links">
                            <p>{{ $portalMember->name }}</p>
                            <a class="{{ request()->routeIs('member.membership.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.membership.*')) aria-current="page" @endif href="{{ route('member.membership.show') }}"><span aria-hidden="true">◇</span>Membership</a>
                            <a class="{{ request()->routeIs('member.billing.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.billing.*')) aria-current="page" @endif href="{{ route('member.billing.index') }}"><span aria-hidden="true">＄</span>Billing</a>
                            <a class="{{ request()->routeIs('member.benefits') ? 'is-active' : '' }}" @if(request()->routeIs('member.benefits')) aria-current="page" @endif href="{{ route('member.benefits') }}"><span aria-hidden="true">✧</span>Benefits</a>
                            <a class="{{ request()->routeIs('member.card.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.card.*')) aria-current="page" @endif href="{{ route('member.card.show') }}"><span aria-hidden="true">▭</span>Member card</a>
                            <a class="{{ request()->routeIs('member.profile.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.profile.*')) aria-current="page" @endif href="{{ route('member.profile.edit') }}"><span aria-hidden="true">○</span>My profile</a>
                            <a class="{{ request()->routeIs('member.settings.*') ? 'is-active' : '' }}" @if(request()->routeIs('member.settings.*')) aria-current="page" @endif href="{{ route('member.settings.edit') }}"><span aria-hidden="true">⚙</span>Settings</a>
                            <form class="portal-mobile-signout" method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><span aria-hidden="true">↪</span>Sign out</button></form>
                        </div>
                    </details>
                </div>
            </header>
            <main class="portal-main">@yield('content')</main>
        </div>
    </div>
</body>
</html>
