<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ProfileChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $changes;

    public function __construct(array $changes)
    {
        $this->changes = $changes;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Profile Update Notification')
            ->view('emails.profile-changed', [
                'user' => $notifiable,
                'changes' => $this->changes,
            ]);
    }
} 