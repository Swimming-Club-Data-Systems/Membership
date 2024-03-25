<?php

namespace App\Http\Resources\Central;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ID,
            'uuid' => $this->UniqueID,
            'name' => $this->Name,
            'code' => $this->Code,
            'website' => $this->Website,
            'verified' => $this->Verified,
            'domain' => $this->Domain,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
