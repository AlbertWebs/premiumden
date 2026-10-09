@extends('layouts.public')
@section('title', 'Contact Premium Business Den')
@section('meta_description', 'Send a private enquiry to Premium Business Den.')
@section('content')
<section class="page-hero contact-hero"><p class="eyebrow">Start a conversation</p><h1>Contact the <em>Den.</em></h1><p>A considered space for thoughtful enquiries. Tell us what you have in mind and our team will be in touch.</p></section>
<section class="contact-page">
    <aside class="contact-intro">
        <span class="contact-seal" aria-hidden="true">P<span>·</span>D</span>
        <p class="eyebrow">A considered response</p>
        <h2>We’re here to<br><em>listen.</em></h2>
        <p class="contact-intro-copy">Whether you’re exploring membership, a partnership, or simply want to learn more, we welcome your note.</p>
        <div class="contact-details">
            @if($siteSettings->get('public_contact_email'))<a class="contact-detail" href="mailto:{{ $siteSettings->get('public_contact_email') }}"><span class="contact-detail-icon" aria-hidden="true">✉</span><span><small>Email</small><strong>{{ $siteSettings->get('public_contact_email') }}</strong></span><span class="contact-detail-arrow" aria-hidden="true">↗</span></a>@endif
            @if($siteSettings->get('public_contact_phone'))<a class="contact-detail" href="tel:{{ preg_replace('/[^0-9+]/', '', $siteSettings->get('public_contact_phone')) }}"><span class="contact-detail-icon" aria-hidden="true">＋</span><span><small>Telephone</small><strong>{{ $siteSettings->get('public_contact_phone') }}</strong></span><span class="contact-detail-arrow" aria-hidden="true">↗</span></a>@endif
            @if($siteSettings->get('office_location'))<div class="contact-detail"><span class="contact-detail-icon" aria-hidden="true">⌖</span><span><small>Location</small><strong>{{ $siteSettings->get('office_location') }}</strong></span></div>@endif
        </div>
        <p class="contact-privacy-note"><span aria-hidden="true">⌑</span><span>Your message is shared only with the authorized society team.</span></p>
    </aside>
    <form class="contact-form" method="POST" action="{{ route('contact.store') }}">
        @csrf
        <div class="contact-form-heading"><div><p class="eyebrow">Private enquiry</p><h2>Write to us</h2></div><span class="contact-required-note"><i aria-hidden="true"></i> Required fields</span></div>
        @if(session('status'))<div class="contact-success" role="status"><span aria-hidden="true">✓</span><span>{{ session('status') }}</span></div>@endif
        @if($errors->any())<div class="contact-errors" role="alert"><strong>Please review your details.</strong><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
        <div class="contact-field-grid">
            <div class="contact-field"><label for="name">Your name <span>*</span></label><input id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="160" placeholder="Jane Mwangi" required @error('name') aria-invalid="true" @enderror></div>
            <div class="contact-field"><label for="email">Email address <span>*</span></label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="190" placeholder="jane@example.com" required @error('email') aria-invalid="true" @enderror></div>
        </div>
        <div class="contact-field"><label for="phone">Phone number <small>Optional</small></label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="40" placeholder="+254 7XX XXX XXX"></div>
        <div class="contact-field"><label for="subject">Subject <span>*</span></label><input id="subject" name="subject" value="{{ old('subject') }}" maxlength="180" placeholder="What would you like to discuss?" required @error('subject') aria-invalid="true" @enderror></div>
        <div class="contact-field"><label for="message">Your message <span>*</span></label><textarea id="message" name="message" rows="6" maxlength="5000" placeholder="Share a little about your enquiry…" required @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea><small class="contact-field-hint">Please do not include identity documents or payment details.</small></div>
        <div class="form-field-honeypot" aria-hidden="true"><label for="company_website">Company website</label><input id="company_website" name="company_website" tabindex="-1" autocomplete="off"></div>
        <div class="contact-submit-row"><p><span aria-hidden="true">⌑</span> Your details remain private.</p><button class="button button-gold" type="submit">Send enquiry <span aria-hidden="true">→</span></button></div>
    </form>
</section>
@endsection
