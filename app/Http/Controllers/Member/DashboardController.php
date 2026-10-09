<?php

namespace App\Http\Controllers\Member;

use App\Models\User;
use App\Models\Event;
use App\Models\Article;
use App\Models\Conversation;
use App\Models\SocietyAnnouncement;
use App\Models\MembershipApplication;
use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(): View
    {
        $member = request()->user()->loadMissing(['membershipPackage', 'profile', 'membership.application.references.member.membershipPackage']);
        $membershipStatus = $member->membership?->status ?? ($member->membership_active ? 'active' : 'inactive');
        $visibleMembers = User::query()->currentMember()
            ->whereHas('membershipPackage', fn (Builder $query) => $query->where('is_active', true)->where('networking_level', '<=', $member->membershipPackage->networking_level))
            ->whereHas('profile', fn (Builder $query) => $query->where('is_listed', true))
            ->where('id', '!=', $member->id)->count();
        $profileFields = ['company', 'job_title', 'industry', 'location', 'biography'];
        $completedFields = collect($profileFields)->filter(fn (string $field) => filled($member->profile?->$field))->count();
        $application = $member->membership?->application
            ?? MembershipApplication::query()->where('email', $member->email)->latest('submitted_at')->first();
        $recommendations = $application?->references()->with('member.membershipPackage')->get() ?? collect();
        $networkCount = max($visibleMembers, $recommendations->pluck('member_user_id')->filter()->unique()->count());
        $upcomingEvents = Event::query()->where('status', 'published')->where('published_at', '<=', now())->where('starts_at', '>=', now())
            ->where(function (Builder $query) use ($member): void {
                $query->whereIn('visibility', ['public', 'members'])->orWhere(fn (Builder $packages) => $packages->where('visibility', 'packages')->whereJsonContains('package_ids', $member->membership_package_id));
            })->orderBy('starts_at')->limit(3)->get();

        $eligibleConversations = Conversation::query()
            ->whereHas('participants', fn (Builder $query) => $query->whereKey($member->id))
            ->whereHas('participants', fn (Builder $query) => $query->currentMember()->where('users.id', '!=', $member->id)
                ->whereHas('membershipPackage', fn (Builder $package) => $package->where('is_active', true)));
        $withUnreadCounts = fn () => ['messages as unread_messages_count' => fn (Builder $query) => $query->where('sender_id', '!=', $member->id)->whereNull('read_at')];
        $unreadMessageCount = (clone $eligibleConversations)->withCount($withUnreadCounts())->get()->sum('unread_messages_count');
        $recentConversations = (clone $eligibleConversations)
            ->with(['participants' => fn ($query) => $query->where('users.id', '!=', $member->id), 'latestMessage.sender'])
            ->withCount($withUnreadCounts())->orderByDesc('updated_at')->limit(2)->get();

        $recentNotifications = $member->notifications()->latest()->limit(3)->get();
        $latestArticles = Article::query()->where('status', ArticleStatus::Published)->where('published_at', '<=', now())
            ->with('author')->latest('published_at')->limit(2)->get();
        $recentAnnouncements = SocietyAnnouncement::query()->where('status', 'published')->where('published_at', '<=', now())
            ->where(fn (Builder $query) => $query->where('target', 'all_members')
                ->orWhere(fn (Builder $packages) => $packages->where('target', 'packages')->whereJsonContains('package_ids', (int) $member->membership_package_id))
                ->orWhere(fn (Builder $selected) => $selected->where('target', 'selected')->whereHas('selectedMembers', fn (Builder $recipients) => $recipients->whereKey($member->id))))
            ->latest('published_at')->limit(2)->get();
        $suggestedMembers = User::query()->currentMember()
            ->whereHas('membershipPackage', fn (Builder $query) => $query->where('is_active', true)->where('networking_level', '<=', $member->membershipPackage->networking_level))
            ->whereHas('profile', fn (Builder $query) => $query->where('is_listed', true))
            ->where('id', '!=', $member->id)->with(['membershipPackage', 'profile'])
            ->latest('created_at')->limit(3)->get();

        return view('member.dashboard', compact(
            'member', 'membershipStatus', 'networkCount', 'recommendations', 'profileFields', 'completedFields', 'upcomingEvents',
            'unreadMessageCount', 'recentConversations', 'recentNotifications', 'latestArticles', 'recentAnnouncements', 'suggestedMembers',
        ));
    }
}
