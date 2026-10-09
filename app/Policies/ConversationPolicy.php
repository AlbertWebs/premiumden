<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $user->hasActiveMembership()
            && $conversation->participants()->whereKey($user->id)->exists();
    }

    public function send(User $user, Conversation $conversation): bool
    {
        if (! $this->view($user, $conversation)) {
            return false;
        }

        return $conversation->participants()->currentMember()->where('users.id', '!=', $user->id)
            ->whereHas('membershipPackage', fn ($query) => $query->where('is_active', true))
            ->exists();
    }
}
