<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\MembershipApplication;
use App\Models\MembershipPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicantApplicationReceived;
use Tests\TestCase;

class MembershipApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_and_application_form_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Where business');
        $this->get('/membership')->assertOk()->assertSee('Diamond')->assertSee('Gold')->assertSee('Platinum');
        $this->get('/become-a-member')->assertOk()->assertSee('Member references');
    }

    public function test_valid_application_is_submitted_with_two_references_and_private_timeline(): void
    {
        Mail::fake();
        $package = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $referenceOne = $this->member('ref-one@example.test', 'First Ref');
        $referenceTwo = $this->member('ref-two@example.test', 'Second Ref');

        $response = $this->post('/become-a-member', [
            'membership_package_id' => $package->id,
            'full_name' => 'Alex Applicant', 'email' => 'alex@example.test', 'phone' => '+254700000001',
            'company' => 'Example Ventures', 'job_title' => 'Managing Director', 'industry' => 'Finance', 'location' => 'Nairobi',
            'references' => [
                ['full_name' => $referenceOne->name, 'email' => $referenceOne->email],
                ['full_name' => $referenceTwo->name, 'email' => $referenceTwo->email],
            ],
            'consent' => '1',
        ]);

        $response->assertSessionHasNoErrors();

        $application = MembershipApplication::firstOrFail();
        $response->assertRedirect(route('application.received', $application->reference));
        $this->assertSame(ApplicationStatus::Submitted, $application->status);
        $this->assertSame(2, $application->references()->count());
        $this->assertDatabaseHas('application_references', ['membership_application_id' => $application->id, 'member_user_id' => $referenceOne->id]);
        Mail::assertSent(ApplicantApplicationReceived::class, fn (ApplicantApplicationReceived $mail) => $mail->hasTo($application->email) && str_contains($mail->statusUrl, 'signature='));
        $this->assertDatabaseHas('application_status_histories', ['membership_application_id' => $application->id, 'to_status' => 'submitted']);
        $this->get(route('application.received', $application->reference))->assertOk()->assertSee($application->reference);
        $this->get(route('application.status', $application->reference))->assertOk()->assertDontSee('Alex Applicant')->assertSee('Submitted');
    }

    public function test_application_rejects_missing_references_or_consent(): void
    {
        $this->from('/become-a-member')->post('/become-a-member', [])->assertSessionHasErrors(['membership_package_id', 'full_name', 'references', 'consent']);
        $this->assertDatabaseCount('membership_applications', 0);
    }

    public function test_risk_review_status_transitions_are_logged_and_notes_are_private(): void
    {
        $application = $this->makeApplication();
        $riskOfficer = User::create(['name' => 'Risk Officer', 'email' => 'risk@example.test', 'password' => 'password', 'role' => UserRole::RiskTeam, 'membership_active' => false]);

        $this->actingAs($riskOfficer)->get(route('admin.applications.show', $application))->assertOk()->assertSee('Applicant profile');
        $this->patch(route('admin.applications.transition', $application), ['status' => 'under_review', 'note' => 'Reference checks started.'])->assertRedirect();
        $this->assertSame(ApplicationStatus::UnderReview, $application->fresh()->status);
        $this->assertDatabaseHas('application_internal_notes', ['membership_application_id' => $application->id, 'body' => 'Reference checks started.']);
        $this->get(route('application.status', $application->reference))->assertOk()->assertDontSee('Reference checks started.');
    }

    public function test_membership_administrator_cannot_make_risk_decisions(): void
    {
        $application = $this->makeApplication();
        $membershipAdmin = User::create(['name' => 'Membership Admin', 'email' => 'membership@example.test', 'password' => 'password', 'role' => UserRole::MembershipAdministrator, 'membership_active' => false]);

        $this->actingAs($membershipAdmin)
            ->patch(route('admin.applications.transition', $application), ['status' => 'under_review'])
            ->assertForbidden();
        $this->assertSame(ApplicationStatus::Submitted, $application->fresh()->status);
    }

    public function test_member_cannot_reach_application_administration(): void
    {
        $member = User::create(['name' => 'Member', 'email' => 'member@example.test', 'password' => 'password', 'role' => UserRole::Member, 'membership_active' => true]);
        $this->actingAs($member)->get('/admin/applications')->assertForbidden();
    }

    public function test_development_admin_can_sign_in_to_the_admin_dashboard(): void
    {
        $this->post('/login', ['email' => 'admin@premiumden.test', 'password' => 'PremiumDen-Local-2026!'])
            ->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Applications to review');
    }

    public function test_inactive_member_login_is_rejected(): void
    {
        User::create(['name' => 'Pending Member', 'email' => 'pending@example.test', 'password' => 'password', 'role' => UserRole::Member, 'membership_active' => false]);

        $this->from('/login')->post('/login', ['email' => 'pending@example.test', 'password' => 'password'])
            ->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    private function member(string $email, string $name): User
    {
        return User::create(['name' => $name, 'email' => $email, 'password' => 'password', 'role' => UserRole::Member, 'membership_package_id' => MembershipPackage::where('slug', 'platinum')->value('id'), 'membership_active' => true]);
    }

    private function makeApplication(): MembershipApplication
    {
        return MembershipApplication::create([
            'reference' => 'PBD-2610-TEST123', 'membership_package_id' => MembershipPackage::where('slug', 'gold')->value('id'),
            'status' => ApplicationStatus::Submitted, 'full_name' => 'Test Applicant', 'email' => 'test@example.test', 'phone' => '0700000000',
            'company' => 'Test Co', 'job_title' => 'Director', 'industry' => 'Technology', 'location' => 'Nairobi', 'consent_at' => now(), 'submitted_at' => now(),
        ]);
    }
}
