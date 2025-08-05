<?php

namespace App\Console\Commands;

use App\Models\BranchMpesaCredential;
use App\Models\PaymentCallbackUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateCallbackUrlsForExistingCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callbacks:create-for-existing-credentials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create callback URLs for all existing M-PESA credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to create callback URLs for existing M-PESA credentials...');

        $credentials = BranchMpesaCredential::where('is_active', true)->get();
        
        if ($credentials->isEmpty()) {
            $this->warn('No active M-PESA credentials found.');
            return 0;
        }

        $this->info("Found {$credentials->count()} active M-PESA credentials.");

        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($credentials as $credential) {
            try {
                // Check if callback URL already exists
                $existingCallback = PaymentCallbackUrl::where('branch_id', $credential->branch_id)
                    ->where('payment_type', 'mpesa')
                    ->where('provider', 'safaricom')
                    ->where('environment', $credential->environment)
                    ->first();

                if ($existingCallback) {
                    $this->line("Skipping: Callback URL already exists for branch {$credential->branch_id} ({$credential->environment})");
                    $skipped++;
                    continue;
                }

                // Create callback URL
                $callbackUrl = $credential->createCallbackUrl();
                
                $this->info("Created: Callback URL for branch {$credential->branch_id} ({$credential->environment}) - {$callbackUrl->callback_url}");
                $created++;

            } catch (\Exception $e) {
                $this->error("Error creating callback URL for branch {$credential->branch_id}: {$e->getMessage()}");
                $errors++;
                
                Log::error('Failed to create callback URL for existing credentials', [
                    'branch_id' => $credential->branch_id,
                    'environment' => $credential->environment,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Created: {$created} callback URLs");
        $this->info("- Skipped: {$skipped} (already existed)");
        $this->info("- Errors: {$errors}");

        if ($errors > 0) {
            $this->warn("There were {$errors} errors. Check the logs for details.");
            return 1;
        }

        $this->info('All callback URLs created successfully!');
        return 0;
    }
}
