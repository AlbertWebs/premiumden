<?php

namespace Tests\Feature;

use App\Mail\ApplicationDraftSaved;
use App\Models\MembershipApplication;
use App\Models\MembershipPackage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicationDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_can_save_and_resume_draft_then_submit_it(): void
    {
        $this->seed(DatabaseSeeder::class);
        Mail::fake();
        $data = [
            'membership_package_id' => MembershipPackage::where('slug', 'gold')->value('id'),
            'full_name' => 'Draft Applicant', 'email' => 'draft@example.test', 'phone' => '0712345678',
            'company' => 'Draft Company', 'job_title' => 'Managing Director', 'industry' => 'Trade', 'location' => 'Nairobi',
            'references' => [
                ['full_name' => 'Ref One', 'email' => 'ref-one@example.test'],
                ['full_name' => 'Ref Two', 'email' => 'ref-two@example.test'],
            ],
        ];
        $this->post(route('application.store'), $data + ['save_draft' => 1])->assertRedirect();
        $draft = MembershipApplication::where('email', 'draft@example.test')->firstOrFail();
        $this->assertSame('draft', $draft->status->value);
        Mail::assertSent(ApplicationDraftSaved::class);
        $resumeUrl = Mail::sent(ApplicationDraftSaved::class)->first()->resumeUrl;
        $token = basename(parse_url($resumeUrl, PHP_URL_PATH));
        $this->get($resumeUrl)->assertOk()->assertSee('Draft Applicant');
        $this->post(route('application.store'), $data + ['draft_reference' => $draft->reference, 'draft_token' => $token, 'consent' => 1])
            ->assertRedirect(route('application.received', $draft->reference));
        $this->assertSame('submitted', $draft->fresh()->status->value);
        $this->assertNull($draft->fresh()->draft_token_hash);
        $this->assertDatabaseHas('application_status_histories', ['membership_application_id' => $draft->id, 'from_status' => 'draft', 'to_status' => 'submitted']);
    }

    public function test_expired_drafts_are_pruned_without_deleting_submitted_applications(): void
    {
        $this->seed(DatabaseSeeder::class);
        $packageId = MembershipPackage::where('slug', 'gold')->value('id');
        $draft = MembershipApplication::create([
            'reference' => 'PBD-2610-OLD1234', 'membership_package_id' => $packageId, 'status' => 'draft',
            'full_name' => 'Old Draft', 'email' => 'old-draft@example.test', 'phone' => '0700000000', 'company' => 'Example',
            'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi', 'draft_expires_at' => now()->subDay(), 'draft_token_hash' => hash('sha256', 'unused'),
        ]);
        $submitted = MembershipApplication::create([
            'reference' => 'PBD-2610-SUB1234', 'membership_package_id' => $packageId, 'status' => 'submitted',
            'full_name' => 'Submitted Applicant', 'email' => 'submitted@example.test', 'phone' => '0700000000', 'company' => 'Example',
            'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi', 'submitted_at' => now()->subDay(), 'draft_expires_at' => now()->subDay(),
        ]);
        $this->artisan('application-drafts:prune')->expectsOutput('Removed 1 expired application drafts.')->assertExitCode(0);
        $this->assertDatabaseMissing('membership_applications', ['id' => $draft->id]);
        $this->assertDatabaseHas('membership_applications', ['id' => $submitted->id]);
    }
}
