<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $daysLeft,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->daysLeft === 1
            ? 'Dernier jour avant la suppression de votre compte'
            : "Il vous reste {$this->daysLeft} jours avant la suppression";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.account.deletion-reminder',
            with: [
                'firstName' => $this->user->first_name,
                'daysLeft'  => $this->daysLeft,
                'dueAt'     => $this->user->deletionDueAt()?->isoFormat('D MMMM YYYY'),
            ],
        );
    }
}
