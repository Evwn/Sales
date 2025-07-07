<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Business;

class BusinessCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $business;

    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    public function build()
    {
        return $this->subject('Your Business Has Been Created')
            ->view('emails.business_created')
            ->with([
                'business' => $this->business,
                'owner' => $this->business->owner,
            ]);
    }
} 