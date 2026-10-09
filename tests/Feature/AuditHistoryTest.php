<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\MembershipPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_changes_are_audited_and_admin_history_is_restricted(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        $package = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.packages.update', $package), [
            'description' => $package->description, 'price' => 15000, 'benefits' => "Business access\nEvents",
            'renewal_months' => 12, 'networking_level' => $package->networking_level,
            'display_order' => $package->display_order, 'is_active' => 1,
        ])->assertRedirect();
        $this->assertDatabaseHas('audit_events', ['action' => 'membership_package.updated', 'resource_id' => (string) $package->id, 'actor_id' => $admin->id]);
        $this->get(route('admin.audit.index'))->assertOk()->assertSee('Audit history');

        $member = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $this->actingAs($member)->get(route('admin.audit.index'))->assertForbidden();
        $this->assertInstanceOf(AuditEvent::class, AuditEvent::firstOrFail());
    }
}
