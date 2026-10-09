<?php

namespace App\Services;

use App\Mail\MembershipRenewalReminder;
use App\Models\AuditEvent;
use App\Models\Membership;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMembershipRenewalReminders
{
    public function handle(): int
    {
        $sent = 0;
        $start = today()->addDays(30);
        $end = today()->addDays(31);
        Membership::query()->where('status', 'active')->whereBetween('expires_at', [$start, $end])->with(['user', 'package'])->orderBy('id')->chunkById(100, function ($memberships) use (&$sent): void {
            foreach ($memberships as $membership) {
                $expiry = $membership->expires_at?->toDateString();
                $alreadySent = AuditEvent::where('action', 'membership.renewal_reminder_sent')
                    ->where('resource_type', Membership::class)->where('resource_id', (string) $membership->id)
                    ->whereJsonContains('metadata->expires_at', $expiry)->exists();
                if ($alreadySent || ! $membership->user?->membership_active) continue;
                try {
                    $support = SiteSetting::value('public_contact_email') ?: (string) config('services.contact.to_address', '');
                    Mail::to($membership->user->email)->send(new MembershipRenewalReminder($membership, $support));
                    app(RecordAuditEvent::class)->handle('membership.renewal_reminder_sent', $membership, ['expires_at' => $expiry]);
                    $sent++;
                } catch (\Throwable $exception) {
                    Log::error('Unable to send membership renewal reminder.', ['membership_id' => $membership->id, 'exception' => $exception::class]);
                }
            }
        });
        return $sent;
    }
}
