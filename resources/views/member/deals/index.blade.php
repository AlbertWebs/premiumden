@extends('layouts.member')
@section('title', 'Member deals')
@section('content')
<div class="member-deals-page">
    @if(session('status'))<div class="deal-flash" role="status">{{ session('status') }}</div>@endif
    @if(session('deal_application_exists'))<div class="deal-flash" role="status">{{ session('deal_application_exists') }}</div>@endif

    <section class="member-deals-list" aria-label="Available member deals">
        <div class="deals-section-heading"><div><p class="eyebrow">CURATED FOR MEMBERS</p><h2>Open opportunities</h2></div><span>{{ $deals->total() }} {{ str('opportunity')->plural($deals->total()) }}</span></div>
        @forelse($deals as $deal)
            @php($application = $deal->applications->first())
            <article class="member-deal-card">
                <div class="member-deal-main">
                    <div class="member-deal-meta"><span>{{ $deal->category }}</span>@if($deal->closes_at)<span>Apply by {{ $deal->closes_at->format('j M Y') }}</span>@endif</div>
                    <h3>{{ $deal->title }}</h3>
                    <p class="member-deal-partner">Offered through {{ $deal->partner }}</p>
                    <p class="member-deal-summary">{{ $deal->summary }}</p>
                    <div class="member-deal-detail"><h4>The opportunity</h4><p>{{ $deal->details }}</p></div>
                    <div class="member-deal-detail member-deal-value"><h4>What this could mean for you</h4><p>{{ $deal->member_value }}</p></div>
                    @if($deal->application_instructions)<p class="member-deal-instructions">{{ $deal->application_instructions }}</p>@endif
                    @if($deal->documents->isNotEmpty())
                        <div class="member-deal-documents"><h4>Opportunity documents <span>{{ $deal->documents->count() }}</span></h4>
                            @foreach($deal->documents as $document)
                                <a href="{{ route('member.deals.documents.show', [$deal, $document]) }}"><span class="member-deal-file-type">{{ strtoupper(pathinfo($document->original_name, PATHINFO_EXTENSION) ?: 'FILE') }}</span><span><strong>{{ $document->original_name }}</strong><small>{{ number_format($document->size_bytes / 1024, 0) }} KB</small></span><b aria-hidden="true">↓</b></a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <aside class="member-deal-action">
                    @if($application)
                        <span class="deal-application-status">Interest {{ str($application->status)->replace('_', ' ')->title() }}</span>
                        <p>Your interest has been shared with the Den team.</p>
                    @else
                        <p class="eyebrow">A MEMBER INTRODUCTION</p>
                        <p>Tell us briefly why this opportunity is relevant to you. The team will follow up on next steps.</p>
                        <form method="POST" action="{{ route('member.deals.apply', $deal) }}">
                            @csrf
                            <label for="note-{{ $deal->id }}">A note for the team <span class="optional">Optional</span></label>
                            <textarea id="note-{{ $deal->id }}" name="note" rows="3" maxlength="1200" placeholder="Share your interest or relevant experience.">{{ old('note') }}</textarea>
                            @error('note')<span class="field-error">{{ $message }}</span>@enderror
                            <button class="button button-gold" type="submit">Apply through the Den <span aria-hidden="true">→</span></button>
                        </form>
                    @endif
                </aside>
            </article>
        @empty
            <div class="deals-empty-state"><span class="deals-empty-mark">P<span>·</span>D</span><p class="eyebrow">THE NEXT INTRODUCTION IS TAKING SHAPE</p><h3>No open deals right now.</h3><p>New partner opportunities are shared here as they become available. In the meantime, keep building your network and check back soon.</p><a class="text-link" href="{{ route('member.directory.index') }}">Explore the member directory <span aria-hidden="true">→</span></a></div>
        @endforelse
        {{ $deals->links() }}
    </section>
    <section class="member-deals-hero">
        <p class="eyebrow">Member space / Deals</p>
        <h1>Good connections.<br><em>Real opportunity.</em></h1>
        <p>Explore selected opportunities made available through the Den’s relationships and collective influence. Review the details, then submit your interest for the team to consider.</p>
        <div class="deals-trust-note"><span aria-hidden="true">✧</span>Opportunities are shared with members in confidence. Each application is subject to partner review and availability.</div>
    </section>
</div>
@endsection
