<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\MembershipPackage;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipAccessTest extends TestCase
{
    use RefreshDatabase;

    private array $packages;

    private array $members;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->packages = MembershipPackage::query()->get()->keyBy('slug')->all();
        $this->members = [];

        foreach (['diamond', 'gold', 'platinum'] as $tier) {
            $this->members[$tier] = User::create([
                'name' => ucfirst($tier).' Member',
                'email' => $tier.'@example.test',
                'password' => 'password',
                'role' => UserRole::Member,
                'membership_package_id' => $this->packages[$tier]->id,
                'membership_active' => true,
            ]);
        }
    }

    public function test_diamond_can_only_view_and_message_diamond_members(): void
    {
        $this->assertTrue($this->members['diamond']->canViewMember($this->members['diamond']));
        $this->assertFalse($this->members['diamond']->canViewMember($this->members['gold']));
        $this->assertFalse($this->members['diamond']->canViewMember($this->members['platinum']));
        $this->assertFalse($this->members['diamond']->canMessageMember($this->members['diamond']));
        $this->assertFalse($this->members['diamond']->canMessageMember($this->members['gold']));
        $this->assertFalse($this->members['diamond']->canMessageMember($this->members['platinum']));
    }

    public function test_gold_can_view_and_message_gold_and_diamond_but_not_platinum(): void
    {
        $gold = $this->members['gold'];
        $this->assertTrue($gold->canViewMember($this->members['diamond']));
        $this->assertTrue($gold->canViewMember($gold));
        $this->assertFalse($gold->canViewMember($this->members['platinum']));
        $this->assertTrue($gold->canMessageMember($this->members['diamond']));
        $this->assertFalse($gold->canMessageMember($gold));
        $this->assertFalse($gold->canMessageMember($this->members['platinum']));
    }

    public function test_platinum_can_view_and_message_all_members(): void
    {
        $platinum = $this->members['platinum'];
        foreach ($this->members as $member) {
            $this->assertTrue($platinum->canViewMember($member));
            if ($member->isNot($platinum)) {
                $this->assertTrue($platinum->canMessageMember($member));
            }
        }
    }

    public function test_inactive_or_unassigned_accounts_cannot_access_member_profiles(): void
    {
        $inactive = User::create(['name' => 'Inactive', 'email' => 'inactive@example.test', 'password' => 'password', 'role' => UserRole::Member, 'membership_package_id' => $this->packages['platinum']->id, 'membership_active' => false]);
        $this->assertFalse($inactive->canViewMember($this->members['diamond']));
        $this->assertFalse($this->members['platinum']->canViewMember($inactive));
    }

    public function test_expired_membership_term_blocks_member_portal_immediately(): void
    {
        $member = $this->members['gold'];
        $application = MembershipApplication::create([
            'reference' => 'PBD-2610-EXPIRED1', 'membership_package_id' => $member->membership_package_id,
            'status' => 'membership_activated', 'full_name' => $member->name, 'email' => $member->email,
            'phone' => '0700000000', 'company' => 'Example', 'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi',
            'consent_at' => now(), 'submitted_at' => now(),
        ]);
        Membership::create([
            'user_id' => $member->id, 'membership_application_id' => $application->id,
            'membership_package_id' => $member->membership_package_id, 'number' => 'PBD-26-EXPIRE01',
            'status' => 'active', 'started_at' => today()->subYear(), 'expires_at' => today()->subDay(),
        ]);
        $this->assertFalse($member->fresh()->hasActiveMembership());
        $this->actingAs($member->fresh())->get(route('member.dashboard'))->assertForbidden();
    }

    public function test_active_member_can_view_membership_details_and_card_issuance_state(): void
    {
        $member = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $this->actingAs($member)->get(route('member.membership.show'))->assertOk()->assertSee('Gold')->assertSee('On activation');
        $this->actingAs($member)->get(route('member.card.show'))->assertOk()->assertSee('Your digital card will appear after membership activation.');
    }
}
