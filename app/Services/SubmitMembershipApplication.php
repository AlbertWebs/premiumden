<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Mail\ApplicantApplicationReceived;
use App\Mail\ApplicationDraftSaved;
use App\Mail\MemberNominationReceived;
use App\Models\ApplicationStatusHistory;
use App\Models\MembershipApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SubmitMembershipApplication
{
    public function handle(array $data, bool $saveDraft = false, ?MembershipApplication $existing = null): MembershipApplication
    {
        $draftToken = $saveDraft ? Str::random(64) : null;
        $application = DB::transaction(function () use ($data, $saveDraft, $existing, $draftToken): MembershipApplication {
            $fromStatus = $existing?->status;
            $application = $existing ?? new MembershipApplication;
            $application->fill([
                'reference' => $application->reference ?: 'PBD-'.now()->format('ym').'-'.Str::upper(Str::random(7)),
                'membership_package_id' => $data['membership_package_id'],
                'status' => $saveDraft ? ApplicationStatus::Draft : ApplicationStatus::Submitted,
                'full_name' => $data['full_name'], 'email' => strtolower($data['email']), 'phone' => $data['phone'],
                'company' => $data['company'], 'job_title' => $data['job_title'], 'industry' => $data['industry'],
                'location' => $data['location'], 'biography' => $data['biography'] ?? null,
                'company_description' => $data['company_description'] ?? null,
                'employee_count' => $data['employee_count'] ?? null,
                'consent_at' => $saveDraft ? null : now(), 'submitted_at' => $saveDraft ? null : now(),
                'draft_token_hash' => $draftToken ? hash('sha256', $draftToken) : null,
                'draft_expires_at' => $draftToken ? now()->addDays(14) : null,
            ])->save();

            $application->references()->delete();
            foreach ($data['references'] as $referenceData) {
                $member = User::query()->currentMember()->whereHas('membership', fn ($membership) => $membership->whereRaw('LOWER(number) = ?', [strtolower(trim($referenceData['membership_number']))]))
                    ->whereHas('membershipPackage', fn ($package) => $package->where('is_active', true))
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($referenceData['full_name']))])->first();
                if (! $member) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'references' => 'A member name and membership number did not match an active member. Please check both details.',
                    ]);
                }
                $application->references()->create([
                    'full_name' => $member->name, 'email' => $member->email,
                    'membership_number' => $referenceData['membership_number'], 'member_user_id' => $member->id,
                ]);
            }

            if (! $saveDraft && $fromStatus !== ApplicationStatus::Submitted) {
                ApplicationStatusHistory::create([
                    'membership_application_id' => $application->id, 'from_status' => $fromStatus,
                    'to_status' => ApplicationStatus::Submitted, 'actor_id' => null,
                    'note' => $fromStatus ? 'Application draft submitted by applicant.' : 'Application submitted by applicant.', 'occurred_at' => now(),
                ]);
            } elseif (! $application->wasRecentlyCreated && $saveDraft) {
                // Draft saves do not enter the review timeline until the applicant submits.
            }
            return $application;
        });

        if (! $saveDraft && ($data['company_profile_file'] ?? null) instanceof \Illuminate\Http\UploadedFile) {
            $file = $data['company_profile_file'];
            $path = $file->storeAs('applications/'.$application->id.'/documents', Str::uuid().'.'.($file->guessExtension() ?: 'bin'), 'local');
            $application->documents()->updateOrCreate(['document_type' => 'company_profile'], [
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 180),
                'storage_path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => $file->getSize(),
                'status' => 'pending',
            ]);
        }

        $application->load('package');
        try {
            if ($saveDraft && $draftToken) {
                $expiry = now()->addDays(14);
                $url = URL::temporarySignedRoute('application.resume', $expiry, ['reference' => $application->reference, 'token' => $draftToken]);
                Mail::to($application->email)->send(new ApplicationDraftSaved($application, $url, $expiry->format('j F Y, H:i T')));
            } elseif (! $saveDraft) {
                $url = URL::temporarySignedRoute('application.status', now()->addDays(90), ['reference' => $application->reference]);
                Mail::to($application->email)->send(new ApplicantApplicationReceived($application, $url));
                foreach ($application->references()->with('member')->get() as $reference) {
                    $expires = now()->addDays(14);
                    $acceptUrl = URL::temporarySignedRoute('application.reference.respond', $expires, ['reference' => $reference->id, 'response' => 'accepted']);
                    $declineUrl = URL::temporarySignedRoute('application.reference.respond', $expires, ['reference' => $reference->id, 'response' => 'declined']);
                    Mail::to($reference->member->email)->send(new MemberNominationReceived($application, $reference, $acceptUrl, $declineUrl));
                }
            }
        } catch (\Throwable $exception) {
            Log::error('Unable to send application email.', ['application_id' => $application->id, 'exception' => $exception::class]);
        }
        if (! $saveDraft) app(RecordAuditEvent::class)->handle('application.submitted', $application, ['package_id' => $application->membership_package_id]);
        return $application;
    }
}
