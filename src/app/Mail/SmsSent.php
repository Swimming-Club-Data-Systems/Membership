<?php

namespace App\Mail;

use App\Business\Helpers\Mailable;
use App\Models\Tenant\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class SmsSent extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public Sms    $sms,
        public string $totalFee,
        public int    $sentUsers,
        public int    $failedUsers
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
        if ($this->sentUsers > 0) {
            return $this->setDefaultFromAndReply()
                ->subject('We\'ve sent your SMS')
                ->markdown('emails.sms.sent');
        }

        return $this->setDefaultFromAndReply()
            ->subject('We could not send your SMS')
            ->markdown('emails.sms.sent');
    }
}
