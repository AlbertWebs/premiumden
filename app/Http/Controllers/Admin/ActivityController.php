<?php

namespace App\Http\Controllers\Admin;

use App\Models\SocietyActivity;
use App\Services\RecordAuditEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ActivityController
{
    public function index(): View { return view('admin.activities.index', ['activities' => SocietyActivity::with('creator')->latest()->paginate(20)]); }
    public function create(): View { return view('admin.activities.edit', ['activity' => new SocietyActivity]); }
    public function edit(SocietyActivity $activity): View { return view('admin.activities.edit', compact('activity')); }
    public function store(Request $request, RecordAuditEvent $audit): RedirectResponse { return $this->save($request, new SocietyActivity, $audit); }
    public function update(Request $request, SocietyActivity $activity, RecordAuditEvent $audit): RedirectResponse { return $this->save($request, $activity, $audit); }

    private function save(Request $request, SocietyActivity $activity, RecordAuditEvent $audit): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'], 'category' => ['required', 'string', 'max:80'],
            'excerpt' => ['nullable', 'string', 'max:500'], 'body' => ['required', 'string', 'max:12000'],
            'visibility' => ['required', 'in:public,members'], 'status' => ['required', 'in:draft,published'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $data['slug'] = Str::slug($data['title']);
        if (SocietyActivity::where('slug', $data['slug'])->where('id', '!=', $activity->id ?? 0)->exists()) $data['slug'] .= '-'.Str::lower(Str::random(5));
        if ($request->hasFile('featured_image')) {
            if ($activity->featured_image_path) Storage::disk('public')->delete($activity->featured_image_path);
            $data['featured_image_path'] = $request->file('featured_image')->store('activities', 'public');
        }
        unset($data['featured_image']);
        $data['published_at'] = $data['status'] === 'published' ? ($activity->published_at ?? now()) : null;
        $data['created_by'] = $activity->exists ? $activity->created_by : $request->user()->id;
        $activity->fill($data)->save();
        $audit->handle('society_activity.saved', $activity, ['status' => $activity->status, 'visibility' => $activity->visibility], $request->user()->id);
        return redirect()->route('admin.activities.index')->with('status', 'Society activity saved.');
    }
}
