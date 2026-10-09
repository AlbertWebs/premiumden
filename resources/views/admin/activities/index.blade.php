@extends('layouts.admin')
@section('title', 'Society activities')
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Content / News and activities</p><h1>Society activity</h1><p>Manage partnerships, achievements, meetings and community updates.</p></div><a class="button" href="{{ route('admin.activities.create') }}">New activity</a></div>
<section class="admin-table-section"><table class="admin-table"><thead><tr><th>Activity</th><th>Category</th><th>Visibility</th><th>Status</th><th>Updated</th><th></th></tr></thead><tbody>@forelse($activities as $activity)<tr><td><strong>{{ $activity->title }}</strong><small>{{ $activity->creator->name }}</small></td><td>{{ $activity->category }}</td><td>{{ str($activity->visibility)->title() }}</td><td>{{ str($activity->status)->title() }}</td><td>{{ $activity->updated_at->format('j M Y') }}</td><td><a class="table-link" href="{{ route('admin.activities.edit', $activity) }}">Edit</a></td></tr>@empty<tr><td colspan="6">No society updates yet.</td></tr>@endforelse</tbody></table>{{ $activities->links() }}</section>
@endsection
