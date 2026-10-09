@extends('layouts.admin')
@section('title', 'Edit '.$package->name.' package')
@section('content')
<div class="admin-breadcrumb"><a href="{{ route('admin.packages.index') }}">Membership packages</a><span>/</span><span>{{ $package->name }}</span></div>
<div class="admin-page-heading"><div><p class="eyebrow">Membership / Package configuration</p><h1>{{ $package->name }}</h1><p>Pricing must be configured before an invoice can be issued to an applicant.</p></div></div>

<form class="cms-editor-form cms-editor-form-narrow event-editor admin-package-form" method="POST" action="{{ route('admin.packages.update', $package) }}">
    @csrf @method('PUT')
    <header class="admin-form-heading">
        <div><p class="eyebrow">Tier configuration</p><h2>{{ $package->name }} membership</h2><p>Manage the public description, member benefits and access settings for this tier.</p></div>
        <span class="admin-form-state {{ $package->is_active ? 'is-active' : 'is-inactive' }}"><i aria-hidden="true"></i>{{ $package->is_active ? 'Public' : 'Hidden' }}</span>
    </header>

    <div class="article-editor-fields">
        <section class="admin-form-section">
            <div class="admin-form-section-heading"><span>01</span><div><h3>Package overview</h3><p>This description appears wherever applicants compare membership levels.</p></div></div>
            <div class="admin-form-field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" maxlength="2000">{{ old('description', $package->description) }}</textarea>
                <small class="admin-form-help">Keep this clear and focused on who this tier is designed for.</small>
                @error('description')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-form-field admin-form-price">
                <label for="price">Membership term price <span class="admin-form-unit">KES</span></label>
                <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $package->price) }}" inputmode="decimal">
                <small class="admin-form-help">Leave blank until the price is confirmed. An invoice cannot be issued without a price.</small>
                @error('price')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </section>

        <section class="admin-form-section">
            <div class="admin-form-section-heading"><span>02</span><div><h3>Member benefits</h3><p>List the benefits displayed on this membership level.</p></div></div>
            <div class="admin-form-field">
                <label for="benefits">Benefits <span class="admin-form-unit">One benefit per line</span></label>
                <textarea id="benefits" name="benefits" rows="6" maxlength="5000">{{ old('benefits', implode("\n", $package->benefits ?? [])) }}</textarea>
                <small class="admin-form-help">Each non-empty line becomes a separate item on the membership page.</small>
                @error('benefits')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </section>

        <section class="admin-form-section">
            <div class="admin-form-section-heading"><span>03</span><div><h3>Access and renewal</h3><p>Control the membership term, network reach and order shown on the public page.</p></div></div>
            <div class="form-grid admin-form-settings">
                <div class="admin-form-field"><label for="renewal_months">Renewal period</label><div class="admin-form-input-unit"><input id="renewal_months" name="renewal_months" type="number" min="1" max="120" value="{{ old('renewal_months', $package->renewal_months) }}"><span>months</span></div><small class="admin-form-help">Length of each membership term.</small>@error('renewal_months')<span class="field-error">{{ $message }}</span>@enderror</div>
                <div class="admin-form-field"><label for="networking_level">Networking level</label><input id="networking_level" name="networking_level" type="number" min="1" max="100" value="{{ old('networking_level', $package->networking_level) }}"><small class="admin-form-help">Members can connect within this access level.</small>@error('networking_level')<span class="field-error">{{ $message }}</span>@enderror</div>
                <div class="admin-form-field"><label for="display_order">Display order</label><input id="display_order" name="display_order" type="number" min="0" max="65535" value="{{ old('display_order', $package->display_order) }}"><small class="admin-form-help">Lower numbers appear first.</small>@error('display_order')<span class="field-error">{{ $message }}</span>@enderror</div>
                <div class="admin-form-field"><label for="is_active">Public visibility</label><select id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $package->is_active) === '1')>Visible to applicants</option><option value="0" @selected((string) old('is_active', (int) $package->is_active) === '0')>Hidden from applicants</option></select><small class="admin-form-help">Existing memberships are not affected.</small>@error('is_active')<span class="field-error">{{ $message }}</span>@enderror</div>
            </div>
        </section>
        @foreach($errors->all() as $message)<span class="field-error">{{ $message }}</span>@endforeach
    </div>

    <footer class="admin-form-actions"><a class="admin-form-cancel" href="{{ route('admin.packages.index') }}">Cancel</a><button class="button" type="submit">Save package <span aria-hidden="true">→</span></button></footer>
</form>
@endsection
