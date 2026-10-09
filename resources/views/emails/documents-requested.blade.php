<x-email-layout>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-family:Arial,sans-serif;color:#352d19">
    <tr><td style="padding:0 0 10px;color:#9a7c30;font-size:10px;font-weight:bold;letter-spacing:2px;text-transform:uppercase">Your application is progressing</td></tr>
    <tr><td style="padding:0 0 18px;color:#183126;font-family:Georgia,serif;font-size:30px;line-height:1.25">Your next step, handled securely.</td></tr>
    <tr><td style="padding:0 0 20px;color:#625a46;font-size:14px;line-height:1.8">Hello {{ $application->full_name }},<br><br>Your application for <strong>{{ $application->package->name }}</strong> membership has been approved to proceed to document review. Please securely provide the following:</td></tr>
    <tr><td style="padding:15px 18px;border:1px solid #e7d8a8;background:#fbf7e9;color:#4e452f;font-size:13px;line-height:2">&bull;&nbsp; Passport-size photograph<br>&bull;&nbsp; National identity card or travel passport</td></tr>
    <tr><td style="padding:17px 0 0;color:#625a46;font-size:12px;line-height:1.8">This private link expires <strong>{{ $expiresAt }}</strong>. You can use it to replace a document if the membership team requests one.</td></tr>
    <tr><td style="padding:22px 0 7px">
        <table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tr><td style="background:#b99434;padding:14px 22px">
            <a href="{{ $uploadUrl }}" style="display:inline-block;color:#1c1a10;font-size:11px;font-weight:bold;letter-spacing:1px;text-decoration:none;text-transform:uppercase">Upload documents securely&nbsp;&nbsp; →</a>
        </td></tr></table>
    </td></tr>
    <tr><td style="padding:12px 0 0;color:#746b55;font-size:11px;line-height:1.8">APPLICATION REFERENCE&nbsp;&nbsp; <strong style="color:#352d19">{{ $application->reference }}</strong></td></tr>
    <tr><td style="padding:16px 0 0;color:#756d5a;font-size:11px;line-height:1.8">If you did not submit this application, contact the Premium Business Den team.</td></tr>
</table>
</x-email-layout>
