<?php

namespace App\Http\Controllers\Public;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class EventController
{
    public function index(): View
    {
        $events = Event::query()->where('status', 'published')->where('published_at', '<=', now())->where('visibility', 'public')->where('starts_at', '>=', now())->orderBy('starts_at')->paginate(12);
        return view('public.events.index', ['events' => $events, 'memberView' => false]);
    }

    public function show(string $slug): View
    {
        $event = Event::query()->where('slug', $slug)->where('status', 'published')->where('published_at', '<=', now())->where('visibility', 'public')->firstOrFail();
        return view('public.events.show', ['event' => $event, 'memberView' => false]);
    }

    public function memberIndex(): View
    {
        $packageId = request()->user()->membership_package_id;
        $events = Event::query()->where('status', 'published')->where('published_at', '<=', now())->where('starts_at', '>=', now())
            ->where(function (Builder $query) use ($packageId): void {
                $query->whereIn('visibility', ['public', 'members'])->orWhere(fn (Builder $packages) => $packages->where('visibility', 'packages')->whereJsonContains('package_ids', $packageId));
            })->orderBy('starts_at')->paginate(12);
        return view('public.events.index', ['events' => $events, 'memberView' => true]);
    }

    public function memberShow(string $slug): View
    {
        $packageId = request()->user()->membership_package_id;
        $event = Event::query()->where('slug', $slug)->where('status', 'published')->where('published_at', '<=', now())
            ->where(function (Builder $query) use ($packageId): void {
                $query->whereIn('visibility', ['public', 'members'])->orWhere(fn (Builder $packages) => $packages->where('visibility', 'packages')->whereJsonContains('package_ids', $packageId));
            })->firstOrFail();
        return view('public.events.show', ['event' => $event, 'memberView' => true]);
    }
}
