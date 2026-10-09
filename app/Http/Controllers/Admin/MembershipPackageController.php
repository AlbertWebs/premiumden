<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipTier;
use App\Models\MembershipPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipPackageController
{
    public function index(): View
    {
        return view('admin.packages.index', ['packages' => MembershipPackage::orderBy('display_order')->get()]);
    }

    public function edit(MembershipPackage $package): View
    {
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, MembershipPackage $package): RedirectResponse
    {
        abort_if($package->slug !== MembershipTier::tryFrom($package->slug)?->value, 422, 'This package slug is not supported.');
        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'benefits' => ['nullable', 'string', 'max:5000'],
            'renewal_months' => ['required', 'integer', 'between:1,120'],
            'networking_level' => ['required', 'integer', 'between:1,100'],
            'display_order' => ['required', 'integer', 'between:0,65535'],
            'is_active' => ['required', 'boolean'],
        ]);
        $data['benefits'] = collect(preg_split('/\r\n|\r|\n/', $data['benefits'] ?? ''))->map(fn (string $benefit) => trim($benefit))->filter()->values()->all();
        $package->update($data);
        app(\App\Services\RecordAuditEvent::class)->handle('membership_package.updated', $package, [
            'price' => $package->price, 'is_active' => $package->is_active, 'renewal_months' => $package->renewal_months,
        ], $request->user()->id);

        return back()->with('status', 'Membership package settings updated.');
    }
}
