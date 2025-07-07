<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProfileChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $summary;

    public function __construct($user, $summary)
    {
        $this->user = $user;
        $this->summary = $summary;
    }

    public function build()
    {
        return $this->subject('Your Profile Has Been Updated')
            ->view('emails.profile-changed');
    }
} 