<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the mail configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Mail::raw('This is a test email from Laravel', function($message) {
                $message->to('erastuseven@gmail.com')
                        ->subject('Test Email');
            });
            
            $this->info('Test email sent successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());
        }
    }
}
