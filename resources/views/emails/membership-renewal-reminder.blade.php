<h1>Your membership term is coming to an end</h1>
<p>Hello {{ $membership->user->name }},</p>
<p>Your {{ $membership->package->name }} membership is currently set to expire on {{ $membership->expires_at->format('j F Y') }}.</p>
<p>The membership team can help you arrange renewal. Contact {{ $supportEmail }} or use the society contact form.</p>
<p><a href="{{ route('member.membership.show') }}">View your membership details</a></p>
