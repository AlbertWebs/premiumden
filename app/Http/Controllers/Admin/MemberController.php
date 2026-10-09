<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Models\MembershipPackage;
use App\Models\MembershipCard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class MemberController
{
    public function updateCard(Request $request, MembershipCard $card): RedirectResponse
    {
        $data = $request->validate([
            'physical_card_reference' => ['nullable', 'string', 'max:100', Rule::unique('membership_cards', 'physical_card_reference')->ignore($card->id)],
            'status' => ['required', 'in:active,suspended,lost,replaced,inactive'],
        ]);
        $card->update($data);
        app(\App\Services\RecordAuditEvent::class)->handle('member_card.hardware_reference_updated', $card, [
            'reference_configured' => filled($data['physical_card_reference']), 'status' => $data['status'],
        ], $request->user()->id);
        return back()->with('status', 'Card record updated. Physical chip programming is handled by the card issuer.');
    }

    public function updateStatus(Request $request, User $member): RedirectResponse
    {
        abort_unless($member->hasRole(UserRole::Member), 404);
        $data = $request->validate(['status' => ['required', 'in:active,suspended']]);
        $membership = $member->membership;
        abort_unless($membership, 404);
        $active = $data['status'] === 'active';
        if ($active && $membership->expires_at?->isBefore(today())) {
            return back()->withErrors(['status' => 'This membership term has expired. Complete renewal before reactivating access.']);
        }
        app(\App\Services\TransitionMembershipApplication::class)->handle(
            $membership->application,
            $request->user(),
            $active ? \App\Enums\ApplicationStatus::MembershipActivated : \App\Enums\ApplicationStatus::Suspended,
        );
        return back()->with('status', $active ? 'Membership reactivated.' : 'Membership suspended.');
    }

    public function index(Request $request): View
    {
        $members = User::query()->where('role', UserRole::Member)->with(['membershipPackage', 'membership.card', 'profile'])
            ->when($request->filled('status'), fn ($query) => $query->where('membership_active', $request->string('status')->value() === 'active'))
            ->when($request->filled('package'), fn ($query) => $query->where('membership_package_id', $request->integer('package')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($nested) => $nested->where('name', 'like', $term)->orWhere('email', 'like', $term)
                    ->orWhereHas('membership', fn ($membership) => $membership->where('number', 'like', $term))
                    ->orWhereHas('membership.application', fn ($application) => $application->where('phone', 'like', $term))
                    ->orWhereHas('profile', fn ($profile) => $profile->where('company', 'like', $term)->orWhere('industry', 'like', $term)));
            })->orderBy('name')->paginate(25)->withQueryString();
        return view('admin.members.index', ['members' => $members, 'packages' => MembershipPackage::orderBy('display_order')->get()]);
    }
}
