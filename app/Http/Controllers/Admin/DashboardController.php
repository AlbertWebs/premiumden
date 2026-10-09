<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\ArticleStatus;
use App\Models\MembershipApplication;
use App\Models\MembershipPackage;
use App\Models\User;
use App\Models\Article;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'pendingApplications' => MembershipApplication::whereIn('status', [ApplicationStatus::Submitted, ApplicationStatus::UnderReview, ApplicationStatus::Vetting])->count(),
            'activeMembers' => User::query()->currentMember()->whereHas('membershipPackage', fn ($query) => $query->where('is_active', true))->count(),
            'awaitingDocuments' => MembershipApplication::where('status', ApplicationStatus::DocumentsRequired)->count(),
            'awaitingPayments' => MembershipApplication::whereIn('status', [ApplicationStatus::InvoiceIssued, ApplicationStatus::AwaitingPayment])->count(),
            'articlesForReview' => Article::where('status', ArticleStatus::PendingReview)->count(),
            'packages' => MembershipPackage::withCount(['users' => fn ($query) => $query->currentMember()])->orderBy('display_order')->get(),
            'recentApplications' => MembershipApplication::with('package')->latest('submitted_at')->limit(6)->get(),
            'recentPayments' => Payment::with('invoice.application')->latest()->limit(6)->get(),
            'recentMembers' => User::where('role', \App\Enums\UserRole::Member)->with(['membershipPackage', 'membership'])->latest()->limit(6)->get(),
        ]);
    }
}
