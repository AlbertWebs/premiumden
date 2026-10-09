<?php

namespace App\Http\Controllers\Public;

use App\Models\SocietyActivity;
use Illuminate\View\View;

class ActivityController
{
    public function index(): View
    {
        return view('public.activities.index', ['activities' => SocietyActivity::where('status', 'published')->where('visibility', 'public')->where('published_at', '<=', now())->latest('published_at')->paginate(12)]);
    }

    public function memberIndex(): View
    {
        $member = request()->user();
        abort_unless($member?->hasActiveMembership(), 403);
        return view('public.activities.index', ['activities' => SocietyActivity::where('status', 'published')->where('published_at', '<=', now())->latest('published_at')->paginate(12)]);
    }

    public function show(string $slug): View
    {
        $activity = SocietyActivity::where('slug', $slug)->where('status', 'published')->where('published_at', '<=', now())->firstOrFail();
        abort_if($activity->visibility === 'members' && ! auth()->user()?->hasActiveMembership(), 404);
        return view('public.activities.show', compact('activity'));
    }
}
