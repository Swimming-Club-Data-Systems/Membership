<?php

namespace App\Mail;

use App\Models\Tenant\SquadMove;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SquadMoveCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public SquadMove $squadMove
    ) {
        //
    }

    private function getSubject(): string
    {
        if ($this->squadMove->oldSquad && $this->squadMove->newSquad) {
            return $this->squadMove->member->name.' is moving to '.$this->squadMove->newSquad->SquadName;
        } elseif ($this->squadMove->oldSquad) {
            return $this->squadMove->member->name.' is leaving '.$this->squadMove->oldSquad->SquadName;
        } elseif ($this->squadMove->newSquad) {
            return $this->squadMove->member->name.' is joining '.$this->squadMove->newSquad->SquadName;
        }

        return 'Squad move created for '.$this->squadMove->member->name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@myswimmingclub.uk', tenant()->getOption('CLUB_NAME')),
            replyTo: [
                new Address(tenant()->getOption('CLUB_EMAIL'), tenant()->getOption('CLUB_NAME')),
            ],
            subject: $this->getSubject(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.members.squad_move_created',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
