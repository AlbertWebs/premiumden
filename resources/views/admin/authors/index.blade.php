@extends('layouts.admin')
@section('title', 'Authors')
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Content team</p><h1>Authors</h1><p>Invite writers to create drafts for editorial review.</p></div></div>
<section class="admin-table-section" style="margin-bottom:24px"><div class="admin-section-heading"><div><p class="eyebrow">New invitation</p><h2>Invite an author</h2></div></div><form method="POST" action="{{ route('admin.authors.store') }}" class="article-editor-fields">@csrf
    <label>Name<input name="name" value="{{ old('name') }}" required maxlength="160">@error('name')<span class="field-error">{{ $message }}</span>@enderror</label>
    <label>Email<input type="email" name="email" value="{{ old('email') }}" required maxlength="190">@error('email')<span class="field-error">{{ $message }}</span>@enderror</label>
    <button class="button" type="submit">Create author and send setup link</button>
</form><p class="form-note">The setup link expires after 48 hours. Authors choose their own password.</p></section>
<section class="admin-table-section"><div class="admin-section-heading"><div><p class="eyebrow">Writers</p><h2>Current authors</h2></div></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Name</th><th>Email</th><th>Articles</th><th>Joined</th></tr></thead><tbody>
@forelse($authors as $author)<tr><td>{{ $author->name }}</td><td>{{ $author->email }}</td><td>{{ $author->articles_count }}</td><td>{{ $author->created_at->format('j M Y') }}</td></tr>@empty<tr><td colspan="4">No authors yet.</td></tr>@endforelse
</tbody></table></div>{{ $authors->links() }}</section>
@endsection
