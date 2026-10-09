<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Requests\TransitionMembershipApplicationRequest;
use App\Models\MembershipApplication;
use App\Services\TransitionMembershipApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController
{
    public function index(Request $request): View
    {
        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'payment_status' => ['nullable', 'in:pending,processing,paid,failed,cancelled,refunded']]);
        $applications = MembershipApplication::query()
            ->with('package')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('package'), fn ($query) => $query->where('membership_package_id', $request->integer('package')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('submitted_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('submitted_at', '<=', $request->date('to')))
            ->when($request->filled('payment_status'), fn ($query) => $query->whereHas('invoice.payments', fn ($payment) => $payment->where('status', $request->string('payment_status'))))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($nested) => $nested->where('full_name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('reference', 'like', $term)
                    ->orWhereHas('invoice', fn ($invoice) => $invoice->where('reference', 'like', $term))
                    ->orWhereHas('references', fn ($reference) => $reference->where('full_name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term)));
            })
            ->latest('submitted_at')->paginate(15)->withQueryString();

        return view('admin.applications.index', ['applications' => $applications, 'statuses' => ApplicationStatus::cases()]);
    }

    public function show(MembershipApplication $application, TransitionMembershipApplication $transition): View
    {
        $application->load(['package', 'references.member', 'statusHistory.actor', 'internalNotes.author', 'documents.reviewer', 'invoice.items', 'invoice.payments']);

        return view('admin.applications.show', ['application' => $application, 'statuses' => $transition->availableFor($application, request()->user())]);
    }

    public function transition(TransitionMembershipApplicationRequest $request, MembershipApplication $application, TransitionMembershipApplication $transition): RedirectResponse
    {
        $transition->handle($application, $request->user(), ApplicationStatus::from($request->string('status')->value()), $request->validated('note'));

        return back()->with('status', 'Application status updated.');
    }

    public function resendDocumentLink(Request $request, MembershipApplication $application, TransitionMembershipApplication $transition): RedirectResponse
    {
        $transition->resendDocumentRequest($application, $request->user());

        return back()->with('status', 'A fresh, time-limited upload link was sent to the applicant.');
    }
}
