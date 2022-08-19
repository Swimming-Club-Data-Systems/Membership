<?php

namespace App\Models\Tenant\Passport;

use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
  // Include support for tenancy

  /**
   * Determine if the client should skip the authorization prompt.
   *
   * @return bool
   */
  public function skipsAuthorization()
  {
    return $this->firstParty;
  }
}
