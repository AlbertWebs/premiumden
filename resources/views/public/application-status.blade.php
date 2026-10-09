@extends('layouts.public')
@section('title', 'Application status')
@section('content')
@php
    $status = $application->status;
    $isDraft = $status === \App\Enums\ApplicationStatus::Draft;
    $isTerminal = in_array($status, [\App\Enums\ApplicationStatus::Rejected, \App\Enums\ApplicationStatus::Cancelled, \App\Enums\ApplicationStatus::Suspended], true);
    $progressStep = match ($status) {
        \App\Enums\ApplicationStatus::Draft => 0,
        \App\Enums\ApplicationStatus::Submitted, \App\Enums\ApplicationStatus::UnderReview, \App\Enums\ApplicationStatus::Vetting => 1,
        \App\Enums\ApplicationStatus::Approved, \App\Enums\ApplicationStatus::DocumentsRequired, \App\Enums\ApplicationStatus::DocumentsReceived, \App\Enums\ApplicationStatus::InvoiceIssued, \App\Enums\ApplicationStatus::AwaitingPayment, \App\Enums\ApplicationStatus::Paid => 2,
        \App\Enums\ApplicationStatus::MembershipActivated => 3,
        default => null,
    };
    $statusMessage = match ($status) {
        \App\Enums\ApplicationStatus::Draft => 'Your application is saved as a draft and has not been submitted. Check your email for a private link to return and finish it.',
        \App\Enums\ApplicationStatus::Submitted, \App\Enums\ApplicationStatus::UnderReview, \App\Enums\ApplicationStatus::Vetting => 'Your application is being reviewed. The team will contact you if they need more information.',
        \App\Enums\ApplicationStatus::Approved => 'Your application has been approved. The membership team will email you instructions for the secure document submission step.',
        \App\Enums\ApplicationStatus::DocumentsRequired => 'Please use the secure document upload link sent to your email to submit your photograph and identification.',
        \App\Enums\ApplicationStatus::DocumentsReceived => 'Your documents have been received and are being reviewed.',
        \App\Enums\ApplicationStatus::InvoiceIssued, \App\Enums\ApplicationStatus::AwaitingPayment => 'Your invoice and payment instructions have been sent to your email.',
        \App\Enums\ApplicationStatus::Paid => 'Your payment is recorded. The team is completing the membership setup.',
        \App\Enums\ApplicationStatus::MembershipActivated => 'Your membership is active. Sign in to access your member account.',
        \App\Enums\ApplicationStatus::Rejected => 'We are unable to proceed with this membership application. Thank you for your interest in Premium Business Den.',
        \App\Enums\ApplicationStatus::Cancelled => 'This membership application has been cancelled. Contact the membership team if you believe this is an error.',
        \App\Enums\ApplicationStatus::Suspended => 'Membership access has been suspended. Contact the membership team for assistance.',
    };
    $journey = [
        ['Application', 'Your details and references are on file.'],
        ['Review', 'The team considers each application with care.'],
        ['Membership setup', 'Documents and payment are completed securely.'],
        ['Member access', 'Your membership is ready to use.'],
    ];
@endphp
<section class="application-status-hero">
    <div class="application-status-hero-inner">
        <div class="application-status-title"><p class="eyebrow"><span class="eyebrow-line"></span>Membership / Application status</p><h1>Your application<br><em>{{ $isDraft ? 'draft.' : 'status.' }}</em></h1><p>Private status information for your Premium Business Den application.</p></div>
        <aside class="application-status-card">
            <span class="status-card-label">Current status</span>
            <strong class="application-status-badge status-{{ str($status->value)->replace('_', '-') }}">{{ $status->label() }}</strong>
            <p>{{ $statusMessage }}</p>
        </aside>
    </div>
</section>
<section class="application-status-content">
    <div class="application-status-layout">
        <div class="application-journey-panel">
            @if(!$isTerminal && $progressStep !== null)
                <p class="eyebrow">Your application journey</p>
                <ol class="application-journey">
                    @foreach($journey as $index => [$label, $description])
                        <li class="{{ $index < $progressStep ? 'is-complete' : ($index === $progressStep ? 'is-current' : '') }}" @if($index === $progressStep) aria-current="step" @endif>
                            <span class="journey-marker" aria-hidden="true">{{ $index < $progressStep ? '✓' : str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div><strong>{{ $label }}</strong><p>{{ $description }}</p></div>
                            @if($index === $progressStep)<span class="journey-current-label">{{ $status->label() }}</span>@endif
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="eyebrow">Application status</p>
                <h2>{{ $status->label() }}</h2>
                <p class="status-terminal-copy">For assistance with this status, contact the membership team.</p>
                @if(in_array($status, [\App\Enums\ApplicationStatus::Rejected, \App\Enums\ApplicationStatus::Cancelled, \App\Enums\ApplicationStatus::Suspended], true))
                    <a class="text-link" href="{{ route('contact') }}">Contact the Den <span aria-hidden="true">↗</span></a>
                @endif
            @endif
        </div>
        <aside class="application-record-panel">
            <p class="eyebrow">Your record</p>
            <div class="application-reference-block"><span>Reference</span><strong>{{ $application->reference }}</strong><small>Keep this number for your records.</small></div>
            <dl><div><dt>Membership level</dt><dd>{{ $application->package->name }}</dd></div><div><dt>{{ $isDraft ? 'Last saved' : 'Last updated' }}</dt><dd>{{ ($application->updated_at ?: $application->submitted_at)?->format('j F Y') ?? '—' }}</dd></div></dl>
            @if($hasActiveAccount)<a class="button button-gold" href="{{ route('login') }}">Log in to your account <span aria-hidden="true">↗</span></a>@endif
        </aside>
    </div>
    <p class="application-status-privacy"><span aria-hidden="true">⌑</span> For your privacy, this page does not display application details or internal review notes. Check your email for secure document or invoice links.</p>
</section>
@endsection
