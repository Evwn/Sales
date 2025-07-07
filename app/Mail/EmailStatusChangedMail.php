<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $status; // 'activated' or 'deactivated'

    public function __construct($user, $status)
    {
        $this->user = $user;
        $this->status = $status;
    }

    public function build()
    {
        $subject = $this->status === 'activated'
            ? 'Your Account Has Been Activated'
            : 'Your Account Has Been Deactivated';
        return $this->subject($subject)
            ->view('emails.email_status_changed');
    }
} 