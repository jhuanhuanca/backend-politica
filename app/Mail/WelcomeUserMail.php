<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido a Viejocracia, '.$this->user->nick,
        );
    }

    public function content(): Content
    {
        $front = rtrim((string) config('app.frontend_url'), '/');

        return new Content(
            html: 'emails.welcome',
            text: 'emails.welcome-text',
            with: [
                'nick' => $this->user->nick,
                'department' => $this->user->department ?: 'tu departamento',
                'homeUrl' => $front ?: 'https://viejocracia.techrep.app',
                'debatesUrl' => $front.'/debates',
                'votesUrl' => $front.'/votaciones',
                'suggestUrl' => $front.'/sugerencias',
            ],
        );
    }
}
