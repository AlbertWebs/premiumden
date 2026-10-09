<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $member): bool
    {
        return $user->canViewMember($member);
    }

    public function message(User $user, User $member): bool
    {
        return $user->canMessageMember($member);
    }

    public function updateProfile(User $user, User $member): bool
    {
        return $user->is($member) && $user->hasActiveMembership();
    }
}
