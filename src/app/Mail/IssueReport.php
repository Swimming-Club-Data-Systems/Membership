<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IssueReport extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $url;
    protected $description;
    protected $userAgent;
    protected $userAgentBrands;
    protected $userAgentPlatform;
    protected $userAgentMobile;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $url
     * @param $description
     * @param $userAgent
     * @param $userAgentBrands
     * @param $userAgentPlatform
     * @param $userAgentMobile
     * @return void
     */
    public function __construct($user, $url, $description, $userAgent, $userAgentBrands, $userAgentPlatform, $userAgentMobile)
    {
        $this->user = $user;
        $this->url = $url;
        $this->description = $description;
        $this->userAgent = $userAgent;
        $this->userAgentBrands = $userAgentBrands;
        $this->userAgentPlatform = $userAgentPlatform;
        $this->userAgentMobile = $userAgentMobile;
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
