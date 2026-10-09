<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Mail\ApplicantDocumentsRequested;
use App\Models\ApplicantDocument;
use App\Models\MembershipApplication;
use App\Models\MembershipPackage;
use App\Models\User;
use App\Services\TransitionMembershipApplication;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ApplicantDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
    }

    public function test_expiring_signed_link_accepts_documents_into_private_storage(): void
    {
        Mail::fake();
        $application = $this->application();
        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        app(TransitionMembershipApplication::class)->handle($application, $admin, ApplicationStatus::DocumentsRequired);

        Mail::assertSent(ApplicantDocumentsRequested::class, function (ApplicantDocumentsRequested $mail) use ($application): bool {
            $this->assertStringContainsString('signature=', $mail->uploadUrl);
            $this->assertTrue($mail->application->is($application));
            return $mail->hasTo($application->email);
        });
        $mail = Mail::sent(ApplicantDocumentsRequested::class)->first();
        $this->get($mail->uploadUrl)->assertOk()->assertSee('Passport-size photograph')->assertSee('National ID or passport');

        $tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7X8AAAAASUVORK5CYII=');
        $this->post($mail->uploadUrl, [
            'photo' => UploadedFile::fake()->createWithContent('portrait.png', $tinyPng),
            'identity' => UploadedFile::fake()->createWithContent('national-id.pdf', "%PDF-1.4\n% document"),
            'identity_type' => 'national_id',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseCount('applicant_documents', 2);
        $this->assertSame(ApplicationStatus::DocumentsRequired, $application->fresh()->status);
        foreach (ApplicantDocument::all() as $document) {
            $this->assertTrue(Storage::disk('local')->exists($document->storage_path));
        }
    }

    public function test_document_access_token_and_signed_url_are_both_required(): void
    {
        Mail::fake();
        $application = $this->application();
        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        app(TransitionMembershipApplication::class)->handle($application, $admin, ApplicationStatus::DocumentsRequired);
        $mail = Mail::sent(ApplicantDocumentsRequested::class)->first();
        $tampered = str_replace('signature=', 'signature=x', $mail->uploadUrl);

        $this->get($tampered)->assertForbidden();
        $wrongTokenUrl = URL::temporarySignedRoute('application.documents.create', now()->addHour(), ['reference' => $application->reference, 'token' => 'wrong']);
        $this->get($wrongTokenUrl)->assertForbidden();
    }

    public function test_only_membership_administrator_can_open_private_document_route(): void
    {
        Mail::fake();
        $application = $this->application();
        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        app(TransitionMembershipApplication::class)->handle($application, $admin, ApplicationStatus::DocumentsRequired);
        $application->documents()->create(['document_type' => 'photo', 'original_name' => 'portrait.png', 'storage_path' => 'applications/private/portrait.png', 'mime_type' => 'image/png', 'size_bytes' => 10, 'status' => 'pending']);
        Storage::disk('local')->put('applications/private/portrait.png', 'file');
        $document = ApplicantDocument::firstOrFail();

        $this->actingAs(User::where('email', 'gold@premiumden.test')->firstOrFail())->get(route('admin.documents.show', $document))->assertForbidden();
        $response = $this->actingAs($admin)->get(route('admin.documents.show', $document))->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_documents_must_be_approved_before_application_can_proceed(): void
    {
        Mail::fake();
        $application = $this->application();
        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        $transition = app(TransitionMembershipApplication::class);
        $transition->handle($application, $admin, ApplicationStatus::DocumentsRequired);
        $application->documents()->createMany([
            ['document_type' => 'photo', 'original_name' => 'portrait.png', 'storage_path' => 'x/photo.png', 'mime_type' => 'image/png', 'size_bytes' => 12, 'status' => 'pending'],
            ['document_type' => 'identity', 'identity_type' => 'passport', 'original_name' => 'passport.pdf', 'storage_path' => 'x/passport.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 12, 'status' => 'pending'],
        ]);

        $this->assertNotContains(ApplicationStatus::DocumentsReceived, $transition->availableFor($application->fresh(), $admin));
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $transition->handle($application->fresh(), $admin, ApplicationStatus::DocumentsReceived);
    }

    public function test_membership_administrator_can_resend_a_new_signed_link(): void
    {
        Mail::fake();
        $application = $this->application();
        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        $transition = app(TransitionMembershipApplication::class);
        $transition->handle($application, $admin, ApplicationStatus::DocumentsRequired);
        $firstLink = Mail::sent(ApplicantDocumentsRequested::class)->first()->uploadUrl;

        $transition->resendDocumentRequest($application, $admin);
        $mail = Mail::sent(ApplicantDocumentsRequested::class)->last();
        $this->assertNotSame($firstLink, $mail->uploadUrl);
        $this->get($firstLink)->assertForbidden();
        $this->get($mail->uploadUrl)->assertOk();
        $this->assertDatabaseHas('application_internal_notes', ['membership_application_id' => $application->id, 'body' => 'A new time-limited document upload link was issued to the applicant.']);
    }

    private function application(): MembershipApplication
    {
        return MembershipApplication::create([
            'reference' => 'PBD-2610-DOC123', 'membership_package_id' => MembershipPackage::where('slug', 'gold')->value('id'),
            'status' => ApplicationStatus::Approved, 'full_name' => 'Document Applicant', 'email' => 'docs@example.test', 'phone' => '+254700000001',
            'company' => 'Sample Company', 'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi', 'consent_at' => now(), 'submitted_at' => now(),
        ]);
    }
}
