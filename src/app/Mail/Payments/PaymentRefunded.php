<?php

namespace App\Mail\Payments;

use App\Business\Helpers\Money;
use App\Models\Tenant\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRefunded extends Mailable
{
    use Queueable, SerializesModels;

    public string $amountRefunded;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Payment $payment,
        public int $amount,
    ) {
        $this->amountRefunded = Money::formatCurrency($this->amount, $this->payment->currency);
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
            subject: 'Payment Refunded',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payments.payment-refunded',
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
