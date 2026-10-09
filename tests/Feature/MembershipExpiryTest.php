<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\MembershipCard;
use App\Models\MembershipPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduler_command_expires_access_and_card_for_ended_terms(): void
    {
        $this->seed(DatabaseSeeder::class);
        $member = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $packageId = MembershipPackage::where('slug', 'gold')->value('id');
        $application = MembershipApplication::create([
            'reference' => 'PBD-2610-EXPIRE22', 'membership_package_id' => $packageId, 'status' => 'membership_activated',
            'full_name' => $member->name, 'email' => $member->email, 'phone' => '0700000000', 'company' => 'Example',
            'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi', 'consent_at' => now(), 'submitted_at' => now(),
        ]);
        $membership = Membership::create([
            'user_id' => $member->id, 'membership_application_id' => $application->id, 'membership_package_id' => $packageId,
            'number' => 'PBD-26-EXPIRE22', 'status' => 'active', 'started_at' => today()->subYear(), 'expires_at' => today()->subDay(),
        ]);
        MembershipCard::create(['membership_id' => $membership->id, 'identifier' => (string) str()->uuid(), 'status' => 'active']);
        $this->artisan('memberships:expire')->expectsOutput('Expired 1 memberships.')->assertExitCode(0);
        $this->assertFalse($member->fresh()->membership_active);
        $this->assertSame('expired', $membership->fresh()->status);
        $this->assertSame('expired', $membership->card()->first()->status);
        $this->assertDatabaseHas('audit_events', ['action' => 'membership.expired', 'resource_id' => (string) $membership->id]);
    }
}
