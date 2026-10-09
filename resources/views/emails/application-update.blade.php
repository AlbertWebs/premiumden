<x-email-layout>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-family:Arial,sans-serif;color:#352d19">
    <tr><td style="padding:0 0 10px;color:#9a7c30;font-size:10px;font-weight:bold;letter-spacing:2px;text-transform:uppercase">Membership application update</td></tr>
    <tr><td style="padding:0 0 18px;color:#183126;font-family:Georgia,serif;font-size:30px;line-height:1.25">A new step in your application.</td></tr>
    <tr><td style="padding:0 0 20px;color:#625a46;font-size:14px;line-height:1.8">Hello {{ $application->full_name }},<br><br>{{ $updateText }}</td></tr>
    <tr><td style="padding:17px 20px;border:1px solid #e7d8a8;background:#fbf7e9">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse">
            <tr><td style="padding:0 0 6px;color:#9a7c30;font-size:9px;font-weight:bold;letter-spacing:1.7px;text-transform:uppercase">Current application status</td></tr>
            <tr><td style="padding:0 0 13px;color:#183126;font-family:Georgia,serif;font-size:21px">{{ $application->status->label() }}</td></tr>
            <tr><td style="padding-top:11px;border-top:1px solid #e7d8a8;color:#746b55;font-size:11px;letter-spacing:.4px">APPLICATION REFERENCE&nbsp;&nbsp; <strong style="color:#352d19">{{ $application->reference }}</strong></td></tr>
        </table>
    </td></tr>
    <tr><td style="padding:24px 0 7px">
        <table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tr><td style="background:#b99434;padding:13px 21px">
            <a href="{{ $statusUrl }}" style="display:inline-block;color:#1c1a10;font-size:11px;font-weight:bold;letter-spacing:1px;text-decoration:none;text-transform:uppercase">View application status&nbsp;&nbsp; →</a>
        </td></tr></table>
    </td></tr>
    <tr><td style="padding:13px 0 0;color:#756d5a;font-size:11px;line-height:1.8">Your application is being handled privately by the Premium Business Den team. If you have questions, reply to this email or contact the membership team.</td></tr>
</table>
</x-email-layout>
