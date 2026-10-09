<?php

namespace App\Http\Controllers\Member;

use Illuminate\View\View;

class MembershipController
{
    public function benefits(): View
    {
        return view('member.benefits', ['member' => request()->user()->loadMissing('membershipPackage')]);
    }

    public function show(): View
    {
        $member = request()->user()->loadMissing(['membershipPackage', 'membership.card', 'membership.welcomePackage']);
        return view('member.membership', ['member' => $member, 'membership' => $member->membership]);
    }
}
