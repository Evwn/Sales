<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsAppService
{
    protected $client;
    protected $fromNumber;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.account_sid'),
            config('services.twilio.auth_token')
        );
        $this->fromNumber = 'whatsapp:' . config('services.twilio.whatsapp_number');
    }

    public function sendMessage($to, $message)
    {
        try {
            $response = $this->client->messages->create(
                'whatsapp:' . $to,
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );

            return $response;
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendDocument($to, $documentUrl, $caption = '')
    {
        try {
            $response = $this->client->messages->create(
                'whatsapp:' . $to,
                [
                    'from' => $this->fromNumber,
                    'mediaUrl' => [$documentUrl],
                    'body' => $caption
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendPDF($to, $pdfPath, $message = null)
    {
        try {
            // Get the full path of the PDF
            $fullPath = Storage::path($pdfPath);
            
            // Upload the PDF to Twilio's media service
            $mediaUrl = $this->uploadMedia($fullPath);

            // Send the message with PDF
            $response = $this->client->messages->create(
                "whatsapp:{$to}",
                [
                    'from' => "whatsapp:{$this->fromNumber}",
                    'body' => $message ?? 'Here is your requested document',
                    'mediaUrl' => [$mediaUrl]
                ]
            );
            return $response;
        } catch (\Exception $e) {
            Log::error('WhatsApp PDF sending failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function uploadMedia($filePath)
    {
        // Upload the file to a publicly accessible URL
        // You can use your own storage solution or a service like AWS S3
        // For this example, we'll assume the file is already publicly accessible
        return url(Storage::url($filePath));
    }
} 