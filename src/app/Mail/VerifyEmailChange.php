<?php

namespace App\Mail;

use App\Business\Helpers\Mailable;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailChange extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance
     *
     * @var \App\Models\Tenant\User
     */
    public $user;

    /**
     * The signed url to visit and confirm
     */
    public $url;

    /**
     * The user's new email address
     */
    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $url, $email)
    {
        $this->user = $user;
        $this->url = $url;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->setDefaultFromAndReply()->markdown('emails.users.verify_email_change');
    }
}
