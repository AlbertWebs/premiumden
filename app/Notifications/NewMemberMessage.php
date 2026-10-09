<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewMemberMessage extends Notification
{
    use Queueable;

    public function __construct(public Message $message, public Conversation $conversation) {}

    public function via(object $notifiable): array
    {
        return $notifiable->email_message_notifications ? ['database', 'mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $senderName = $this->message->sender->name;

        return (new MailMessage)
            ->subject('A private message from '.$senderName.' · Premium Business Den')
            ->view([
                'html' => 'emails.member-message-received',
                'text' => 'emails.member-message-received-text',
            ], [
                'recipientName' => $notifiable->name,
                'senderName' => $senderName,
                'sentAt' => $this->message->created_at?->format('j F Y · H:i'),
                'conversationUrl' => route('member.messages.show', $this->conversation),
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'new_member_message',
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'conversation_id' => $this->conversation->id,
            'message_id' => $this->message->id,
            'preview' => mb_substr($this->message->body, 0, 100),
        ];
    }
}
