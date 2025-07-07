<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $status; // 'verified' or 'unverified'

    public function __construct($user, $status)
    {
        $this->user = $user;
        $this->status = $status;
    }

    public function build()
    {
        $subject = $this->status === 'verified'
            ? 'Your Email Has Been Verified'
            : 'Your Email Has Been Unverified';
        return $this->subject($subject)
            ->view('emails.email_verification_status_changed');
    }
} 