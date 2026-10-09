<?php

namespace App\Http\Controllers\Public;

use App\Enums\ApplicationStatus;
use App\Http\Requests\UploadApplicantDocumentsRequest;
use App\Models\MembershipApplication;
use App\Services\StoreApplicantDocuments;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApplicantDocumentController
{
    public function create(string $reference, string $token): View
    {
        $application = $this->authorizedApplication($reference, $token);

        return view('public.documents-upload', ['application' => $application->load('package', 'documents')]);
    }

    public function store(UploadApplicantDocumentsRequest $request, string $reference, string $token, StoreApplicantDocuments $store): RedirectResponse
    {
        $application = $this->authorizedApplication($reference, $token);
        $store->handle($application, $request->only(['photo', 'identity']), $request->validated('identity_type'));

        return back()->with('status', 'Your documents were uploaded securely. The membership team will review them.');
    }

    private function authorizedApplication(string $reference, string $token): MembershipApplication
    {
        $application = MembershipApplication::query()->where('reference', $reference)->firstOrFail();
        abort_unless($application->status === ApplicationStatus::DocumentsRequired, 404);
        abort_unless($application->document_upload_expires_at?->isFuture(), 403);
        abort_unless(hash_equals((string) $application->document_upload_token_hash, hash('sha256', $token)), 403);

        return $application;
    }
}
