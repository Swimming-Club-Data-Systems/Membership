<?php

namespace App\Mail;

use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyNotifyAdditionalEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance
     *
     * @var User
     */
    public $user;

    /**
     * The signed url to visit and confirm
     *
     * @var string $url
     */
    public string $url;

    /**
     * The additional recipient's email address
     *
     * @var string $email
     */
    public string $email;

    /**
     * The additional recipient's name
     *
     * @var string $name
     */
    public string $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $url, string $email, string $name)
    {
        $this->user = $user;
        $this->url = $url;
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.users.verify_notify_aditional_email');
    }
}