<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController
{
    public function edit(): View { return view('member.settings'); }
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['email_message_notifications' => ['nullable', 'boolean'], 'email_society_updates' => ['nullable', 'boolean']]);
        $request->user()->update([
            'email_message_notifications' => $request->boolean('email_message_notifications'),
            'email_society_updates' => $request->boolean('email_society_updates'),
        ]);
        app(\App\Services\RecordAuditEvent::class)->handle('member.preferences_updated', $request->user(), ['keys' => array_keys($data)], $request->user()->id);
        return back()->with('status', 'Your communication preferences have been saved.');
    }
}
