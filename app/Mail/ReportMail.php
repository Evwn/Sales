<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfContent, $reportType;

    public function __construct($pdfContent, $reportType)
    {
        $this->pdfContent = $pdfContent;
        $this->reportType = $reportType;
    }

    public function build()
    {
        return $this->subject('Your ' . ucfirst($this->reportType) . ' Report')
            ->view('emails.report-notification')
            ->attachData($this->pdfContent, $this->reportType . '-report.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
} 