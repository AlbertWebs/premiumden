<x-email-layout>
<h1>Payment received</h1>
<p>Hello {{ $payment->invoice->application->full_name }},</p>
<p>We have confirmed your payment of {{ $payment->currency }} {{ number_format((float)$payment->amount, 2) }} against invoice {{ $payment->invoice->reference }}.</p>
<p>Your membership setup is complete. Your membership number is {{ $payment->invoice->application->membership->number }}.</p>

</x-email-layout>
