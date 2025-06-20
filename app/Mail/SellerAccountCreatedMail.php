<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $seller;
    public $branch;
    public $business;

    public function __construct($seller, $branch, $business)
    {
        $this->seller = $seller;
        $this->branch = $branch;
        $this->business = $business;
    }

    public function build()
    {
        return $this->subject('Welcome to ' . $this->business->name . ' - Seller Account Created')
            ->view('emails.seller_account_created');
    }
} 