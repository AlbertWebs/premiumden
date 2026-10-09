<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipPackage;
use App\Models\User;
use App\Models\WelcomePackage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomePackageAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_membership_admin_can_track_welcome_package_delivery(): void
    {
        $this->seed(DatabaseSeeder::class);
        $member = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $membership = Membership::create([
            'user_id' => $member->id, 'membership_application_id' => $this->applicationId(),
            'membership_package_id' => MembershipPackage::where('slug', 'gold')->value('id'),
            'number' => 'PBD-26-TEST1234', 'status' => 'active', 'started_at' => today(), 'expires_at' => today()->addYear(),
        ]);
        $welcome = WelcomePackage::create(['membership_id' => $membership->id, 'status' => 'pending']);

        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.welcome-packages.index'))->assertOk()->assertSee($member->name);
        $this->actingAs($admin)
            ->patch(route('admin.welcome-packages.update', $welcome), ['status' => 'dispatched', 'tracking_reference' => 'TRK-00312', 'notes' => 'Collected by courier.'])
            ->assertRedirect();

        $this->assertSame('dispatched', $welcome->fresh()->status);
        $this->assertNotNull($welcome->fresh()->dispatched_at);
        $this->assertSame('TRK-00312', $welcome->fresh()->tracking_reference);
    }

    private function applicationId(): int
    {
        return \App\Models\MembershipApplication::create([
            'reference' => 'PBD-2610-KIT123', 'membership_package_id' => MembershipPackage::where('slug', 'gold')->value('id'),
            'status' => \App\Enums\ApplicationStatus::MembershipActivated, 'full_name' => 'Gold Member', 'email' => 'gold@premiumden.test', 'phone' => '+254700000001',
            'company' => 'Sample Ventures', 'job_title' => 'Director', 'industry' => 'Technology', 'location' => 'Nairobi', 'consent_at' => now(), 'submitted_at' => now(),
        ])->id;
    }
}
