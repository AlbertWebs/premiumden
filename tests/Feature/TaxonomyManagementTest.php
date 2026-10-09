<?php

namespace Tests\Feature;

use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxonomyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_content_admin_can_manage_article_categories_and_tags(): void
    {
        $editor = User::where('email', 'content@premiumden.test')->firstOrFail();
        $this->actingAs($editor)->get(route('admin.taxonomy.index'))->assertOk()->assertSee('Article categories');
        $this->actingAs($editor)->post(route('admin.taxonomy.categories.store'), [
            'name' => 'Trade Journal', 'slug' => '', 'description' => 'Trade and enterprise stories.', 'display_order' => 5, 'is_active' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('article_categories', ['slug' => 'trade-journal', 'is_active' => 1]);

        $this->actingAs($editor)->post(route('admin.taxonomy.tags.store'), ['name' => 'Cross-border trade'])->assertRedirect();
        $this->assertDatabaseHas('article_tags', ['slug' => 'cross-border-trade', 'name' => 'Cross-border trade']);
        $this->assertSame(ArticleCategory::count(), 5);
        $this->assertSame(ArticleTag::count(), 1);
    }

    public function test_member_cannot_manage_editorial_taxonomy(): void
    {
        $this->actingAs(User::where('email', 'gold@premiumden.test')->firstOrFail())->get(route('admin.taxonomy.index'))->assertForbidden();
    }
}
