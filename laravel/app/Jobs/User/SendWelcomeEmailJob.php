<?php

namespace App\Jobs\User;

use App\Mail\User\WelcomeMail;
use App\Models\User\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/**
 * Job for sending welcome emails to new users asynchronously.
 * 
 * This queued job handles sending welcome emails to users
 * after successful registration. It provides a good user
 * experience by welcoming them to the platform.
 *
 * @package App\Jobs\User
 */
class SendWelcomeEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param User $user The user to send welcome email to
     */
    public function __construct(public User $user,) {}

    /**
     * Execute the job to send welcome email.
     * 
     * Sends a welcome email to the user's email address
     * using the WelcomeMail mailable.
     *
     * @return void
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(new WelcomeMail($this->user));
    }
}
