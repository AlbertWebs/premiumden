<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditEventController
{
    public function index(Request $request): View
    {
        $events = AuditEvent::with('actor')->latest('created_at')
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')->trim()))
            ->paginate(30)->withQueryString();
        return view('admin.audit.index', compact('events'));
    }
}
