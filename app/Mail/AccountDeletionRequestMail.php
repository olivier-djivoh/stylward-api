<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmez la suppression de votre compte',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.account.deletion-request',
            with: [
                'firstName' => $this->user->first_name,
                // ✅ Lien vers le FRONTEND (pas l'API brute)
                'link'      => rtrim(config('app.frontend_url'), '/') . '/account/deletion/confirm/' . $this->token,
                'dueAt'     => now()->addDays(30)->isoFormat('D MMMM YYYY'),
            ],
        );
    }
}
