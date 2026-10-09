<x-email-layout>
<h1>Welcome to Premium Business Den, {{ $author->name }}</h1>
<p>Your author account is ready. Use the private link below to set your password and access the writing studio.</p>
<p><a href="{{ $setupUrl }}">Set up author access</a></p>
<p>This link expires {{ $expiresAt }} and can be used once. If you were not expecting this invitation, you can ignore this message.</p>

</x-email-layout>
