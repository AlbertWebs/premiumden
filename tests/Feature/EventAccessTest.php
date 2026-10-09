<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\MembershipPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_content_admin_can_publish_tier_limited_event(): void
    {
        Storage::fake('public');
        $gold = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $editor = User::where('email', 'content@premiumden.test')->firstOrFail();
        $this->actingAs($editor)->get(route('admin.events.create'))->assertOk()->assertSee('Who can see this event?');
        $this->actingAs($editor)
            ->post(route('admin.events.store'), [
                'title' => 'Gold members roundtable', 'description' => 'A private discussion for the Gold circle.', 'location' => 'Nairobi',
                'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'), 'visibility' => 'packages', 'package_ids' => [(string) $gold->id],
                'status' => 'published', 'featured_image' => UploadedFile::fake()->createWithContent('gathering.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7X8AAAAASUVORK5CYII=')),
            ])->assertRedirect();

        $event = Event::firstOrFail();
        $this->assertSame('gold-members-roundtable', $event->slug);
        $this->assertSame([$gold->id], $event->package_ids);
        Storage::disk('public')->assertExists($event->featured_image_path);
        $this->get(route('events.show', $event->slug))->assertNotFound();
        $goldMember = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $this->actingAs($goldMember)->get(route('member.events.index'))->assertOk()->assertSee($event->title);
        $this->actingAs($goldMember)->get(route('member.events.show', $event->slug))->assertOk()->assertSee($event->title)->assertSee($event->featured_image_path);
        $this->actingAs(User::where('email', 'diamond@premiumden.test')->firstOrFail())->get(route('member.events.show', $event->slug))->assertNotFound();
    }

    public function test_public_event_appears_on_public_calendar_and_notices_are_published_only(): void
    {
        $event = Event::create([
            'title' => 'Public business briefing', 'slug' => 'public-business-briefing', 'description' => 'A public overview.', 'location' => 'Nairobi',
            'starts_at' => now()->addDays(10), 'visibility' => 'public', 'status' => 'published', 'published_at' => now(),
            'created_by' => User::where('email', 'content@premiumden.test')->value('id'),
        ]);
        Event::create([
            'title' => 'Draft gathering', 'slug' => 'draft-gathering', 'description' => 'Not released.', 'location' => 'Nairobi',
            'starts_at' => now()->addDays(12), 'visibility' => 'public', 'status' => 'draft',
            'created_by' => User::where('email', 'content@premiumden.test')->value('id'),
        ]);

        $this->get(route('events.index'))->assertOk()->assertSee($event->title)->assertDontSee('Draft gathering');
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee($event->description);
    }
}
