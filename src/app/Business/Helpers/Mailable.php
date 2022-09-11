<?php

namespace App\Business\Helpers;

use App\Models\Central\Tenant;

/**
 * Override defaults
 */
class Mailable extends \Illuminate\Mail\Mailable
{
    public function setDefaultFromAndReply()
    {
        $fromName = config('mail.from.name');
        $fromMail = config('mail.from.address');

        if (tenant()) {
            /** @var Tenant $tenant */
            $tenant = tenant();

            $fromName = $tenant->getOption('CLUB_NAME');
            $replyName = $tenant->getOption('CLUB_NAME');
            $replyMail = $tenant->getOption('CLUB_EMAIL');

            $this->replyTo($replyMail, $replyName);
        }

        $this->from($fromMail, $fromName);

        return $this;
    }
}
