<?php

namespace App\Mail;

use App\Business\Helpers\Recipient;
use App\Models\Tenant\NotifyHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public NotifyHistory $email,
        public Recipient $recipient,
    )
    {
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@myswimmingclub.uk', $this->email->fromName()),
            replyTo: [
                new Address($this->email->replyToEmail(), $this->email->replyToName()),
            ],
            subject: $this->email->Subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notify.message',
            text: 'emails.notify.message-text',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return Attachment[]
     */
    public function attachments(): array
    {
        $map = function ($attachment) {
            return Attachment::fromStorage($attachment['s3_path'])
                ->as($attachment['name'])
                ->withMime($attachment['mime_type']);
        };

        return array_map($map, $this->email->attachments());
    }
}
