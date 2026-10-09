<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactEnquiryController
{
    public function index(Request $request): View
    {
        $enquiries = ContactEnquiry::with('handler')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $like = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('subject', 'like', $like));
            })->latest()->paginate(20)->withQueryString();
        return view('admin.contact-enquiries.index', compact('enquiries'));
    }

    public function update(Request $request, ContactEnquiry $enquiry): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:new,contacted,resolved']]);
        $updates = $data + ['handled_by' => $request->user()->id];
        if ($data['status'] === 'contacted' && ! $enquiry->contacted_at) { $updates['contacted_at'] = now(); }
        if ($data['status'] === 'resolved' && ! $enquiry->resolved_at) { $updates['resolved_at'] = now(); }
        $enquiry->update($updates);
        app(\App\Services\RecordAuditEvent::class)->handle('contact_enquiry.status_changed', $enquiry, ['status' => $enquiry->status], $request->user()->id);
        return back()->with('status', 'Enquiry status updated.');
    }
}
