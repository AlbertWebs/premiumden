<?php

namespace Tests\Feature;

use App\Models\SocietyActivity;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocietyActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_admin_can_publish_public_and_member_only_activities(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'content@premiumden.test')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.activities.store'), [
            'title' => 'A public partnership update', 'category' => 'Partnership', 'excerpt' => 'A public summary.',
            'body' => 'Public activity details.', 'visibility' => 'public', 'status' => 'published',
        ])->assertRedirect(route('admin.activities.index'));
        $publicActivity = SocietyActivity::firstOrFail();
        $this->get(route('activities.show', $publicActivity->slug))->assertOk()->assertSee('Public activity details.');

        $this->actingAs($admin)->post(route('admin.activities.store'), [
            'title' => 'Members only update', 'category' => 'Meeting', 'excerpt' => 'Member summary.',
            'body' => 'Private member details.', 'visibility' => 'members', 'status' => 'published',
        ])->assertRedirect();
        $privateActivity = SocietyActivity::where('visibility', 'members')->firstOrFail();
        auth()->logout();
        $this->get(route('activities.show', $privateActivity->slug))->assertNotFound();
        $this->actingAs(User::where('email', 'gold@premiumden.test')->firstOrFail())->get(route('activities.show', $privateActivity->slug))->assertOk()->assertSee('Private member details.');
        $this->actingAs(User::where('email', 'gold@premiumden.test')->firstOrFail())->get(route('member.activities.index'))->assertOk()->assertSee('Members only update');
    }
}
