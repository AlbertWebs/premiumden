<x-email-layout>
    <p style="margin:0 0 12px;color:#9a7730;font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase">A note from your network</p>
    <h1 style="margin:0 0 18px;color:#17251f;font-family:Georgia,serif;font-size:30px;font-weight:400;line-height:1.2">A private message<br>is waiting for you.</h1>
    <p style="margin:0 0 19px;color:#50564e">Hello {{ $recipientName }},</p>
    <p style="margin:0 0 23px;color:#50564e;line-height:1.8"><strong style="color:#20241f">{{ $senderName }}</strong> has sent you a message in your Premium Business Den member space.</p>
    <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;margin:0 0 26px;border:1px solid #e4dcc7;background:#f8f5ec">
        <tr><td style="padding:16px 18px">
            <div style="color:#8a7955;font-size:9px;font-weight:700;letter-spacing:.16em;text-transform:uppercase">PRIVATE CONVERSATION</div>
            @if($sentAt)<div style="margin-top:6px;color:#68685d;font-size:12px">Received {{ $sentAt }}</div>@endif
        </td></tr>
    </table>
    <p style="margin:0 0 27px;color:#50564e;line-height:1.8">Your conversation is private. Sign in to read and reply securely in the member portal.</p>
    <p style="margin:0 0 27px"><a href="{{ $conversationUrl }}" style="display:inline-block;padding:13px 21px;background:#17251f;color:#fff5d6;font-size:11px;font-weight:700;letter-spacing:.09em;text-decoration:none;text-transform:uppercase">Open your message&nbsp; &rarr;</a></p>
    <p style="margin:0;color:#6c7067;font-size:12px;line-height:1.7">Warm regards,<br><strong style="color:#30382f">The Premium Business Den</strong></p>
</x-email-layout>
