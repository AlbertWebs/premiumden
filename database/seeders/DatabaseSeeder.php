<?php

namespace Database\Seeders;

use App\Models\MembershipPackage;
use App\Models\User;
use App\Models\MemberProfile;
use App\Models\ArticleCategory;
use App\Enums\UserRole;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Membership;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Leadership', 'slug' => 'leadership'],
            ['name' => 'Enterprise', 'slug' => 'enterprise'],
            ['name' => 'Ideas & Practice', 'slug' => 'ideas-practice'],
            ['name' => 'Society Journal', 'slug' => 'society-journal'],
        ] as $order => $category) {
            ArticleCategory::updateOrCreate(['slug' => $category['slug']], $category + ['is_active' => true, 'display_order' => $order + 1]);
        }

        foreach ([
            ['name' => 'Diamond', 'description' => 'A private circle for established business leaders.', 'slug' => 'diamond', 'price' => null, 'benefits' => ['Curated peer network', 'Member directory access', 'Society briefings'], 'display_order' => 1, 'networking_level' => 1],
            ['name' => 'Gold', 'description' => 'A connected community for ambitious business professionals.', 'slug' => 'gold', 'price' => null, 'benefits' => ['Member directory access', 'Society briefings', 'Member introductions'], 'display_order' => 2, 'networking_level' => 2],
            ['name' => 'Platinum', 'description' => 'The society’s broadest member network access.', 'slug' => 'platinum', 'price' => null, 'benefits' => ['Full member directory access', 'Society briefings', 'Member introductions'], 'display_order' => 3, 'networking_level' => 3],
        ] as $package) {
            MembershipPackage::updateOrCreate(['slug' => $package['slug']], $package + ['is_active' => true, 'renewal_months' => 12]);
        }

        if (app()->environment('local', 'testing')) {
            $accounts = [
                ['name' => 'Premium Den Administrator', 'email' => 'admin@premiumden.test', 'role' => UserRole::SuperAdministrator, 'package' => null],
                ['name' => 'Risk Officer', 'email' => 'risk@premiumden.test', 'role' => UserRole::RiskTeam, 'package' => null],
                ['name' => 'Membership Administrator', 'email' => 'membership@premiumden.test', 'role' => UserRole::MembershipAdministrator, 'package' => null],
                ['name' => 'Content Administrator', 'email' => 'content@premiumden.test', 'role' => UserRole::ContentAdministrator, 'package' => null],
                ['name' => 'Den Contributor', 'email' => 'author@premiumden.test', 'role' => UserRole::Author, 'package' => null],
                ['name' => 'Diamond Member', 'email' => 'diamond@premiumden.test', 'role' => UserRole::Member, 'package' => 'diamond'],
                ['name' => 'Gold Member', 'email' => 'gold@premiumden.test', 'role' => UserRole::Member, 'package' => 'gold'],
                ['name' => 'Platinum Member', 'email' => 'platinum@premiumden.test', 'role' => UserRole::Member, 'package' => 'platinum'],
            ];

            foreach ($accounts as $account) {
                $user = User::updateOrCreate(['email' => $account['email']], [
                    'name' => $account['name'],
                    'password' => 'PremiumDen-Local-2026!',
                    'role' => $account['role'],
                    'membership_package_id' => $account['package'] ? MembershipPackage::where('slug', $account['package'])->value('id') : null,
                    'membership_active' => $account['role'] === UserRole::Member,
                ]);
                if ($account['role'] === UserRole::Member && $user->membership_package_id) {
                    Membership::firstOrCreate(['user_id' => $user->id], [
                        'membership_application_id' => null,
                        'membership_package_id' => $user->membership_package_id,
                        'number' => app(\App\Services\GenerateMembershipNumber::class)->handle(),
                        'status' => 'active',
                        'started_at' => today(),
                    ]);
                }
            }

            foreach ([
                'diamond' => ['company' => 'Example Enterprise', 'job_title' => 'Business Leader', 'industry' => 'Professional Services', 'business_category' => 'Consulting', 'location' => 'Nairobi', 'biography' => 'Development sample profile. Replace with verified member information.', 'interests' => ['strategy', 'leadership']],
                'gold' => ['company' => 'Sample Ventures', 'job_title' => 'Managing Director', 'industry' => 'Technology', 'business_category' => 'Digital Services', 'location' => 'Mombasa', 'biography' => 'Development sample profile. Replace with verified member information.', 'interests' => ['innovation', 'trade']],
                'platinum' => ['company' => 'Placeholder Group', 'job_title' => 'Founder', 'industry' => 'Manufacturing', 'business_category' => 'Consumer Goods', 'location' => 'Kisumu', 'biography' => 'Development sample profile. Replace with verified member information.', 'interests' => ['growth', 'partnerships']],
            ] as $tier => $profile) {
                $userId = User::where('email', $tier.'@premiumden.test')->value('id');
                MemberProfile::updateOrCreate(['user_id' => $userId], $profile + ['is_listed' => true]);
            }

            $authorId = User::where('email', 'author@premiumden.test')->value('id');
            $sampleArticles = [
                [
                    'title' => 'AI Has Changed the Meeting. Leadership Hasn’t.',
                    'slug' => 'ai-has-changed-the-meeting-leadership-hasnt',
                    'excerpt' => 'As intelligent tools take on more routine work, a leader’s edge comes from clearer judgment, better questions and the confidence to keep people at the centre.',
                    'body' => "Artificial intelligence can help a team move from a blank page to a useful first draft, find patterns in a large set of information, or prepare for a customer conversation. The value is practical: less time spent assembling the basics, and more time to decide what matters.

That shift makes leadership more visible. Teams still need someone to set direction, explain trade-offs and take responsibility for a decision. A polished answer is not the same as a sound one, especially when context, trust and consequences matter.

The strongest way to introduce a new tool is to pair it with a clear human purpose. Start with a real bottleneck, agree what good work looks like, and give people room to challenge the output. Technology can accelerate the work; thoughtful leadership gives it meaning.",
                    'category' => 'ideas-practice',
                    'days_ago' => 3,
                ],
                [
                    'title' => 'The Quiet Power of a Trusted Introduction',
                    'slug' => 'the-quiet-power-of-a-trusted-introduction',
                    'excerpt' => 'A useful introduction is more than an exchange of contact details. It creates context, sets expectations and gives two people a better place to begin.',
                    'body' => "The best introductions are generous with context. A short note about what each person is working on, why the connection could be useful and whether both people are open to meeting turns a cold contact into a considered conversation.

Trust grows through small acts of follow-through. Ask before making the introduction, keep the message concise and let the people involved decide what happens next. This respects their time while making the purpose of the connection clear.

For business leaders, a thoughtful network is built by being useful before there is an immediate return. Over time, those well-made connections help ideas travel, surface new perspectives and make collaboration feel natural.",
                    'category' => 'leadership',
                    'days_ago' => 2,
                ],
                [
                    'title' => 'Designing a More Resilient African Business',
                    'slug' => 'designing-a-more-resilient-african-business',
                    'excerpt' => 'Resilience is built into everyday choices: who a business depends on, how quickly it can adapt and how well it understands the needs around it.',
                    'body' => "Resilience is often discussed as a response to disruption, but it begins much earlier. A business becomes more adaptable when leaders understand their dependencies, keep communication close to customers and make room to review assumptions before conditions force a change.

Across African markets, those choices need to reflect local realities. Infrastructure, access to finance, regulation and customer expectations vary widely. Listening to the people closest to each market is a better starting point than applying a single playbook everywhere.

Practical resilience does not require a perfect forecast. It asks leaders to notice early signals, build relationships they can rely on and make decisions in manageable steps. The result is a business better prepared to learn and move with purpose.",
                    'category' => 'enterprise',
                    'days_ago' => 1,
                ],
            ];

            foreach ($sampleArticles as $sampleArticle) {
                Article::updateOrCreate(['slug' => $sampleArticle['slug']], [
                    'title' => $sampleArticle['title'],
                    'excerpt' => $sampleArticle['excerpt'],
                    'body' => $sampleArticle['body'],
                    'author_id' => $authorId,
                    'category_id' => ArticleCategory::where('slug', $sampleArticle['category'])->value('id'),
                    'status' => ArticleStatus::Published,
                    'published_at' => now()->subDays($sampleArticle['days_ago']),
                    'seo_title' => $sampleArticle['title'],
                    'seo_description' => $sampleArticle['excerpt'],
                ]);
            }
        }
    }
}
