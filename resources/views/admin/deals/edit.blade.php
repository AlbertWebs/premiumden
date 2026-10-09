@extends('layouts.admin')
@section('title', $deal->exists ? 'Edit member deal' : 'New member deal')
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Member experience / Opportunities</p><h1>{{ $deal->exists ? 'Edit opportunity' : 'New opportunity' }}</h1><p>Share a useful opportunity with the right members, with supporting documents when needed.</p></div><a class="text-link" href="{{ route('admin.deals.index') }}">← All deals</a></div>
<form class="cms-editor-form cms-editor-form-narrow event-editor admin-deal-form" method="POST" enctype="multipart/form-data" action="{{ $deal->exists ? route('admin.deals.update', $deal) : route('admin.deals.store') }}">
    @csrf
    @if($deal->exists)@method('PUT')@endif
    <div class="article-editor-fields">
        <div class="form-grid">
            <div><label for="title">Opportunity title</label><input id="title" name="title" value="{{ old('title', $deal->title) }}" maxlength="180" required></div>
            <div><label for="partner">Partner or source</label><input id="partner" name="partner" value="{{ old('partner', $deal->partner) }}" maxlength="180" required></div>
        </div>
        <label for="category">Category</label><input id="category" name="category" value="{{ old('category', $deal->category) }}" maxlength="80" placeholder="Finance, procurement, growth..." required>
        <label for="summary">Short summary</label><textarea id="summary" name="summary" rows="3" maxlength="500" required>{{ old('summary', $deal->summary) }}</textarea>
        <label for="details">Opportunity details</label><textarea id="details" name="details" rows="7" maxlength="12000" required>{{ old('details', $deal->details) }}</textarea>
        <label for="member_value">Member value</label><textarea id="member_value" name="member_value" rows="4" maxlength="5000" required>{{ old('member_value', $deal->member_value) }}</textarea>
        <label for="application_instructions">Application guidance <span class="optional">Optional</span></label><textarea id="application_instructions" name="application_instructions" rows="3" maxlength="5000">{{ old('application_instructions', $deal->application_instructions) }}</textarea>

        <section class="deal-form-section">
            <p class="eyebrow">Member audience</p>
            <label for="minimum_package_id">Minimum membership level</label>
            <select id="minimum_package_id" name="minimum_package_id"><option value="">All active members</option>@foreach($packages as $package)<option value="{{ $package->id }}" @selected((string) old('minimum_package_id', $deal->minimum_package_id) === (string) $package->id)>{{ $package->name }}</option>@endforeach</select>
            <p class="deal-form-help">Members at the selected tier and higher access levels can see the deal. Diamond is the broadest audience; selecting Platinum limits it to Platinum-level members.</p>
        </section>

        <section class="deal-form-section">
            <div class="deal-documents-heading"><div><p class="eyebrow">Supporting material</p><h2>Deal documents</h2><p>Upload briefs, terms, application packs or partner information for eligible members.</p></div><span>{{ $deal->documents->count() }} attached</span></div>
            <div class="deal-upload-zone" data-deal-dropzone>
                <input id="documents" class="deal-upload-input" type="file" name="documents[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.jpg,.jpeg,.png" multiple aria-label="Upload supporting deal documents" aria-describedby="deal-upload-help">
                <div class="deal-upload-icon" aria-hidden="true">↑</div>
                <strong>Drop files here or <span>browse</span></strong>
                <small id="deal-upload-help">PDF, Word, Excel, PowerPoint, CSV or image · Up to 10 MB per file · 5 files per save</small>
                <ul class="deal-upload-selection" data-deal-file-list aria-live="polite"></ul>
            </div>
            @error('documents')<span class="field-error">{{ $message }}</span>@enderror
            @error('documents.*')<span class="field-error">{{ $message }}</span>@enderror
            @if($deal->documents->isNotEmpty())
                <ul class="deal-attached-documents">
                    @foreach($deal->documents as $document)
                        <li><span class="deal-file-mark" aria-hidden="true">DOC</span><span class="deal-file-copy"><strong>{{ $document->original_name }}</strong><small>{{ number_format($document->size_bytes / 1024, 0) }} KB</small></span><a href="{{ route('admin.deals.documents.show', [$deal, $document]) }}">Download</a><form method="POST" action="{{ route('admin.deals.documents.destroy', [$deal, $document]) }}" onsubmit="return confirm('Remove this deal document?')">@csrf @method('DELETE')<button type="submit" aria-label="Remove {{ $document->original_name }}">Remove</button></form></li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="form-grid"><div><label for="starts_at">Available from</label><input id="starts_at" name="starts_at" type="date" value="{{ old('starts_at', $deal->starts_at?->format('Y-m-d')) }}"></div><div><label for="closes_at">Application deadline</label><input id="closes_at" name="closes_at" type="date" value="{{ old('closes_at', $deal->closes_at?->format('Y-m-d')) }}"></div><div><label for="display_order">Display order</label><input id="display_order" name="display_order" type="number" min="0" max="65535" value="{{ old('display_order', $deal->display_order ?? 0) }}" required></div><div><label for="is_active">Visibility</label><select id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $deal->is_active) === '1')>Published</option><option value="0" @selected((string) old('is_active', (int) $deal->is_active) === '0')>Draft</option></select></div></div>
        @foreach($errors->all() as $error)<span class="field-error">{{ $error }}</span>@endforeach
    </div>
    <button class="button" type="submit">Save opportunity <span aria-hidden="true">→</span></button>
</form>
@endsection
