<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#140e00">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <title>@yield('title', $siteSettings->get('default_seo_title') ?: 'Premium Business Den') · Premium Business Den</title>
    <meta name="description" content="@yield('meta_description', $siteSettings->get('default_seo_description') ?: 'A private business society for ambitious leaders and established professionals in Kenya.')">
    @if(request()->routeIs('member.*', 'application.*') || request()->is('invoice/*') || (isset($activity) && $activity->visibility === 'members'))<meta name="robots" content="noindex,nofollow">@endif
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', $siteSettings->get('default_seo_title') ?: 'Premium Business Den') · Premium Business Den">
    <meta property="og:description" content="@yield('meta_description', 'A private business society for ambitious leaders and established professionals in Kenya.')">
    <meta property="og:type" content="website">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif
    <script type="application/ld+json">{"@@context":"https://schema.org","@type":"Organization","name":"Premium Business Den","url":"https://premiumden.co.ke"}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header" x-data="{ menuOpen: false, scrolled: window.scrollY > 40 }" x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 40, { passive: true })" :class="{ 'is-scrolled': scrolled }" @keydown.escape.window="if (menuOpen) { menuOpen = false; $nextTick(() => $refs.menuToggle.focus()) }" @click.outside="menuOpen = false">
        <a class="brand" href="{{ route('home') }}" aria-label="Premium Business Den home">
            <img class="brand-logo" src="{{ asset('images/pbd-crest.png') }}" alt="">
            
        </a>
        @unless(auth()->check() && auth()->user()->hasActiveMembership() && request()->routeIs('member.dashboard'))<button x-ref="menuToggle" class="menu-toggle" type="button" aria-controls="site-primary-nav" :aria-expanded="menuOpen.toString()" :aria-label="menuOpen ? 'Close navigation' : 'Open navigation'" @click="menuOpen = !menuOpen">
            <span class="menu-toggle-icon" aria-hidden="true"><span></span><span></span></span><span class="menu-toggle-label" x-text="menuOpen ? 'Close' : 'Menu'">Menu</span>
        </button>@endunless
        @if(auth()->check() && auth()->user()->hasRole('member') && auth()->user()->membership_active)
            @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
            @unless(request()->routeIs('member.dashboard'))
            <nav class="primary-nav member-nav" id="site-primary-nav" aria-label="Member navigation" @click="menuOpen = false" x-bind:class="{ 'is-open': menuOpen }"><a href="{{ route('member.dashboard') }}" @if(request()->routeIs('member.dashboard')) aria-current="page" @endif>Dashboard</a><a href="{{ route('member.directory.index') }}" @if(request()->routeIs('member.directory.*')) aria-current="page" @endif>Members</a><a href="{{ route('member.events.index') }}" @if(request()->routeIs('member.events.*')) aria-current="page" @endif>Events</a><a href="{{ route('member.messages.index') }}" @if(request()->routeIs('member.messages.*')) aria-current="page" @endif>Messages</a><a href="{{ route('articles.index') }}" @if(request()->routeIs('articles.*')) aria-current="page" @endif>Journal</a><a href="{{ route('member.notifications.index') }}" @if(request()->routeIs('member.notifications.*')) aria-current="page" @endif>Notices @if($unreadNotifications)<span class="nav-unread">{{ $unreadNotifications }}</span>@endif</a><details class="member-nav-more" @click.stop><summary>More</summary><div class="member-nav-menu"><a href="{{ route('member.membership.show') }}">Membership</a><a href="{{ route('member.benefits') }}">Benefits</a><a href="{{ route('member.card.show') }}">Member card</a><a href="{{ route('member.profile.edit') }}">Profile</a><a href="{{ route('member.activities.index') }}">Activity</a><a href="{{ route('member.settings.edit') }}">Settings</a></div></details></nav>
            @endunless
            <div class="header-actions"><span class="member-nav-tier">{{ auth()->user()->membershipPackage?->name }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="login-link nav-logout" type="submit">Sign out</button></form></div>
        @else
            <nav class="primary-nav" id="site-primary-nav" aria-label="Primary navigation" @click="menuOpen = false" x-bind:class="{ 'is-open': menuOpen }"><a href="{{ route('membership') }}" @if(request()->routeIs('membership')) aria-current="page" @endif>Membership</a><a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>Approach</a><a href="{{ route('activities.index') }}" @if(request()->routeIs('activities.*')) aria-current="page" @endif>Sectors</a><a href="{{ route('articles.index') }}" @if(request()->routeIs('articles.*')) aria-current="page" @endif>Case Studies</a><a href="{{ route('team') }}" @if(request()->routeIs('team')) aria-current="page" @endif>Team</a><a class="nav-engage-link" href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Engage Us</a></nav>
            <div class="header-actions"><a class="button button-small" href="{{ route('application.intro') }}">Apply for membership <span aria-hidden="true">↗</span></a></div>
        @endif
    </header>

    <main>@yield('content')</main>

    <footer class="site-footer">
        <div class="footer-shell">
            <div class="footer-main">
                <div class="footer-brand-block">
                    <a class="brand brand-light" href="{{ route('home') }}" aria-label="Premium Business Den home"><img class="brand-logo" src="{{ asset('images/pbd-crest.png') }}" alt="Premium Business Den"></a>
                    <p>A private business society for ambitious minds and meaningful connection.</p>
                    <span class="footer-location"><i aria-hidden="true"></i>Nairobi, Kenya</span>
                </div>
                <nav class="footer-nav" aria-label="Footer navigation">
                    <div class="footer-nav-group"><h2>The Den</h2><a href="{{ route('about') }}">About</a><a href="{{ route('team') }}">Our team</a><a href="{{ route('membership') }}">Membership</a><a href="{{ route('how-to-join') }}">How to join</a></div>
                    <div class="footer-nav-group"><h2>Discover</h2><a href="{{ route('articles.index') }}">Journal</a><a href="{{ route('activities.index') }}">Activities</a><a href="{{ route('events.index') }}">Events</a><a href="{{ route('authors.index') }}">Authors</a></div>
                    <div class="footer-nav-group"><h2>Information</h2><a href="{{ route('contact') }}">Contact</a><a href="{{ route('login') }}">Member login</a><a href="{{ route('privacy') }}">Privacy</a><a href="{{ route('terms') }}">Terms</a></div>
                </nav>
            </div>
            <div class="footer-bottom"><small>© {{ date('Y') }} Premium Business Den</small><span>Independent minds. Shared momentum.</span></div>
        </div>
    </footer>
</body>
</html>
