<?php

namespace App\Http\Controllers\Member;

use App\Http\Requests\StoreMemberMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMemberMessage;
use App\Services\StartMemberConversation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConversationController
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $conversations = Conversation::query()
            ->whereHas('participants', fn (Builder $query) => $query->whereKey($user->id))
            ->whereHas('participants', fn (Builder $query) => $query->currentMember()->where('users.id', '!=', $user->id)->whereHas('membershipPackage', fn (Builder $package) => $package->where('is_active', true)))
            ->with(['participants.profile', 'participants.membershipPackage', 'participants.membership', 'latestMessage.sender'])
            ->withCount(['messages as unread_messages_count' => fn (Builder $query) => $query->where('sender_id', '!=', $user->id)->whereNull('read_at')])
            ->orderByDesc('updated_at')->paginate(20);

        return view('member.messages.index', compact('conversations'));
    }

    public function start(Request $request, User $member, StartMemberConversation $start): RedirectResponse
    {
        $conversation = $start->handle($request->user(), $member);

        return redirect()->route('member.messages.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        abort_unless($request->user()->can('view', $conversation), 404);
        $conversation->load(['participants.profile', 'participants.membershipPackage', 'participants.membership']);
        $other = $conversation->participants->firstWhere('id', '!=', $request->user()->id);
        abort_unless($other && $other->hasActiveMembership(), 404);

        $conversation->messages()->where('sender_id', '!=', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        $request->user()->unreadNotifications()->whereJsonContains('data->conversation_id', $conversation->id)->update(['read_at' => now()]);
        $messages = $conversation->messages()->with('sender')->orderByDesc('created_at')->paginate(40);
        $messages->setCollection($messages->getCollection()->reverse()->values());

        return view('member.messages.show', compact('conversation', 'other', 'messages'));
    }

    public function store(StoreMemberMessageRequest $request, Conversation $conversation): RedirectResponse
    {
        abort_unless($request->user()->can('send', $conversation), 404);

        $message = DB::transaction(function () use ($request, $conversation): Message {
            $lockedConversation = Conversation::query()->lockForUpdate()->findOrFail($conversation->id);
            abort_unless($request->user()->can('send', $lockedConversation), 404);

            return $lockedConversation->messages()->create(['sender_id' => $request->user()->id, 'body' => trim($request->validated('body'))]);
        });

        $recipient = $conversation->participants()->where('users.id', '!=', $request->user()->id)->first();
        if ($recipient) {
            $recipient->notify(new NewMemberMessage($message->load('sender'), $conversation));
        }

        return redirect()->route('member.messages.show', $conversation)->withFragment('message-'.$message->id);
    }
}
