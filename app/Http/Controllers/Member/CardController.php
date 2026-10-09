<?php

namespace App\Http\Controllers\Member;

use Illuminate\View\View;

class CardController
{
    public function show(): View
    {
        $membership = request()->user()->membership()->with(['package', 'card'])->first();

        return view('member.card', compact('membership'));
    }
}
