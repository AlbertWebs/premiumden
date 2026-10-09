<?php

namespace Tests\Feature;

use App\Models\MembershipPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipPackageAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_membership_admin_can_configure_real_package_price_and_benefits(): void
    {
        $package = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.packages.edit', $package))->assertOk()->assertSee('Membership term price');
        $this->actingAs($admin)
            ->put(route('admin.packages.update', $package), [
                'description' => 'Updated Gold membership description.', 'price' => '12000.00',
                'benefits' => "Directory access\nMember introductions", 'renewal_months' => 12,
                'networking_level' => 2, 'display_order' => 2, 'is_active' => '1',
            ])->assertRedirect();

        $this->assertSame('12000.00', $package->fresh()->price);
        $this->assertSame(['Directory access', 'Member introductions'], $package->fresh()->benefits);
        $this->get(route('admin.packages.index'))->assertOk()->assertSee('KES 12,000.00');
    }

    public function test_risk_team_cannot_change_commercial_package_settings(): void
    {
        $package = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $this->actingAs(User::where('email', 'risk@premiumden.test')->firstOrFail())
            ->get(route('admin.packages.index'))->assertForbidden();
        $this->assertNull($package->fresh()->price);
    }
}
