<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant\User;

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
     * 
     * @var $url
     */
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $url)
    {
        $this->user = $user;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.users.verify_email_change');
    }
}
