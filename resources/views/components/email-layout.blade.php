<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f0e8;padding:32px 12px;font-family:Arial,sans-serif;color:#20241f">
    <tr><td align="center">
        <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="max-width:620px;width:100%;background:#fffefa;border:1px solid #e5e2d9">
            <tr><td style="background:#17251f;padding:25px 32px;color:#f3f0e8">
                <div style="font-family:Georgia,serif;font-size:19px;letter-spacing:.08em">P<span style="color:#c7a872">·</span>D</div>
                <div style="font-size:10px;letter-spacing:.16em;text-transform:uppercase;margin-top:5px">Premium Business Den</div>
            </td></tr>
            <tr><td style="padding:32px;line-height:1.7;font-size:14px">{{ $slot }}</td></tr>
            <tr><td style="padding:18px 32px;background:#f3f0e8;color:#73766e;font-size:11px;line-height:1.6">A private business society.<br>@if(isset($siteSettings) && $siteSettings->get('public_contact_email'))Support: {{ $siteSettings->get('public_contact_email') }}@endif</td></tr>
        </table>
    </td></tr>
</table>
