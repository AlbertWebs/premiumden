<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\MembershipPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_diamond_cannot_start_a_conversation_with_gold_or_platinum(): void
    {
        $diamond = $this->member('diamond');

        $this->actingAs($diamond)->post(route('member.messages.start', $this->member('gold')))->assertForbidden();
        $this->actingAs($diamond)->post(route('member.messages.start', $this->member('platinum')))->assertForbidden();
        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_gold_can_start_conversations_with_gold_and_diamond(): void
    {
        $gold = $this->member('gold');
        $diamond = $this->member('diamond');
        $response = $this->actingAs($gold)->post(route('member.messages.start', $diamond));
        $conversation = Conversation::firstOrFail();

        $response->assertRedirect(route('member.messages.show', $conversation));
        $this->assertDatabaseCount('conversation_participants', 2);
        $this->assertDatabaseHas('conversation_participants', ['conversation_id' => $conversation->id, 'user_id' => $gold->id]);
        $this->assertDatabaseHas('conversation_participants', ['conversation_id' => $conversation->id, 'user_id' => $diamond->id]);
    }

    public function test_platinum_can_start_conversation_and_members_can_send_private_messages(): void
    {
        $platinum = $this->member('platinum');
        $gold = $this->member('gold');
        $this->actingAs($platinum)->post(route('member.messages.start', $gold))->assertRedirect();
        $conversation = Conversation::firstOrFail();

        $this->actingAs($platinum)->post(route('member.messages.store', $conversation), ['body' => '<script>alert("hi")</script>'])
            ->assertRedirect(route('member.messages.show', $conversation).'#message-1');
        $this->assertDatabaseHas('messages', ['conversation_id' => $conversation->id, 'sender_id' => $platinum->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $gold->id, 'type' => 'App\\Notifications\\NewMemberMessage']);

        $this->actingAs($gold)->get(route('member.messages.show', $conversation))->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;hi&quot;)&lt;/script&gt;', false);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $gold->id, 'read_at' => null]);
        $this->actingAs($gold)->post(route('member.messages.store', $conversation), ['body' => 'Thank you for reaching out.'])->assertRedirect();
        $this->assertDatabaseHas('messages', ['conversation_id' => $conversation->id, 'sender_id' => $gold->id, 'body' => 'Thank you for reaching out.']);
    }

    public function test_nonparticipant_and_inactive_member_cannot_access_an_existing_conversation(): void
    {
        $platinum = $this->member('platinum');
        $gold = $this->member('gold');
        $other = $this->member('diamond');
        $this->actingAs($platinum)->post(route('member.messages.start', $gold));
        $conversation = Conversation::firstOrFail();

        $this->actingAs($other)->get(route('member.messages.show', $conversation))->assertNotFound();
        $gold->update(['membership_active' => false]);
        $this->actingAs($platinum)->get(route('member.messages.show', $conversation))->assertNotFound();
        $this->actingAs($platinum)->post(route('member.messages.store', $conversation), ['body' => 'Check-in'])->assertNotFound();
    }

    public function test_repeated_start_requests_reuse_the_existing_conversation(): void
    {
        $gold = $this->member('gold');
        $diamond = $this->member('diamond');
        $this->actingAs($gold)->post(route('member.messages.start', $diamond));
        $this->actingAs($gold)->post(route('member.messages.start', $diamond));

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('conversation_participants', 2);
    }

    private function member(string $tier): User
    {
        return User::where('email', $tier.'@premiumden.test')->firstOrFail();
    }
}
