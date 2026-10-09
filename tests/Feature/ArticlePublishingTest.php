<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticlePublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_author_can_save_a_draft_and_submit_for_content_review(): void
    {
        $author = User::where('email', 'author@premiumden.test')->firstOrFail();
        $category = ArticleCategory::firstOrFail();

        $response = $this->actingAs($author)->post(route('author.articles.store'), [
            'title' => 'A thoughtful working title', 'excerpt' => 'Draft summary.', 'body' => 'Draft text.',
            'category_id' => $category->id, 'tags' => 'trade, strategy', 'seo_title' => 'Search title',
        ]);
        $article = Article::firstOrFail();
        $response->assertRedirect(route('author.articles.edit', $article));
        $this->assertSame(ArticleStatus::Draft, $article->status);
        $this->assertCount(2, $article->tags);
        $this->get(route('author.articles.preview', $article))->assertOk()->assertSee('Draft text.');
        $anotherAuthor = User::create(['name' => 'Second Writer', 'email' => 'second-writer@example.test', 'password' => 'password', 'role' => 'author']);
        $this->actingAs($anotherAuthor)->get(route('author.articles.preview', $article))->assertForbidden();

        $this->actingAs($author);
        $this->actingAs($author)->post(route('author.articles.submit', $article))->assertRedirect(route('author.articles.index'));
        $this->assertSame(ArticleStatus::PendingReview, $article->fresh()->status);
        $this->get(route('articles.show', $article->slug))->assertNotFound();
    }

    public function test_author_cannot_edit_another_authors_article(): void
    {
        $firstAuthor = User::where('email', 'author@premiumden.test')->firstOrFail();
        $secondAuthor = User::create(['name' => 'Second Author', 'email' => 'second-author@example.test', 'password' => 'password', 'role' => 'author']);
        $article = $this->makeArticle($firstAuthor);

        $this->actingAs($secondAuthor)->get(route('author.articles.edit', $article))->assertForbidden();
        $this->actingAs($secondAuthor)->put(route('author.articles.update', $article), ['title' => 'Stolen title', 'body' => 'Text'])->assertForbidden();
    }

    public function test_content_team_can_publish_and_public_output_escapes_article_body(): void
    {
        $author = User::where('email', 'author@premiumden.test')->firstOrFail();
        $reviewer = User::where('email', 'content@premiumden.test')->firstOrFail();
        $article = $this->makeArticle($author, ArticleStatus::PendingReview, '<script>alert("no")</script>');

        $this->actingAs($reviewer)->post(route('admin.articles.review', $article), ['action' => 'publish', 'note' => 'Reviewed and approved.'])->assertRedirect();
        $this->assertSame(ArticleStatus::Published, $article->fresh()->status);
        $this->assertNotNull($article->fresh()->published_at);
        $this->get(route('articles.show', $article->slug))->assertOk()->assertSee('&lt;script&gt;alert(&quot;no&quot;)&lt;/script&gt;', false);
        $this->assertDatabaseHas('article_review_histories', ['article_id' => $article->id, 'action' => 'publish', 'note' => 'Reviewed and approved.']);
    }

    public function test_author_and_member_cannot_open_content_administration(): void
    {
        $author = User::where('email', 'author@premiumden.test')->firstOrFail();
        $member = User::where('email', 'gold@premiumden.test')->firstOrFail();

        $this->actingAs($author)->get(route('admin.articles.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.articles.index'))->assertForbidden();
    }

    public function test_author_can_upload_and_replace_a_featured_image(): void
    {
        Storage::fake('public');
        $author = User::where('email', 'author@premiumden.test')->firstOrFail();
        $tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7X8AAAAASUVORK5CYII=');
        $first = UploadedFile::fake()->createWithContent('cover.png', $tinyPng);
        $this->actingAs($author)->post(route('author.articles.store'), [
            'title' => 'Article with an image', 'body' => 'A useful article body.', 'featured_image' => $first,
        ])->assertRedirect();
        $article = Article::firstOrFail();
        Storage::disk('public')->assertExists($article->featured_image_path);
        $oldPath = $article->featured_image_path;

        $this->actingAs($author)->put(route('author.articles.update', $article), [
            'title' => $article->title, 'body' => 'A useful article body.', 'featured_image' => UploadedFile::fake()->createWithContent('new-cover.png', $tinyPng),
        ])->assertRedirect();
        $article->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($article->featured_image_path);

        $article->update(['status' => ArticleStatus::Published, 'published_at' => now()->subMinute()]);
        $this->get(route('articles.show', $article->slug))->assertOk()->assertSee($article->featured_image_path);
    }

    private function makeArticle(User $author, ArticleStatus $status = ArticleStatus::Draft, string $body = 'Draft text.'): Article
    {
        return Article::create([
            'title' => 'A useful article title', 'slug' => 'useful-article-'.uniqid(), 'excerpt' => 'An excerpt.', 'body' => $body,
            'author_id' => $author->id, 'category_id' => ArticleCategory::value('id'), 'status' => $status,
            'published_at' => $status === ArticleStatus::Published ? now()->subMinute() : null,
        ]);
    }
}
