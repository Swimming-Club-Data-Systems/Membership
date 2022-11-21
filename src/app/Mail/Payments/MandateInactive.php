<?php

namespace App\Mail\Payments;

use App\Business\Helpers\Mailable;
use App\Models\Tenant\Mandate;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class MandateInactive extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public User           $user,
        public Mandate        $mandate,
        public ?PaymentMethod $paymentMethod = null,
        public ?PaymentMethod $newDefaultPaymentMethod = null,
    )
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->newDefaultPaymentMethod ?
            'Your default Direct Debit has changed' : 'Your Direct Debit has been cancelled';

        return $this->setDefaultFromAndReply()
            ->subject($subject)
            ->markdown('emails.payments.mandate-inactive');
    }
}
