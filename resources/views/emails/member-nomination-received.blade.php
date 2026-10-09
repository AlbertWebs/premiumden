<x-email-layout>
<p style="color:#9a835c;font-size:11px;letter-spacing:2px;text-transform:uppercase">A personal request</p>
<h1 style="color:#183126;font-family:Georgia,serif;font-size:30px;font-weight:400">{{ $reference->member->name }}, will you support this nomination?</h1>
<p>{{ $application->full_name }} has named you as a member reference for their Premium Business Den application. They believe you can speak to their work and character.</p>
<p>Your response helps the membership team move the application forward. Please choose one action below:</p>
<p style="margin:28px 0"><a href="{{ $acceptUrl }}" style="display:inline-block;background:#20352b;color:#fff;padding:14px 22px;text-decoration:none;font-weight:600">Accept nomination&nbsp; →</a></p>
<p><a href="{{ $declineUrl }}" style="color:#66563b;font-weight:600">I’m unable to support this nomination</a></p>
<p style="color:#73766e;font-size:13px">These secure links expire in 14 days. If you did not expect this request, you can ignore this email.</p>
</x-email-layout>
