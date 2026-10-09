<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicantDocumentStatus;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\ApplicantDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApplicantDocumentController
{
    public function show(Request $request, ApplicantDocument $document): BinaryFileResponse
    {
        abort_unless($request->user()->hasRole(UserRole::SuperAdministrator) || $request->user()->hasRole(UserRole::MembershipAdministrator) || $request->user()->hasRole(UserRole::RiskTeam), 403);
        abort_unless(Storage::disk('local')->exists($document->storage_path), 404);

        $response = response()->file(Storage::disk('local')->path($document->storage_path), [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    public function decide(Request $request, ApplicantDocument $document): \Illuminate\Http\RedirectResponse
    {
        abort_unless($request->user()->hasRole(UserRole::SuperAdministrator) || $request->user()->hasRole(UserRole::MembershipAdministrator), 403);
        abort_unless($document->application()->where('status', ApplicationStatus::DocumentsRequired->value)->exists(), 404);
        $data = $request->validate(['status' => ['required', 'in:approved,rejected,replacement_requested'], 'review_note' => ['nullable', 'string', 'max:2000']]);
        $document->update(['status' => ApplicantDocumentStatus::from($data['status']), 'reviewed_by' => $request->user()->id, 'review_note' => $data['review_note'] ?? null, 'reviewed_at' => now()]);
        app(\App\Services\RecordAuditEvent::class)->handle('applicant_document.reviewed', $document, ['status' => $data['status']], $request->user()->id);

        return back()->with('status', 'Document review saved.');
    }
}
