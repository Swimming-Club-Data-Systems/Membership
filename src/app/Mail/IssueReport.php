<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class IssueReport extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;

    public $userId;

    public $url;

    public $description;

    public $userAgent;

    public $userAgentBrands;

    public $userAgentPlatform;

    public $userAgentMobile;

    public $tenant;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $url, $description, $userAgent, $userAgentBrands, $userAgentPlatform, $userAgentMobile)
    {
        $this->user = $user;

        if (Arr::has($this->user, 'id')) {
            $this->userId = $this->user['id'];
        }

        if (tenant()) {
            $this->tenant = [
                'id' => tenant('id'),
                'name' => tenant('Name'),
            ];
        }

        $this->url = $url;
        $this->description = $description;
        $this->userAgent = $userAgent;
        $this->userAgentBrands = $userAgentBrands;
        $this->userAgentPlatform = $userAgentPlatform;
        $this->userAgentMobile = $userAgentMobile;

        $this->subject = 'User Issue Report';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.issue.report');
    }
}
