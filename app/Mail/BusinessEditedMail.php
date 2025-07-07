<?php

namespace App\Mail;

use App\Models\Business;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BusinessEditedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $business;
    public $updatedItems;
    public $admin;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Business $business, array $updatedItems, User $admin)
    {
        $this->business = $business;
        $this->updatedItems = $updatedItems;
        $this->admin = $admin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->subject('Business Details Updated')
            ->view('emails.business_edited')
            ->with([
                'updatedItems' => $this->updatedItems,
                'logo_path' => in_array('Business Logo', $this->updatedItems) ? $this->business->logo_path : null,
                'tax_document_path' => in_array('Tax Document', $this->updatedItems) ? $this->business->tax_document_path : null,
                'registration_document_path' => in_array('Business Registration Document', $this->updatedItems) ? $this->business->registration_document_path : null,
                'terms_and_conditions_path' => in_array('Terms and Conditions Document', $this->updatedItems) ? $this->business->terms_and_conditions : null,
            ]);

        // Attach logo if updated
        if (in_array('Business Logo', $this->updatedItems) && $this->business->logo_path) {
            $logoFullPath = storage_path('app/public/' . $this->business->logo_path);
            if (file_exists($logoFullPath)) {
                $email->attach($logoFullPath, [
                    'as' => 'logo.' . pathinfo($logoFullPath, PATHINFO_EXTENSION),
                    'mime' => mime_content_type($logoFullPath),
                ]);
            }
        }
        // Attach tax document if updated
        if (in_array('Tax Document', $this->updatedItems) && $this->business->tax_document_path) {
            $taxDocFullPath = storage_path('app/public/' . $this->business->tax_document_path);
            if (file_exists($taxDocFullPath)) {
                $email->attach($taxDocFullPath, [
                    'as' => 'tax_document.' . pathinfo($taxDocFullPath, PATHINFO_EXTENSION),
                    'mime' => mime_content_type($taxDocFullPath),
                ]);
            }
        }
        // Attach registration document if updated
        if (in_array('Business Registration Document', $this->updatedItems) && $this->business->registration_document_path) {
            $regDocFullPath = storage_path('app/public/' . $this->business->registration_document_path);
            if (file_exists($regDocFullPath)) {
                $email->attach($regDocFullPath, [
                    'as' => 'registration_document.' . pathinfo($regDocFullPath, PATHINFO_EXTENSION),
                    'mime' => mime_content_type($regDocFullPath),
                ]);
            }
        }
        // Attach terms and conditions if updated
        if (in_array('Terms and Conditions Document', $this->updatedItems) && $this->business->terms_and_conditions) {
            $termsFullPath = storage_path('app/public/' . $this->business->terms_and_conditions);
            if (file_exists($termsFullPath)) {
                $email->attach($termsFullPath, [
                    'as' => 'terms_and_conditions.' . pathinfo($termsFullPath, PATHINFO_EXTENSION),
                    'mime' => mime_content_type($termsFullPath),
                ]);
            }
        }
        return $email;
    }
} 