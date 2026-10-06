<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre compte sera supprimé dans 30 jours',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.account.deletion-confirmed',
            with: [
                'firstName' => $this->user->first_name,
                'dueAt'     => $this->user->deletionDueAt()?->isoFormat('D MMMM YYYY'),
            ],
        );
    }
}
