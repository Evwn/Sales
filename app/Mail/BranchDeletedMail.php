<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BranchDeletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $branchName;
    public $businessName;
    public $admin;

    public function __construct($branchName, $businessName, $admin)
    {
        $this->branchName = $branchName;
        $this->businessName = $businessName;
        $this->admin = $admin;
    }

    public function build()
    {
        return $this->subject('Branch Deleted')
            ->view('emails.branch_deleted')
            ->with([
                'branchName' => $this->branchName,
                'businessName' => $this->businessName,
                'admin' => $this->admin,
            ]);
    }
} 