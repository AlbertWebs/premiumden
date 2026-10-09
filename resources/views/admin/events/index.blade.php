@extends('layouts.admin')
@section('title', 'Events')
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Content / Society calendar</p><h1>Events</h1><p>Prepare public gatherings and member events with package visibility.</p></div><a class="button" href="{{ route('admin.events.create') }}">Create event <span aria-hidden="true">＋</span></a></div>
<section class="admin-table-section">@if($events->isEmpty())<div class="admin-empty"><strong>No events recorded</strong><p>Draft and published events will appear here.</p></div>@else<div class="table-scroll"><table class="admin-table"><thead><tr><th>Event</th><th>When</th><th>Visibility</th><th>Status</th><th></th></tr></thead><tbody>@foreach($events as $event)<tr><td><strong>{{ $event->title }}</strong><small>{{ $event->location }}</small></td><td>{{ $event->starts_at->format('j M Y · H:i') }}</td><td>{{ str($event->visibility)->replace('_', ' ')->title() }}</td><td><span class="status-pill">{{ str($event->status)->title() }}</span></td><td><a class="table-link" href="{{ route('admin.events.edit', $event) }}">Open →</a></td></tr>@endforeach</tbody></table></div>{{ $events->links() }}@endif</section>
@endsection
