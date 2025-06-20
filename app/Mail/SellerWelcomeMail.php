<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $branch;
    public $business;

    public function __construct($user, $branch, $business)
    {
        $this->user = $user;
        $this->branch = $branch;
        $this->business = $business;
    }

    public function build()
    {
        return $this->subject('Welcome to ' . $this->business->name)
            ->view('emails.seller_welcome');
    }
} 