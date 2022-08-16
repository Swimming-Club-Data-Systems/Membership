<?php

namespace App\Models\Central;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Tenant extends BaseTenant
{
    use HasDomains;

    /**
     * Return a copy of the legacy tenant object
     */
    public function getLegacyTenant() {
      return \Tenant::fromId($this->id);
    }

    /**
     * Get the tenant id via expected attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['ID'],
        );
    }
}
