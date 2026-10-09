@extends('layouts.public')
@section('title', 'Secure document upload')
@section('content')
<section class="document-upload-heading">
    <p class="eyebrow">Membership / Secure document upload</p>
    <h1>Complete your<br><em>membership file.</em></h1>
    <p>Application {{ $application->reference }} · {{ $application->package->name }} membership</p>
    <div class="secure-note"><span aria-hidden="true">⌑</span> This private link is time-limited. Your documents are stored securely and are only available to authorized membership administrators.</div>
</section>
<section class="document-upload-shell">
    @if(session('status'))
        <div class="profile-success" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="validation-summary" role="alert"><strong>Please review the highlighted files.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" action="{{ request()->fullUrl() }}" enctype="multipart/form-data">
        @csrf
        @php($photoDocument = $application->documents->firstWhere('document_type', 'photo'))
        <div class="upload-item">
            <div>
                <span class="eyebrow">Document 01</span><h2>Passport-size photograph</h2><p>JPG or PNG · up to 5 MB</p>
                @if($photoDocument)
                    <small class="upload-existing">Received · {{ $photoDocument->status->label() }}</small>
                    @if($photoDocument->status->value === 'replacement_requested' && $photoDocument->review_note)<p class="replacement-note">{{ $photoDocument->review_note }}</p>@endif
                @endif
            </div>
            <label class="upload-control" for="photo">Choose photograph<input id="photo" name="photo" type="file" accept="image/jpeg,image/png">
                @error('photo')<small class="field-error">{{ $message }}</small>@enderror
            </label>
        </div>
        @php($identityDocument = $application->documents->firstWhere('document_type', 'identity'))
        <div class="upload-item">
            <div>
                <span class="eyebrow">Document 02</span><h2>National ID or passport</h2><p>JPG, PNG or PDF · up to 10 MB</p>
                @if($identityDocument)
                    <small class="upload-existing">Received · {{ $identityDocument->status->label() }}</small>
                    @if($identityDocument->status->value === 'replacement_requested' && $identityDocument->review_note)<p class="replacement-note">{{ $identityDocument->review_note }}</p>@endif
                @endif
            </div>
            <div class="upload-control-group">
                <label class="select-label" for="identity_type">Document type</label>
                <select id="identity_type" name="identity_type"><option value="">Choose ID or passport</option><option value="national_id" @selected(old('identity_type', $identityDocument?->identity_type) === 'national_id')>National ID</option><option value="passport" @selected(old('identity_type', $identityDocument?->identity_type) === 'passport')>Travel passport</option></select>
                <label class="upload-control" for="identity">Choose document<input id="identity" name="identity" type="file" accept="image/jpeg,image/png,application/pdf">
                    @error('identity')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                @error('identity_type')<small class="field-error">{{ $message }}</small>@enderror
            </div>
        </div>
        <p class="upload-privacy">Upload only the requested documents. Do not email identity documents or share this link with anyone.</p>
        <button class="button" type="submit">Submit documents securely <span aria-hidden="true">↗</span></button>
    </form>
</section>
@endsection
