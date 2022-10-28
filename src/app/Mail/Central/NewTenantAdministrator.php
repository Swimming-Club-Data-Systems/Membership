<?php

namespace App\Mail\Central;

use App\Models\Central\Tenant;
use App\Models\Central\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Business\Helpers\Mailable;
use Illuminate\Queue\SerializesModels;

class NewTenantAdministrator extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The tenant instance
     *
     * @var Tenant
     */
    public Tenant $tenant;

    /**
     * The user instance
     *
     * @var User
     */
    public User $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Tenant $tenant)
    {
        $this->user = $user;
        $this->tenant = $tenant;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->setDefaultFromAndReply()->subject('You\'ve been made an administrator of ' . $this->tenant->Name)->markdown('emails.central.new_tenant_administrator');
    }
}
