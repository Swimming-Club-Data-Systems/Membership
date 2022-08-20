<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant\User;

class UserLoginTwoFactorCode extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance
     * 
     * @var \App\Models\Tenant\User
     */
    public $user;

    /**
     * The login auth code
     * 
     * @var $code
     */
    public $code;

    /**
     * Create a new message instance.
     * 
     * @param \App\Models\Tenant\User $user the logging in user
     *
     * @return void
     */
    public function __construct(User $user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.users.login_two_factor_code');
    }
}
