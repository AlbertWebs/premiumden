<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SaveEventRequest;
use App\Models\Event;
use App\Models\MembershipPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController
{
    public function index(): View
    {
        return view('admin.events.index', ['events' => Event::with('creator')->latest('starts_at')->paginate(20)]);
    }

    public function create(): View { return $this->form(new Event); }
    public function edit(Event $event): View { return $this->form($event); }

    public function store(SaveEventRequest $request): RedirectResponse
    {
        $event = $this->save(new Event, $request);
        return redirect()->route('admin.events.edit', $event)->with('status', 'Event saved.');
    }

    public function update(SaveEventRequest $request, Event $event): RedirectResponse
    {
        $this->save($event, $request);
        return back()->with('status', 'Event updated.');
    }

    private function save(Event $event, SaveEventRequest $request): Event
    {
        $data = $request->validated();
        $upload = $data['featured_image'] ?? null;
        unset($data['featured_image']);
        $previousImage = $event->featured_image_path;
        $newImage = $upload?->storePublicly('events', 'public');
        if ($newImage) { $data['featured_image_path'] = $newImage; }
        $baseSlug = Str::slug(filled($data['slug'] ?? null) ? $data['slug'] : ($event->slug ?: $data['title']));
        $slug = $baseSlug;
        for ($suffix = 2; Event::where('slug', $slug)->where('id', '<>', $event->getKey() ?? 0)->exists(); $suffix++) {
            $slug = $baseSlug.'-'.$suffix;
        }
        $data['slug'] = $slug;
        $data['package_ids'] = $data['visibility'] === 'packages' ? array_values(array_map('intval', $data['package_ids'] ?? [])) : null;
        if ($data['visibility'] === 'packages' && empty($data['package_ids'])) {
            abort(422, 'Choose at least one membership package for this event.');
        }
        $data['published_at'] = $data['status'] === 'published' ? ($event->published_at ?? now()) : null;
        if (! $event->exists) { $data['created_by'] = $request->user()->id; }
        try { $event->fill($data)->save(); }
        catch (\Throwable $exception) { if ($newImage) { Storage::disk('public')->delete($newImage); } throw $exception; }
        if ($previousImage && $newImage && $previousImage !== $newImage) { Storage::disk('public')->delete($previousImage); }
        app(\App\Services\RecordAuditEvent::class)->handle('event.saved', $event, ['status' => $event->status, 'visibility' => $event->visibility], $request->user()->id);
        return $event;
    }

    private function form(Event $event): View
    {
        return view('admin.events.edit', ['event' => $event, 'packages' => MembershipPackage::where('is_active', true)->orderBy('display_order')->get()]);
    }
}
