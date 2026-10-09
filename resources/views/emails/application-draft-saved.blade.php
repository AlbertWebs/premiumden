<x-email-layout>
<h1>Your application draft is saved</h1>
<p>Hello {{ $application->full_name }},</p>
<p>Use this private link to return to your application and complete your submission.</p>
<p><a href="{{ $resumeUrl }}">Continue your application</a></p>
<p>This link expires {{ $expiresAt }}. It can be renewed by saving your progress again.</p>

</x-email-layout>
