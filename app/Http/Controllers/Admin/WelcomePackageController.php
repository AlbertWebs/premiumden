<?php

namespace App\Http\Controllers\Admin;

use App\Models\WelcomePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomePackageController
{
    public function index(): View
    {
        $packages = WelcomePackage::with(['membership.user', 'membership.package'])->latest()->paginate(20);
        return view('admin.welcome-packages.index', compact('packages'));
    }

    public function update(Request $request, WelcomePackage $welcomePackage): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,prepared,dispatched,delivered'],
            'tracking_reference' => ['nullable', 'string', 'max:190'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $milestones = ['prepared' => 'prepared_at', 'dispatched' => 'dispatched_at', 'delivered' => 'delivered_at'];
        if (isset($milestones[$data['status']]) && ! $welcomePackage->{$milestones[$data['status']]}) {
            $data[$milestones[$data['status']]] = now();
        }
        $welcomePackage->update($data);
        app(\App\Services\RecordAuditEvent::class)->handle('welcome_package.updated', $welcomePackage, ['status' => $welcomePackage->status, 'tracking_reference' => $welcomePackage->tracking_reference], $request->user()->id);

        return back()->with('status', 'Welcome package progress updated.');
    }
}
