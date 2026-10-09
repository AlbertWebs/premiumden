<x-email-layout>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-family:Arial,sans-serif;color:#352d19">
    <tr><td style="padding:0 0 10px;color:#9a7c30;font-size:10px;font-weight:bold;letter-spacing:2px;text-transform:uppercase">Membership application update</td></tr>
    <tr><td style="padding:0 0 18px;color:#183126;font-family:Georgia,serif;font-size:30px;line-height:1.25">Your membership invoice is ready.</td></tr>
    <tr><td style="padding:0 0 20px;color:#625a46;font-size:14px;line-height:1.8">Hello {{ $invoice->application->full_name }},<br><br>Your application has progressed to the invoice stage. Your {{ $invoice->application->package->name }} membership invoice is ready to review.</td></tr>
    <tr><td style="padding:17px 20px;border:1px solid #e7d8a8;background:#fbf7e9">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse">
            <tr><td style="padding:0 0 6px;color:#9a7c30;font-size:9px;font-weight:bold;letter-spacing:1.7px;text-transform:uppercase">Invoice total</td></tr>
            <tr><td style="padding:0 0 13px;color:#183126;font-family:Georgia,serif;font-size:24px">{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</td></tr>
            <tr><td style="padding-top:11px;border-top:1px solid #e7d8a8;color:#746b55;font-size:11px;letter-spacing:.4px">INVOICE&nbsp;&nbsp; <strong style="color:#352d19">{{ $invoice->reference }}</strong></td></tr>
        </table>
    </td></tr>
    <tr><td style="padding:22px 0 7px">
        <table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tr><td style="background:#b99434;padding:14px 22px">
            <a href="{{ $invoiceUrl }}" style="display:inline-block;color:#1c1a10;font-size:11px;font-weight:bold;letter-spacing:1px;text-decoration:none;text-transform:uppercase">View your invoice&nbsp;&nbsp; →</a>
        </td></tr></table>
    </td></tr>
    <tr><td style="padding:12px 0 0;color:#756d5a;font-size:11px;line-height:1.8">The secure invoice link expires in 30 days. It contains the payment instructions and current payment status. Contact the membership team if you need a new copy.</td></tr>
    <tr><td style="padding:16px 0 0;color:#746b55;font-size:11px;line-height:1.8">APPLICATION REFERENCE&nbsp;&nbsp; <strong style="color:#352d19">{{ $invoice->application->reference }}</strong></td></tr>
</table>
</x-email-layout>
