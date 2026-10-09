<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class StartMemberConversation
{
    public function handle(User $sender, User $recipient): Conversation
    {
        if (! $sender->canMessageMember($recipient) || ! $recipient->hasActiveMembership()) {
            throw new AuthorizationException('You cannot start a conversation with this member.');
        }

        $participantIds = [$sender->id, $recipient->id];
        sort($participantIds);
        $hash = hash('sha256', implode(':', $participantIds));

        return DB::transaction(function () use ($sender, $participantIds, $hash): Conversation {
            $conversation = Conversation::query()->firstOrCreate(
                ['participant_hash' => $hash],
                ['created_by' => $sender->id],
            );
            $conversation->participants()->syncWithoutDetaching($participantIds);

            return $conversation;
        });
    }
}
