<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BusinessDeletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $businessName;
    public $deletedBranches;
    public $admin;

    public function __construct($businessName, $deletedBranches, $admin)
    {
        $this->businessName = $businessName;
        $this->deletedBranches = $deletedBranches;
        $this->admin = $admin;
    }

    public function build()
    {
        return $this->subject('Business and Branches Deleted')
            ->view('emails.business_deleted')
            ->with([
                'businessName' => $this->businessName,
                'deletedBranches' => $this->deletedBranches,
                'admin' => $this->admin,
            ]);
    }
} 