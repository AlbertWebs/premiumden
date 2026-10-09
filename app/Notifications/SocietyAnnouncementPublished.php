<?php

namespace App\Notifications;

use App\Models\SocietyAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SocietyAnnouncementPublished extends Notification
{
    use Queueable;

    public function __construct(public SocietyAnnouncement $announcement) {}
    public function via(object $notifiable): array { return $notifiable->email_society_updates ? ['database', 'mail'] : ['database']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject($this->announcement->title)->greeting('Hello '.$notifiable->name)
            ->line($this->announcement->body)->action('View society notices', route('member.notifications.index'));
    }

    public function toDatabase(object $notifiable): array
    {
        return ['type' => 'society_announcement', 'announcement_id' => $this->announcement->id, 'title' => $this->announcement->title, 'message' => $this->announcement->body];
    }
}
