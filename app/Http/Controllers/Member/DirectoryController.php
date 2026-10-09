<?php

namespace App\Http\Controllers\Member;

use App\Models\MembershipPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectoryController
{
    public function index(Request $request): View
    {
        $viewer = $request->user()->loadMissing('membershipPackage');
        $level = $viewer->membershipPackage->networking_level;

        $members = User::query()->currentMember()
            ->whereHas('membershipPackage', fn (Builder $query) => $query->where('is_active', true)->where('networking_level', '<=', $level))
            ->whereHas('profile', fn (Builder $query) => $query->where('is_listed', true))
            ->with(['membershipPackage', 'profile'])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn (Builder $nested) => $nested->where('name', 'like', $term)
                    ->orWhereHas('profile', fn (Builder $profile) => $profile->where('company', 'like', $term)->orWhere('job_title', 'like', $term)->orWhere('industry', 'like', $term)->orWhere('business_category', 'like', $term)->orWhere('location', 'like', $term)));
            })
            ->when($request->filled('industry'), fn (Builder $query) => $query->whereHas('profile', fn (Builder $profile) => $profile->where('industry', $request->string('industry'))))
            ->when($request->filled('location'), fn (Builder $query) => $query->whereHas('profile', fn (Builder $profile) => $profile->where('location', $request->string('location'))))
            ->when($request->filled('package'), fn (Builder $query) => $query->whereHas('membershipPackage', fn (Builder $package) => $package->where('is_active', true)->where('slug', $request->string('package'))->where('networking_level', '<=', $level)))
            ->orderBy('name')->paginate(12)->withQueryString();

        $permittedPackages = MembershipPackage::query()->where('is_active', true)->where('networking_level', '<=', $level)->orderBy('display_order')->get();
        $industries = \App\Models\MemberProfile::query()->where('is_listed', true)->whereHas('user', fn (Builder $query) => $query->currentMember()->whereHas('membershipPackage', fn (Builder $package) => $package->where('is_active', true)->where('networking_level', '<=', $level)))->whereNotNull('industry')->distinct()->orderBy('industry')->pluck('industry');

        return view('member.directory.index', compact('members', 'permittedPackages', 'industries'));
    }

    public function show(Request $request, User $member): View
    {
        abort_unless($request->user()->can('view', $member) && $member->profile?->is_listed, 404);

        return view('member.directory.show', ['member' => $member->load(['profile', 'membershipPackage'])]);
    }
}
