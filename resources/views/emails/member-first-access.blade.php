<x-email-layout>
<div style="font-family:Georgia,serif;color:#20241f;max-width:600px;margin:auto;padding:36px">
    <p style="font:11px Arial,sans-serif;letter-spacing:2px;text-transform:uppercase;color:#947a50">Premium Business Den</p>
    <h1 style="font-weight:400;color:#17251f">Welcome, {{ $member->name }}.</h1>
    <p>Your {{ $member->membershipPackage->name }} membership is active. Set a private password for your member account using the one-time link below.</p>
    <p><a href="{{ $setupUrl }}" style="display:inline-block;background:#17251f;color:#fff;padding:14px 20px;text-decoration:none;font:12px Arial,sans-serif">Set up your member access</a></p>
    <p>This link expires {{ $expiresAt }} and can be used once. If you did not expect this message, contact Premium Business Den.</p>
</div>

</x-email-layout>
