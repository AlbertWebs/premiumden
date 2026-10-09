<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\ApplicationStatusHistory;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;

class ExpireMemberships
{
    public function handle(): int
    {
        $expiredCount = 0;
        Membership::query()->where('status', 'active')->whereNotNull('expires_at')->whereDate('expires_at', '<', today())
            ->orderBy('id')->chunkById(100, function ($memberships) use (&$expiredCount): void {
                foreach ($memberships as $membership) {
                    DB::transaction(function () use ($membership, &$expiredCount): void {
                        $locked = Membership::query()->lockForUpdate()->findOrFail($membership->id);
                        if ($locked->status !== 'active' || ! $locked->expires_at?->isBefore(today())) return;
                        $locked->update(['status' => 'expired']);
                        $locked->user()->update(['membership_active' => false]);
                        $locked->card()->update(['status' => 'expired']);
                        $application = $locked->application;
                        if ($application->status === ApplicationStatus::MembershipActivated) {
                            $application->update(['status' => ApplicationStatus::Suspended]);
                            ApplicationStatusHistory::create([
                                'membership_application_id' => $application->id,
                                'from_status' => ApplicationStatus::MembershipActivated,
                                'to_status' => ApplicationStatus::Suspended,
                                'note' => 'Membership term expired.', 'occurred_at' => now(),
                            ]);
                            app(RecordAuditEvent::class)->handle('application.status_changed', $application, ['from' => 'membership_activated', 'to' => 'suspended']);
                        }
                        app(RecordAuditEvent::class)->handle('membership.expired', $locked, ['expired_at' => $locked->expires_at?->toDateString()]);
                        if ($card = $locked->card()->first()) app(RecordAuditEvent::class)->handle('member_card.status_changed', $card, ['status' => 'expired']);
                        $expiredCount++;
                    });
                }
            });
        return $expiredCount;
    }
}
