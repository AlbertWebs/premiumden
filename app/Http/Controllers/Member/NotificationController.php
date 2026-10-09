<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return view('member.notifications.index', compact('notifications'));
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $record->markAsRead();

        if (($record->data['type'] ?? null) === 'new_member_message' && isset($record->data['conversation_id'])) {
            return redirect()->route('member.messages.show', $record->data['conversation_id']);
        }

        return back();
    }
}
