<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerAccountUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $seller;
    public $branch;
    public $business;
    public $changes;

    public function __construct($seller, $branch, $business, $changes)
    {
        $this->seller = $seller;
        $this->branch = $branch;
        $this->business = $business;
        $this->changes = $changes;
    }

    public function build()
    {
        return $this->subject('Your Seller Account Has Been Updated')
            ->view('emails.seller_account_updated');
    }
} 