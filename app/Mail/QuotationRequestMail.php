<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Quotation;
use App\Models\Supplier;

class QuotationRequestMail extends Mailable
{
    use Queueable, SerializesModels;
    public $quotation;
    public $supplier;

    /**
     * Create a new message instance.
     */
    public function __construct(Quotation $quotation, Supplier $supplier)
    {   \Log::info('Trying to send 1');
        $this->quotation = $quotation;
        $this->supplier = $supplier;
    }
    
    public function build()
    {   \Log::info('Trying to send 2');
        $url = route('quotation.response.form', [
            'qs' => $this->quotation->suppliers()->where('supplier_id',$this->supplier->id)->first()->pivot->id,
        ]);
         \Log::info($url);

        return $this->subject("Quotation Request {$this->quotation->reference}")
            ->view('emails.quotation_request')
            ->with(['url'=>$url]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Quotation Request Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
