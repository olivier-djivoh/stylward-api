<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionFinalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $firstName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre compte a été supprimé');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.account.deletion-final',
            with: ['firstName' => $this->firstName],
        );
    }
}
