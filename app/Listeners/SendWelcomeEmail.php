<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        // Example implementation - in a real app, you would send an actual email
        // Mail::to($event->user->email)->send(new \App\Mail\WelcomeEmail($event->user));

        // For now, just log that we would send an email
        \Log::info('Welcome email would be sent to: '.$event->user->email);
    }
}
