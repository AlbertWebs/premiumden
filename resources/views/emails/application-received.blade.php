<x-email-layout>
<h1>Thank you, {{ $application->full_name }}</h1>
<p>We have received your membership application for {{ $application->package->name }}. The Risk Team will review the information and references you provided.</p>
<p>Your application reference is <strong>{{ $application->reference }}</strong>.</p>
<p><a href="{{ $statusUrl }}">View your application status</a></p>
<p>We will contact you at this address as the review progresses. Internal review notes remain confidential.</p>

</x-email-layout>
