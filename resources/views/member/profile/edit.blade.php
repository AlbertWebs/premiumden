@extends('layouts.member')
@section('title', 'My profile')
@section('content')
<section class="profile-edit-shell">
    @if(session('status'))
        <div class="profile-success" role="status">{{ session('status') }}</div>
    @endif
    <form method="POST" enctype="multipart/form-data" action="{{ route('member.profile.update') }}">
        @csrf
        @method('PUT')
        <div class="profile-photo-row">
            <div class="profile-photo-avatar" aria-label="Profile photograph">
                @if($member->profile?->photo_path)
                    <img src="{{ route('member.profile.photo') }}" alt="Current profile photograph">
                @else
                    <span aria-hidden="true">{{ collect(explode(' ', $member->name))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}</span>
                @endif
            </div>
            <div class="profile-photo-copy">
                <label for="photo">Profile photograph</label>
                <p>{{ $member->profile?->photo_path ? 'Your current photograph is shown here. Choose a new file to replace it.' : 'Your initials appear until you add a photograph.' }} Use a clear, recent headshot in JPG, PNG or WebP format.</p>
                <input class="profile-photo-input" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
                @error('photo')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="profile-form-section-heading"><span>01</span><div><h2>Professional details</h2><p>Help other eligible members understand who you are and what you do.</p></div></div>
        <div class="form-grid">
            <div><label for="company">Company</label><input id="company" name="company" value="{{ old('company', $member->profile?->company) }}" maxlength="180"></div>
            <div><label for="job_title">Position or title</label><input id="job_title" name="job_title" value="{{ old('job_title', $member->profile?->job_title) }}" maxlength="140"></div>
            <div><label for="industry">Industry</label><input id="industry" name="industry" value="{{ old('industry', $member->profile?->industry) }}" maxlength="140"></div>
            <div><label for="business_category">Business category</label><input id="business_category" name="business_category" value="{{ old('business_category', $member->profile?->business_category) }}" maxlength="140"></div>
            <div><label for="location">City or region</label><input id="location" name="location" value="{{ old('location', $member->profile?->location) }}" maxlength="140"></div>
            <div><label for="interests">Business interests <span class="optional">Comma separated</span></label><input id="interests" name="interests" value="{{ old('interests', implode(', ', $member->profile?->interests ?? [])) }}" maxlength="500"></div>
        </div>
        <div class="profile-form-section-heading"><span>02</span><div><h2>Your introduction</h2><p>Share a little about your work, your experience and what you are interested in.</p></div></div>
        <label for="biography">Professional introduction</label>
        <textarea id="biography" name="biography" rows="6" maxlength="2000">{{ old('biography', $member->profile?->biography) }}</textarea>
        <div class="profile-form-section-heading"><span>03</span><div><h2>Directory visibility</h2><p>Choose whether your profile appears in the member directory.</p></div></div>
        <label class="profile-list-toggle"><input type="checkbox" name="is_listed" value="1" @checked(old('is_listed', $member->profile?->is_listed ?? true))><span>Include my profile in the member directory.</span></label>
        <p class="profile-privacy-note">Your directory visibility is always limited by membership access rules. This setting controls whether your profile is listed for eligible members.</p>
        <button class="button" type="submit">Save profile <span aria-hidden="true">→</span></button>
    </form>
</section>
<section class="profile-edit-heading"><p class="eyebrow">Member space / My profile</p><h1>Your story,<br><em>in your words.</em></h1><p>Share the professional details you want other members to see. Your contact information and internal membership records remain private.</p></section>
@endsection
