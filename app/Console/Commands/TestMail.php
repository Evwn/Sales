<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email?}';

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
        $email = $this->argument('email') ?? 'test@example.com';
        
        $this->info('Testing mail configuration...');
        $this->info('Mail Driver: ' . config('mail.default'));
        $this->info('Mail Host: ' . config('mail.mailers.smtp.host'));
        $this->info('Mail Port: ' . config('mail.mailers.smtp.port'));
        $this->info('Mail Username: ' . config('mail.mailers.smtp.username'));
        $this->info('Mail Encryption: ' . config('mail.mailers.smtp.encryption'));
        
        try {
            Mail::raw('This is a test email from your Laravel application.', function($message) use ($email) {
                $message->to($email)
                        ->subject('Mail Test - ' . config('app.name'));
            });
            
            $this->info('✅ Email sent successfully!');
            $this->info('Check your inbox at: ' . $email);
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email:');
            $this->error($e->getMessage());
            
            // Log the error for debugging
            Log::error('Mail test failed: ' . $e->getMessage());
            
            $this->info('');
            $this->info('🔧 Troubleshooting tips:');
            $this->info('1. Check if your Gmail app password is correct');
            $this->info('2. Ensure 2-factor authentication is enabled on your Gmail account');
            $this->info('3. Verify the app password was generated for "Mail"');
            $this->info('4. Check if your firewall/antivirus is blocking the connection');
            $this->info('5. Try using port 465 with SSL instead of 587 with TLS');
        }
        
        return 0;
    }
}
