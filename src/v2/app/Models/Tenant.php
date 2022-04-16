<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\Tenant as TenantInterface;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantInterface
{
  use HasDomains;

  public static function getCustomColumns(): array
  {
    return [
      'id',
      'name',
      'code',
      'website',
      'email',
      'verified',
      'uuid',
      'domain',
    ];
  }

  public function getIncrementing()
  {
    return true;
  }

  public function __toString()
  {
    return $this->name;
  }
}
