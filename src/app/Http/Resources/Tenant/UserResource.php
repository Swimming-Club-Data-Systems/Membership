<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->UserID,
            'first_name' => $this->Forename,
            'last_name' => $this->Surname,
            'email' => $this->EmailAddress,
            'phone' => $this->Mobile,
            'email_subscribed' => $this->EmailComms,
            'sms_subscribed' => $this->MobileComms,
        ];
    }
}
