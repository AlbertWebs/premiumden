<?php

namespace Tests\Feature;

use App\Models\MembershipPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTargetingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_content_admin_can_send_package_targeted_notice(): void
    {
        $gold = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $editor = User::where('email', 'content@premiumden.test')->firstOrFail();
        $this->actingAs($editor)->get(route('admin.announcements.create'))->assertOk()->assertSee('Compose a notice');
        $this->actingAs($editor)
            ->post(route('admin.announcements.store'), [
                'title' => 'Gold gathering notice', 'body' => 'An update for the Gold membership circle.',
                'target' => 'packages', 'package_ids' => [(string) $gold->id], 'action' => 'publish',
            ])->assertRedirect(route('admin.announcements.index'));

        $this->assertSame(1, User::where('email', 'gold@premiumden.test')->firstOrFail()->notifications()->count());
        $this->assertSame(0, User::where('email', 'diamond@premiumden.test')->firstOrFail()->notifications()->count());
        $this->assertSame(0, User::where('email', 'platinum@premiumden.test')->firstOrFail()->notifications()->count());

        $goldMember = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $this->actingAs($goldMember)->get(route('member.notifications.index'))->assertOk()->assertSee('Gold gathering notice')->assertSee('An update for the Gold membership circle.');
    }

    public function test_saving_draft_does_not_deliver_notice(): void
    {
        $this->actingAs(User::where('email', 'content@premiumden.test')->firstOrFail())
            ->post(route('admin.announcements.store'), ['title' => 'Unsent draft', 'body' => 'This is not published yet.', 'target' => 'all_members', 'action' => 'draft'])
            ->assertRedirect();
        $this->assertDatabaseHas('society_announcements', ['title' => 'Unsent draft', 'status' => 'draft']);
        $this->assertDatabaseCount('notifications', 0);
        $draft = \App\Models\SocietyAnnouncement::firstOrFail();
        $this->actingAs(User::where('email', 'content@premiumden.test')->firstOrFail())
            ->put(route('admin.announcements.update', $draft), ['title' => 'Edited notice', 'body' => 'Now ready to send.', 'target' => 'all_members', 'action' => 'publish'])
            ->assertRedirect(route('admin.announcements.index'));
        $this->assertDatabaseHas('society_announcements', ['id' => $draft->id, 'title' => 'Edited notice', 'status' => 'published']);
        $this->assertDatabaseCount('notifications', 3);
    }
}
