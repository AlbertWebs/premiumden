<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Mail\AuthorAccessInvitation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthorInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_admin_can_invite_author_and_author_sets_password_once(): void
    {
        $this->seed(DatabaseSeeder::class);
        Mail::fake();
        $contentAdmin = User::where('email', 'content@premiumden.test')->firstOrFail();
        $this->actingAs($contentAdmin)->post(route('admin.authors.store'), [
            'name' => 'New Writer', 'email' => 'writer@example.test',
        ])->assertRedirect()->assertSessionHas('status');

        $author = User::where('email', 'writer@example.test')->firstOrFail();
        $this->assertSame(UserRole::Author, $author->role);
        Mail::assertSent(AuthorAccessInvitation::class, fn ($mail) => $mail->hasTo($author->email));

        $invitation = Mail::sent(AuthorAccessInvitation::class)->first();
        $url = $invitation->setupUrl;
        $token = basename(parse_url($url, PHP_URL_PATH));
        $this->get($url)->assertOk()->assertSee('Set your password');
        $storeUrl = URL::temporarySignedRoute('author.first-access.store', now()->addHour(), ['email' => $author->email, 'token' => $token]);
        $this->post($storeUrl, ['password' => 'WriterPassword-2026!', 'password_confirmation' => 'WriterPassword-2026!'])->assertRedirect(route('login'));
        $this->assertNull($author->fresh()->first_access_token_hash);

        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => $author->email, 'password' => 'WriterPassword-2026!'])->assertRedirect(route('author.articles.index'));
        $this->get(route('author.articles.index'))->assertOk();
        $this->get($storeUrl)->assertNotFound();
    }

    public function test_non_content_roles_cannot_manage_authors(): void
    {
        $this->seed(DatabaseSeeder::class);
        $author = User::where('email', 'author@premiumden.test')->firstOrFail();
        $this->actingAs($author)->get(route('admin.authors.index'))->assertForbidden();
    }
}
