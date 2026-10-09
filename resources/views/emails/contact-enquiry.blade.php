<x-email-layout>
<div style="font-family:Arial,sans-serif;color:#20241f;max-width:650px;margin:auto;padding:32px">
    <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#947a50">Premium Business Den · Website enquiry</p>
    <h1 style="font-family:Georgia,serif;font-weight:400;color:#17251f">{{ $enquiry->subject }}</h1>
    <p><strong>{{ $enquiry->name }}</strong> · <a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a>@if($enquiry->phone) · {{ $enquiry->phone }}@endif</p>
    <p style="white-space:pre-line;line-height:1.7">{{ $enquiry->message }}</p>
</div>

</x-email-layout>
