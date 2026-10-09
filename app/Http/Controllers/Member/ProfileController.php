<?php

namespace App\Http\Controllers\Member;

use App\Http\Requests\UpdateMemberProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProfileController
{
    public function edit(): View
    {
        return view('member.profile.edit', ['member' => request()->user()->load(['profile', 'membershipPackage'])]);
    }

    public function update(UpdateMemberProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $interests = collect(explode(',', $data['interests'] ?? ''))->map(fn ($interest) => trim($interest))->filter()->unique()->values()->all();
        unset($data['interests'], $data['photo']);
        $profile = $request->user()->profile()->firstOrNew();
        if ($request->hasFile('photo')) {
            if ($profile->photo_path) Storage::disk('local')->delete($profile->photo_path);
            $data['photo_path'] = $request->file('photo')->store('member-photos', 'local');
        }
        $request->user()->profile()->updateOrCreate([], $data + ['interests' => $interests, 'is_listed' => $request->boolean('is_listed')]);
        app(\App\Services\RecordAuditEvent::class)->handle('member.profile_updated', $request->user(), ['photo_changed' => $request->hasFile('photo')], $request->user()->id);

        return back()->with('status', 'Your profile has been updated.');
    }
}
