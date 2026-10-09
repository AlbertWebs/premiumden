@extends('layouts.admin')
@section('title', 'Membership packages')
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Membership / Configuration</p><h1>Membership packages</h1><p>Set actual package pricing and maintain the published benefits.</p></div></div>
<section class="admin-table-section"><div class="table-scroll"><table class="admin-table"><thead><tr><th>Package</th><th>Term price</th><th>Access level</th><th>Status</th><th></th></tr></thead><tbody>@foreach($packages as $package)<tr><td><strong>{{ $package->name }}</strong><small>{{ $package->slug }}</small></td><td>{{ $package->price === null ? 'Not set' : 'KES '.number_format((float) $package->price, 2) }}</td><td>{{ $package->networking_level }}</td><td><span class="status-pill">{{ $package->is_active ? 'Active' : 'Inactive' }}</span></td><td><a class="table-link" href="{{ route('admin.packages.edit', $package) }}">Edit →</a></td></tr>@endforeach</tbody></table></div></section>
@endsection
