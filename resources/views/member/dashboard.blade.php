@extends('layouts.member')
@section('title', 'Member dashboard')
@section('content')
<div class="member-dashboard-main">
    <section class="member-welcome">
        <div class="member-welcome-heading">
            <div><p class="eyebrow">YOUR PLACE IN THE DEN · {{ now()->format('l, j F Y') }}</p><h1>Welcome,<br><em>{{ str($member->name)->before(' ')->toString() }}.</em></h1><p>A private place to connect with fellow members and keep your society details close.</p></div>
            <a class="button button-gold" href="{{ route('member.directory.index') }}">Explore the directory <span aria-hidden="true">→</span></a>
        </div>
        <div class="member-dashboard-grid">
            <article class="member-card-preview"><p class="eyebrow eyebrow-light">PREMIUM BUSINESS DEN</p><strong>{{ $member->name }}</strong><span class="member-card-tier">{{ $member->membershipPackage->name }} member</span><small>Membership status · {{ str($membershipStatus)->replace('_', ' ')->title() }}</small><div class="card-seal">P<span>·</span>D</div>@if($member->membership)<a class="text-link card-preview-link" href="{{ route('member.card.show') }}">View digital member card <span aria-hidden="true">→</span></a>@else<small>Your confirmed member identity card will appear here.</small>@endif</article>
            <article class="member-progress"><p class="eyebrow">Your profile</p><div class="progress-number">{{ (int) round($completedFields / count($profileFields) * 100) }}<small>%</small></div><div class="progress-track"><span style="width:{{ (int) round($completedFields / count($profileFields) * 100) }}%"></span></div><p>Complete your profile to help fellow members get to know you.</p><a class="text-link" href="{{ route('member.profile.edit') }}">{{ $completedFields === count($profileFields) ? 'Review your profile' : 'Complete your profile' }} <span aria-hidden="true">→</span></a></article>
            <article class="member-network-card"><p class="eyebrow">Your network</p><strong>{{ $networkCount }}</strong><span>members are in your circle</span>@if($recommendations->isNotEmpty())<small class="member-recommendations">Recommended by {{ $recommendations->map(fn ($reference) => $reference->member?->name ?? $reference->full_name)->join(' · ') }}</small>@endif<a class="text-link" href="{{ route('member.directory.index') }}">Browse members <span aria-hidden="true">→</span></a></article>
        </div>
    </section>
    <section class="member-benefits section-wrap"><div><p class="eyebrow">Your membership</p><h2>{{ $member->membershipPackage->name }}<br><em>membership.</em></h2><p>{{ $member->membershipPackage->description }}</p><a class="text-link" href="{{ route('membership.package', $member->membershipPackage) }}">Explore your membership level <span aria-hidden="true">→</span></a></div><div><p class="eyebrow">Member benefits</p><ul class="feature-list">@foreach(($member->membershipPackage->benefits ?? []) as $benefit)<li>{{ $benefit }}</li>@endforeach</ul></div></section>
    @if($upcomingEvents->isNotEmpty())<section class="latest-insights member-dashboard-events"><div class="latest-insights-inner"><div class="section-heading"><div><p class="eyebrow">Your society calendar</p><h2>Upcoming<br><em>gatherings.</em></h2></div><a class="text-link" href="{{ route('member.events.index') }}">All member events <span aria-hidden="true">→</span></a></div><div class="event-grid">@foreach($upcomingEvents as $event)<article class="event-card"><p class="eyebrow">{{ $event->starts_at->format('j F Y · H:i') }}</p><h2><a href="{{ route('member.events.show', $event->slug) }}">{{ $event->title }}</a></h2><div class="event-meta"><span>{{ $event->location }}</span></div><a class="text-link" href="{{ route('member.events.show', $event->slug) }}">Event details <span aria-hidden="true">→</span></a></article>@endforeach</div></div></section>@endif
    <section class="member-dashboard-updates">
        <div class="member-updates-heading"><div><p class="eyebrow">YOUR DEN, AT A GLANCE</p><h2>Stay connected.</h2></div><a class="text-link" href="{{ route('member.notifications.index') }}">All notices <span aria-hidden="true">→</span></a></div>
        <div class="member-update-grid">
            <article class="member-update-panel">
                <div class="member-update-panel-heading"><div><p class="eyebrow">Messages</p><h3>Your conversations</h3></div><span class="member-update-count">{{ $unreadMessageCount }} unread</span></div>
                @forelse($recentConversations as $conversation)
                    @php($otherMember = $conversation->participants->first())
                    @if($otherMember)<a class="member-update-row" href="{{ route('member.messages.show', $conversation) }}"><span class="member-update-row-main"><strong>{{ $otherMember->name }}</strong><span>{{ $conversation->latestMessage?->body ? str($conversation->latestMessage->body)->limit(66) : 'Conversation opened' }}</span></span><small>{{ $conversation->latestMessage?->created_at?->diffForHumans() }}@if($conversation->unread_messages_count)<b>{{ $conversation->unread_messages_count }}</b>@endif</small></a>@endif
                @empty
                    <p class="member-update-empty">Your private conversations will appear here.</p>
                @endforelse
                <a class="member-update-footer-link" href="{{ route('member.messages.index') }}">Open messages <span aria-hidden="true">→</span></a>
            </article>
            <article class="member-update-panel">
                <div class="member-update-panel-heading"><div><p class="eyebrow">Recent notifications</p><h3>What’s new</h3></div></div>
                @forelse($recentNotifications as $notification)
                    @php($notice = $notification->data)
                    <a class="member-update-row" href="{{ route('member.notifications.index') }}"><span class="member-update-row-main"><strong>{{ $notice['title'] ?? (($notice['type'] ?? '') === 'new_member_message' ? 'Message from '.($notice['sender_name'] ?? 'a member') : str($notice['type'] ?? 'Society update')->replace('_', ' ')->title()) }}</strong><span>{{ $notice['preview'] ?? $notice['message'] ?? 'Open your notices to see more.' }}</span></span><small>{{ $notification->created_at->diffForHumans() }}</small></a>
                @empty
                    <p class="member-update-empty">You’re up to date. New member and society updates will appear here.</p>
                @endforelse
                <a class="member-update-footer-link" href="{{ route('member.notifications.index') }}">Visit notices <span aria-hidden="true">→</span></a>
            </article>
            <article class="member-update-panel">
                <div class="member-update-panel-heading"><div><p class="eyebrow">Society announcements</p><h3>From the Den</h3></div></div>
                @forelse($recentAnnouncements as $announcement)
                    <a class="member-update-row" href="{{ route('member.notifications.index') }}"><span class="member-update-row-main"><strong>{{ $announcement->title }}</strong><span>{{ str($announcement->body)->limit(84) }}</span></span><small>{{ $announcement->published_at?->diffForHumans() }}</small></a>
                @empty
                    <p class="member-update-empty">Important society announcements will appear here.</p>
                @endforelse
                <a class="member-update-footer-link" href="{{ route('member.notifications.index') }}">View society notices <span aria-hidden="true">→</span></a>
            </article>
            <article class="member-update-panel">
                <div class="member-update-panel-heading"><div><p class="eyebrow">Latest articles</p><h3>Ideas for what’s next</h3></div></div>
                @forelse($latestArticles as $article)
                    <a class="member-update-row" href="{{ route('articles.show', $article->slug) }}"><span class="member-update-row-main"><strong>{{ $article->title }}</strong><span>{{ $article->excerpt ?: 'A new perspective from the Den.' }}</span></span><small>{{ $article->published_at?->format('j M Y') }}</small></a>
                @empty
                    <p class="member-update-empty">New perspectives from Den authors will appear here.</p>
                @endforelse
                <a class="member-update-footer-link" href="{{ route('articles.index') }}">Explore the journal <span aria-hidden="true">→</span></a>
            </article>
        </div>
        <article class="member-suggestions-panel">
            <div class="member-update-panel-heading"><div><p class="eyebrow">People you can meet</p><h3>Suggested members</h3></div><a class="text-link" href="{{ route('member.directory.index') }}">Member directory <span aria-hidden="true">→</span></a></div>
            <div class="member-suggestion-grid">
            @forelse($suggestedMembers as $suggestedMember)
                <a class="member-suggestion" href="{{ route('member.directory.show', $suggestedMember) }}"><span class="member-avatar" aria-hidden="true">{{ collect(explode(' ', $suggestedMember->name))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}</span><span class="member-suggestion-copy"><small>{{ $suggestedMember->membershipPackage->name }}</small><strong>{{ $suggestedMember->name }}</strong><span>{{ $suggestedMember->profile->job_title ?: 'Member' }}@if($suggestedMember->profile->company) · {{ $suggestedMember->profile->company }}@endif</span></span><b aria-hidden="true">↗</b></a>
            @empty
                <p class="member-update-empty">New member introductions will appear here as your directory grows.</p>
            @endforelse
            </div>
        </article>
    </section>
</div>
@endsection
