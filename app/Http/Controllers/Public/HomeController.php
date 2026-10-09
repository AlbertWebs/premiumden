<?php

namespace App\Http\Controllers\Public;

use App\Models\MembershipPackage;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Event;
use App\Models\SocietyActivity;
use Illuminate\View\View;

class HomeController
{
    public function __invoke(): View
    {
        return view('public.home', [
            'packages' => MembershipPackage::query()->where('is_active', true)->orderBy('display_order')->get(),
            'latestArticles' => Article::query()->where('status', ArticleStatus::Published)->where('published_at', '<=', now())->with(['author', 'category'])->latest('published_at')->limit(3)->get(),
            'upcomingEvents' => Event::query()->where('status', 'published')->where('published_at', '<=', now())->where('visibility', 'public')->where('starts_at', '>=', now())->orderBy('starts_at')->limit(3)->get(),
            'latestActivities' => SocietyActivity::query()->where('status', 'published')->where('visibility', 'public')->where('published_at', '<=', now())->latest('published_at')->limit(3)->get(),
        ]);
    }

    public function membership(): View
    {
        return view('public.membership', ['packages' => MembershipPackage::query()->where('is_active', true)->orderBy('display_order')->get()]);
    }

    public function package(MembershipPackage $package): View
    {
        abort_unless($package->is_active, 404);

        return view('public.package', compact('package'));
    }
}
