<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use App\Mail\SellerWelcomeMail;
use App\Mail\OwnerWelcomeMail;
use Illuminate\Support\Facades\Mail;

class SendWelcomeAfterEmailVerified
{
    public function handle(Verified $event)
    {
        $user = $event->user;

        if ($user->hasRole('seller')) {
            $branch = $user->branch; // Make sure relationship is loaded
            $business = $branch ? $branch->business : null;
            Mail::to($user->email)->send(new SellerWelcomeMail($user, $branch, $business));
        } elseif ($user->hasRole('owner')) {
            Mail::to($user->email)->send(new OwnerWelcomeMail($user));
        }
    }
} 