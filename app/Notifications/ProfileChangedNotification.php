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
        $mail = (new MailMessage)
            ->subject('Profile Update Notification')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We wanted to let you know that your profile information was recently updated on ' . config('app.name') . '.')
            ->line('Below is a summary of the changes made:');

        // Improved summary table of changes
        $summary = "<ul style='padding-left:16px;'>";
        foreach ($this->changes as $field => $change) {
            $summary .= "<li><strong>" . ucfirst(str_replace('_', ' ', $field)) . ":</strong> ";
            $summary .= "<span style='color:#6366f1;'>(old) " . e($change['old']) . "</span> ";
            $summary .= "<span style='color:#6b7280;'>→</span> ";
            $summary .= "<span style='color:#059669;'>(new) " . e($change['new']) . "</span>";
            $summary .= "</li>";
        }
        $summary .= "</ul>";
        $mail->line($summary);

        if (isset($this->changes['email'])) {
            $mail->line('')
                ->line('**Important:** Because your email address was changed, you will need to verify your new email the next time you log in.')
                ->action('Verify Email', url('/email/verify'));
        }

        $mail->line('If you did not make this change, please contact our support team immediately.');
        $mail->salutation('Thank you for using ' . config('app.name') . '!');

        return $mail;
    }
} 