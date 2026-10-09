<?php

namespace App\Http\Controllers\Public;

use App\Http\Requests\StoreMembershipApplicationRequest;
use App\Models\MembershipApplication;
use App\Models\MembershipPackage;
use App\Services\SubmitMembershipApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipApplicationController
{
    public function create(Request $request): View
    {
        $packages = MembershipPackage::query()->where('is_active', true)->orderBy('display_order')->get();
        $selectedPackageId = $packages->firstWhere('slug', $request->query('package'))?->id;

        return view('public.application', compact('packages', 'selectedPackageId') + ['draft' => null, 'draftToken' => null]);
    }

    public function resume(string $reference, string $token): View
    {
        $draft = MembershipApplication::where('reference', $reference)->where('status', \App\Enums\ApplicationStatus::Draft)->firstOrFail();
        abort_unless($draft->draft_expires_at?->isFuture() && hash_equals((string) $draft->draft_token_hash, hash('sha256', $token)), 403);
        return view('public.application', ['packages' => MembershipPackage::query()->where('is_active', true)->orderBy('display_order')->get(), 'selectedPackageId' => null, 'draft' => $draft->load('references'), 'draftToken' => $token]);
    }

    public function store(StoreMembershipApplicationRequest $request, SubmitMembershipApplication $submit): RedirectResponse
    {
        $data = $request->validated();
        $saveDraft = (bool) ($data['save_draft'] ?? false);
        $existing = null;
        if (! empty($data['draft_reference'])) {
            $existing = MembershipApplication::where('reference', $data['draft_reference'])->where('status', \App\Enums\ApplicationStatus::Draft)->firstOrFail();
            abort_unless($existing->draft_expires_at?->isFuture() && ! empty($data['draft_token']) && hash_equals((string) $existing->draft_token_hash, hash('sha256', $data['draft_token'])), 403);
        }
        unset($data['save_draft'], $data['draft_reference'], $data['draft_token'], $data['consent']);
        $application = $submit->handle($data + ['consent' => $request->boolean('consent')], $saveDraft, $existing);

        if ($saveDraft) return redirect()->route('application.status', $application->reference)->with('status', 'Your draft is saved. A secure link to continue has been sent to your email.');
        return redirect()->route('application.received', $application->reference);
    }

    public function received(string $reference): View
    {
        $application = MembershipApplication::query()->with('package')->where('reference', $reference)->firstOrFail();

        return view('public.application-received', compact('application'));
    }

    public function lookup(Request $request): RedirectResponse
    {
        $request->merge(['reference' => strtoupper(trim((string) $request->input('reference')))]);
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:32', 'regex:/^PBD-[A-Z0-9]{4}-[A-Z0-9]{7}$/i'],
        ]);
        $application = MembershipApplication::query()->where('reference', $validated['reference'])->first();

        if (! $application) {
            return back()->withErrors(['reference' => 'We could not find an application with that reference. Check the code and try again.'])->withInput();
        }

        return redirect()->route('application.status', $application->reference);
    }

    public function show(string $reference): View
    {
        $application = MembershipApplication::query()->with(['package', 'membership.user'])->where('reference', $reference)->firstOrFail();
        $hasActiveAccount = $application->membership?->status === 'active'
            && ($application->membership->user?->hasActiveMembership() ?? false);

        return view('public.application-status', compact('application', 'hasActiveAccount'));
    }
}
