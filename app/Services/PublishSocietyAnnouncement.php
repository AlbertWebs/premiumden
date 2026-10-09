<?php

namespace App\Services;

use App\Models\SocietyAnnouncement;
use App\Models\User;
use App\Notifications\SocietyAnnouncementPublished;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishSocietyAnnouncement
{
    public function handle(SocietyAnnouncement $announcement, ?User $actor = null): void
    {
        DB::transaction(function () use ($announcement, $actor): void {
            $locked = SocietyAnnouncement::whereKey($announcement->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'published') { return; }

            $recipients = User::query()->currentMember()->whereHas('membershipPackage', fn ($query) => $query->where('is_active', true));
            if ($locked->target === 'packages') {
                $packageIds = $locked->package_ids ?? [];
                if ($packageIds === []) { throw ValidationException::withMessages(['target' => 'Choose at least one membership package.']); }
                $recipients->whereIn('membership_package_id', $packageIds);
            } elseif ($locked->target === 'selected') {
                $recipients->whereIn('users.id', $locked->selectedMembers()->select('users.id'));
            }

            $locked->update(['status' => 'published', 'published_at' => now()]);
            app(RecordAuditEvent::class)->handle('announcement.published', $locked, ['target' => $locked->target], $actor?->id);
            $announcement->refresh();
            $recipients->orderBy('id')->chunkById(250, function ($members) use ($announcement): void {
                foreach ($members as $member) { $member->notify(new SocietyAnnouncementPublished($announcement)); }
            });
        });
    }
}
