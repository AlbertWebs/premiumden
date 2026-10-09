<?php

namespace Tests\Feature;

use App\Models\MemberProfile;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberSettingsAndPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_manage_optional_email_preferences(): void
    {
        $this->seed(DatabaseSeeder::class);
        $member = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $this->actingAs($member)->get(route('member.settings.edit'))->assertOk()->assertSee('optional email alerts');
        $this->put(route('member.settings.update'), [])->assertRedirect();
        $this->assertFalse($member->fresh()->email_message_notifications);
        $this->assertFalse($member->fresh()->email_society_updates);
    }

    public function test_member_can_upload_a_valid_private_profile_photo(): void
    {
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
        $member = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7X8AAAAASUVORK5CYII=');
        $this->actingAs($member)->put(route('member.profile.update'), [
            'company' => 'Example Co', 'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi',
            'interests' => 'Trade, strategy', 'biography' => 'A short professional introduction.', 'is_listed' => 1,
            'photo' => UploadedFile::fake()->createWithContent('profile.png', $png),
        ])->assertRedirect();
        $path = MemberProfile::where('user_id', $member->id)->value('photo_path');
        $this->assertNotNull($path);
        Storage::disk('local')->assertExists($path);
    }
}
