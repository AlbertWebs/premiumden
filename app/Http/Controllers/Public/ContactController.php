<?php

namespace App\Http\Controllers\Public;

use App\Http\Requests\StoreContactEnquiryRequest;
use App\Mail\ContactEnquiryReceived;
use App\Models\ContactEnquiry;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController
{
    public function create(): View { return view('public.contact'); }

    public function store(StoreContactEnquiryRequest $request): RedirectResponse
    {
        if (filled($request->validated('company_website'))) {
            return redirect()->route('contact')->with('status', 'Your message has been received.');
        }

        $enquiry = ContactEnquiry::create($request->safe()->only(['name', 'email', 'phone', 'subject', 'message']));
        $recipient = SiteSetting::value('contact_recipient_email', config('services.contact.to_address'));
        if ($recipient) {
            try { Mail::to($recipient)->send(new ContactEnquiryReceived($enquiry)); }
            catch (\Throwable $exception) { Log::error('Unable to notify the configured contact inbox.', ['enquiry_id' => $enquiry->id, 'exception' => $exception::class]); }
        }

        return redirect()->route('contact')->with('status', 'Thank you. Your message has been received.');
    }
}
