<?php

namespace App\Mail\Central;

use App\Business\Helpers\Mailable;
use App\Models\Central\Tenant;
use App\Models\Central\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class NewTenantAdministratorUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The tenant instance
     *
     * @var Tenant $tenant
     */
    public Tenant $tenant;

    /**
     * The user instance
     *
     * @var User
     */
    public User $user;

    /**
     * The link
     *
     * @var string
     */
    public string $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Tenant $tenant, string $link)
    {
        $this->user = $user;
        $this->tenant = $tenant;
        $this->link = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->setDefaultFromAndReply()->subject('You\'ve been invited to become an administrator of ' . $this->tenant->Name)->markdown('emails.central.new_tenant_administrator_user');
    }
}
