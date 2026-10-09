<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Membership;
use App\Models\MembershipCard;
use App\Models\MembershipApplication;
use App\Models\MembershipPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_membership_admin_can_search_members_payments_and_suspend_members(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        $member = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.members.index', ['search' => 'gold@premiumden.test']))->assertOk()->assertSee('Gold Member');
        $this->get(route('admin.payments.index'))->assertOk()->assertSee('Payments');
        $application = MembershipApplication::create([
            'reference' => 'PBD-2601-ADMTEST', 'membership_package_id' => MembershipPackage::where('slug', 'gold')->value('id'),
            'status' => ApplicationStatus::MembershipActivated, 'full_name' => $member->name, 'email' => $member->email,
            'phone' => '0712345678', 'company' => 'Example Co', 'job_title' => 'Director', 'industry' => 'Trade',
            'location' => 'Nairobi', 'consent_at' => now(), 'submitted_at' => now(),
        ]);
        $membership = Membership::create(['user_id' => $member->id, 'membership_application_id' => $application->id,
            'membership_package_id' => $application->membership_package_id, 'number' => 'PBD-26-TEST0001', 'status' => 'active', 'started_at' => today()]);
        $this->patch(route('admin.members.status', $member), ['status' => 'suspended'])->assertRedirect();
        $this->assertFalse($member->fresh()->membership_active);
        $this->assertSame('suspended', $membership->fresh()->status);
        $this->assertDatabaseHas('audit_events', ['action' => 'membership.suspended', 'resource_id' => (string) $membership->id]);

        $card = MembershipCard::create(['membership_id' => $membership->id, 'identifier' => (string) \Illuminate\Support\Str::uuid(), 'status' => 'active']);
        $this->patch(route('admin.cards.update', $card), ['status' => 'lost', 'physical_card_reference' => 'CHIP-UID-001'])->assertRedirect();
        $this->assertSame('lost', $card->fresh()->status);
        $this->assertSame('CHIP-UID-001', $card->fresh()->physical_card_reference);
        $this->assertDatabaseHas('audit_events', ['action' => 'member_card.hardware_reference_updated', 'resource_id' => (string) $card->id]);
    }
}
