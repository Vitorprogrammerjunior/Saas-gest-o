<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkspaceInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Convite para o workspace: {$this->invitation->workspace->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.workspace-invitation',
            with: [
                'inviterName' => $this->invitation->inviter->name,
                'workspaceName' => $this->invitation->workspace->name,
                'acceptUrl' => config('app.frontend_url') . '/invitations/' . $this->invitation->token,
                'expiresAt' => $this->invitation->expires_at->format('d/m/Y'),
            ],
        );
    }
}
