@extends('layouts.admin')
@section('title', 'Audit history')
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Administration</p><h1>Audit history</h1><p>Key application, document, payment, package and author decisions.</p></div></div>
<form class="admin-filters" method="GET"><label class="filter-search"><span class="sr-only">Filter by action</span><input name="action" value="{{ request('action') }}" placeholder="Filter action"></label><button class="button" type="submit">Filter</button></form>
<section class="admin-table-section"><table class="admin-table"><thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Record</th><th>Details</th></tr></thead><tbody>
@forelse($events as $event)<tr><td>{{ $event->created_at->format('j M Y H:i') }}</td><td>{{ $event->actor?->name ?? 'System / payment provider' }}</td><td>{{ str($event->action)->replace('.', ' · ')->title() }}</td><td>{{ class_basename($event->resource_type) }} #{{ $event->resource_id }}</td><td>{{ collect($event->metadata ?? [])->map(fn($value, $key) => str($key)->replace('_', ' ')->title().': '.(is_scalar($value) ? $value : json_encode($value)))->implode(' · ') ?: '—' }}</td></tr>@empty<tr><td colspan="5">No audit activity yet.</td></tr>@endforelse
</tbody></table>{{ $events->links() }}</section>
@endsection
