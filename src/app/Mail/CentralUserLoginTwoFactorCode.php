<?php

namespace App\Mail;

use App\Business\Helpers\Mailable;
use App\Models\Central\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CentralUserLoginTwoFactorCode extends Mailable
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
     */
    public $code;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Central\User  $user the logging in user
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
        return $this->setDefaultFromAndReply()->subject('Your Two-Factor Login Code')->markdown('emails.central_users.login_two_factor_code');
    }
}
