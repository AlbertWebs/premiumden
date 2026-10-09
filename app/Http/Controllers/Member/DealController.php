<?php

namespace App\Http\Controllers\Member;

use App\Models\MemberDeal;
use App\Models\MemberDealDocument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DealController
{
    public function index(Request $request): View
    {
        $member = $request->user()->loadMissing('membershipPackage');
        $deals = MemberDeal::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))
            ->where(fn ($query) => $query->whereNull('closes_at')->orWhereDate('closes_at', '>=', today()))
            ->where(fn ($query) => $query->whereNull('minimum_package_id')->orWhereHas('targetPackage', fn ($package) => $package->where('networking_level', '<=', ($member->membershipPackage?->networking_level ?? -1))))
            ->with('documents')
            ->with(['applications' => fn ($query) => $query->where('user_id', $request->user()->id)])
            ->orderBy('display_order')
            ->orderBy('closes_at')
            ->paginate(9);

        return view('member.deals.index', compact('deals'));
    }

    public function apply(Request $request, MemberDeal $deal): RedirectResponse
    {
        abort_unless($this->isAvailableTo($deal, $request->user()), 404);

        $data = $request->validate(['note' => ['nullable', 'string', 'max:1200']]);
        $application = $deal->applications()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['note' => $data['note'] ?? null, 'status' => 'pending', 'applied_at' => now()],
        );

        return back()->with($application->wasRecentlyCreated ? 'status' : 'deal_application_exists', $application->wasRecentlyCreated
            ? 'Your interest has been received. The Den team will review it and follow up with you.'
            : 'You have already submitted your interest for this opportunity.');
    }

    public function document(MemberDeal $deal, MemberDealDocument $document, Request $request)
    {
        abort_unless($document->member_deal_id === $deal->id && $this->isAvailableTo($deal, $request->user()), 404);

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    private function isAvailableTo(MemberDeal $deal, User $member): bool
    {
        $member->loadMissing('membershipPackage');

        return $deal->is_active
            && (! $deal->starts_at || $deal->starts_at->isPast() || $deal->starts_at->isToday())
            && (! $deal->closes_at || $deal->closes_at->isFuture() || $deal->closes_at->isToday())
            && (! $deal->minimum_package_id || ($deal->targetPackage()->value('networking_level') ?? PHP_INT_MAX) <= ($member->membershipPackage?->networking_level ?? -1));
    }
}
