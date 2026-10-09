<?php

namespace Tests\Feature;

use App\Models\MembershipPackage;
use App\Models\MemberProfile;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MembershipDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_directory_query_only_returns_packages_within_the_viewers_access(): void
    {
        $diamond = User::where('email', 'diamond@premiumden.test')->firstOrFail();
        $gold = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $platinum = User::where('email', 'platinum@premiumden.test')->firstOrFail();

        $this->actingAs($diamond)->get(route('member.directory.index'))->assertOk()
            ->assertSee('Diamond Member')->assertDontSee('Gold Member')->assertDontSee('Platinum Member');
        $this->actingAs($gold)->get(route('member.directory.index'))->assertOk()
            ->assertSee('Diamond Member')->assertSee('Gold Member')->assertDontSee('Platinum Member');
        $this->actingAs($platinum)->get(route('member.directory.index'))->assertOk()
            ->assertSee('Diamond Member')->assertSee('Gold Member')->assertSee('Platinum Member');
    }

    public function test_forbidden_member_profile_url_is_denied_server_side(): void
    {
        $diamond = User::where('email', 'diamond@premiumden.test')->firstOrFail();
        $gold = User::where('email', 'gold@premiumden.test')->firstOrFail();

        $this->actingAs($diamond)->get(route('member.directory.show', $gold))->assertForbidden();
        $this->actingAs($gold)->get(route('member.directory.show', $gold))->assertOk()->assertSee('Gold Member');
    }

    public function test_profile_editing_cannot_change_membership_or_role_fields(): void
    {
        $diamond = User::where('email', 'diamond@premiumden.test')->firstOrFail();
        $goldPackageId = MembershipPackage::where('slug', 'gold')->value('id');

        $this->actingAs($diamond)->put(route('member.profile.update'), [
            'company' => 'A Member Company', 'job_title' => 'Managing Director', 'industry' => 'Consulting',
            'location' => 'Nairobi', 'interests' => 'trade, strategy', 'biography' => 'An updated introduction.',
            'is_listed' => '1', 'membership_package_id' => $goldPackageId, 'role' => 'super_administrator', 'membership_active' => '0',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('diamond', $diamond->fresh()->membershipPackage->slug);
        $this->assertSame('member', $diamond->fresh()->role->value);
        $this->assertTrue($diamond->fresh()->membership_active);
        $this->assertSame(['trade', 'strategy'], $diamond->fresh()->profile->interests);
    }

    public function test_members_can_opt_out_of_directory_listing(): void
    {
        $gold = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $profile = $gold->profile;
        $profile->update(['is_listed' => false]);
        $viewer = User::where('email', 'platinum@premiumden.test')->firstOrFail();

        $this->actingAs($viewer)->get(route('member.directory.index'))->assertOk()->assertDontSee('Gold Member');
        $this->get(route('member.directory.show', $gold))->assertNotFound();
    }

    public function test_profile_photo_uses_membership_authorization_and_private_cache_headers(): void
    {
        Storage::fake('local');
        $diamond = User::where('email', 'diamond@premiumden.test')->firstOrFail();
        $gold = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $path = 'member-photos/gold.png';
        Storage::disk('local')->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7X8AAAAASUVORK5CYII='));
        MemberProfile::updateOrCreate(['user_id' => $gold->id], ['photo_path' => $path, 'is_listed' => true]);

        $this->actingAs($diamond)->get(route('member.directory.photo', $gold))->assertNotFound();
        $platinum = User::where('email', 'platinum@premiumden.test')->firstOrFail();
        $response = $this->actingAs($platinum)->get(route('member.directory.photo', $gold))->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }
}
