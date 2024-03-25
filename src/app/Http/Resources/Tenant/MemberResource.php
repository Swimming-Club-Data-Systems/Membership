<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->MemberID,
            'first_name' => $this->MForename,
            'last_name' => $this->MSurname,
            'date_of_birth' => $this->DateOfBirth,
        ];
    }
}
