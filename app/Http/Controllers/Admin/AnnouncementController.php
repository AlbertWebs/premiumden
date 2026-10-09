<?php

namespace App\Http\Controllers\Admin;

use App\Models\MembershipPackage;
use App\Models\SocietyAnnouncement;
use App\Models\User;
use App\Services\PublishSocietyAnnouncement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController
{
    public function index(): View
    {
        return view('admin.announcements.index', ['announcements' => SocietyAnnouncement::with('creator')->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return $this->form(new SocietyAnnouncement);
    }

    public function edit(SocietyAnnouncement $announcement): View
    {
        abort_unless($announcement->status === 'draft', 404);
        $announcement->load('selectedMembers');
        return $this->form($announcement);
    }

    public function update(Request $request, SocietyAnnouncement $announcement, PublishSocietyAnnouncement $publish): RedirectResponse
    {
        abort_unless($announcement->status === 'draft', 404);
        return $this->save($request, $announcement, $publish);
    }

    public function store(Request $request, PublishSocietyAnnouncement $publish): RedirectResponse
    {
        return $this->save($request, new SocietyAnnouncement, $publish);
    }

    private function save(Request $request, SocietyAnnouncement $announcement, PublishSocietyAnnouncement $publish): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'], 'body' => ['required', 'string', 'max:5000'],
            'target' => ['required', Rule::in(['all_members', 'packages', 'selected'])],
            'package_ids' => [Rule::requiredIf($request->input('target') === 'packages'), 'nullable', 'array', 'min:1'],
            'package_ids.*' => ['integer', 'distinct', 'exists:membership_packages,id'],
            'member_ids' => [Rule::requiredIf($request->input('target') === 'selected'), 'nullable', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')->where('role', 'member')->where('membership_active', true)],
            'action' => ['required', Rule::in(['draft', 'publish'])],
        ]);
        if ($data['target'] === 'selected') {
            $eligible = User::query()->currentMember()->whereHas('membershipPackage', fn ($query) => $query->where('is_active', true))
                ->whereIn('id', $data['member_ids'])->count();
            if ($eligible !== count($data['member_ids'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['member_ids' => 'Select only members with a current active membership.']);
            }
        }

        DB::transaction(function () use ($data, $request, $announcement): void {
            $announcement->fill([
                'title' => $data['title'], 'body' => $data['body'], 'target' => $data['target'],
                'package_ids' => $data['target'] === 'packages' ? array_values(array_map('intval', $data['package_ids'])) : null,
                'status' => 'draft', 'created_by' => $announcement->exists ? $announcement->created_by : $request->user()->id,
            ])->save();
            $announcement->selectedMembers()->sync($data['target'] === 'selected' ? $data['member_ids'] : []);
        });

        if ($data['action'] === 'publish') { $publish->handle($announcement, $request->user()); }

        return redirect()->route('admin.announcements.index')->with('status', $data['action'] === 'publish' ? 'Announcement delivered to its selected member audience.' : 'Announcement saved as a draft.');
    }

    private function form(SocietyAnnouncement $announcement): View
    {
        return view('admin.announcements.create', [
            'announcement' => $announcement,
            'packages' => MembershipPackage::where('is_active', true)->orderBy('display_order')->get(),
            'members' => User::query()->currentMember()->whereHas('membershipPackage', fn ($query) => $query->where('is_active', true))->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }
}
