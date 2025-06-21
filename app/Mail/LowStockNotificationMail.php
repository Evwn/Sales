<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Business;
use App\Models\Product;

class LowStockNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $business;
    public $lowStockProducts;
    public $businessOwner;

    /**
     * Create a new message instance.
     */
    public function __construct(Business $business, $lowStockProducts, $businessOwner)
    {
        $this->business = $business;
        $this->lowStockProducts = $lowStockProducts;
        $this->businessOwner = $businessOwner;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 Low Stock Alert - ' . $this->business->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.low-stock-notification',
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