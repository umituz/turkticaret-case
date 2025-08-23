<?php

namespace App\Mail\User;

use App\Models\Auth\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WelcomeMail extends Mailable
{
    public function __construct(
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Welcome to TurkTicaret - Your E-Commerce Journey Begins!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
