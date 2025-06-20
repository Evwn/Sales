<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Sales Management System! 🎉')
            ->greeting('Welcome to Your Business Success Journey!')
            ->line('Dear ' . $notifiable->name . ',')
            ->line('We are thrilled to welcome you to the Sales Management System! Your account has been successfully verified, and you\'re now ready to transform your business operations.')
            ->line('Here\'s what you can do with our powerful platform:')
            ->line('🚀 **Multi-Business Management**')
            ->line('• Create and manage multiple businesses under one account')
            ->line('• Set up different branches with unique settings')
            ->line('• Monitor performance across all locations')
            ->line('')
            ->line('📊 **Advanced Sales Management**')
            ->line('• Track sales in real-time')
            ->line('• Generate detailed sales reports')
            ->line('• Manage inventory efficiently')
            ->line('')
            ->line('👥 **Team Management**')
            ->line('• Add and manage sales staff')
            ->line('• Set role-based permissions')
            ->line('• Monitor team performance')
            ->line('')
            ->line('📈 **Business Analytics**')
            ->line('• View comprehensive dashboards')
            ->line('• Generate custom reports')
            ->line('• Make data-driven decisions')
            ->line('')
            ->line('We\'re committed to helping you grow your business. Our support team is always ready to assist you.')
            ->action('Go to Dashboard', url('/dashboard'))
            ->line('Thank you for choosing Sales Management System. We\'re excited to be part of your business journey!')
            ->salutation('Best regards, The Sales Management Team');
    }
} 