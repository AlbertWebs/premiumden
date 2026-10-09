<x-email-layout>
<h1>Welcome to Premium Business Den, {{ $membership->user->name }}</h1>
<p>Your {{ $membership->package->name }} membership is active.</p>
<p>Membership number: <strong>{{ $membership->number }}</strong></p>
<h2>Your membership benefits</h2>
<ul>@foreach(($membership->package->benefits ?? []) as $benefit)<li>{{ $benefit }}</li>@endforeach</ul>
<p><a href="{{ $portalUrl }}">Enter the member portal</a></p>
<p>Your first-access link arrives separately. For membership support, contact {{ $supportEmail }}.</p>

</x-email-layout>
