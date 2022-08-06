<?php

namespace SCDS\GoCardless;

class Client
{

  /**
   * Returns the current tenant's GoCardless client
   */
  public static function get()
  {
    $at = config('GOCARDLESS_ACCESS_TOKEN');
    return;

    if (!$at) throw new \Exception('No access token');

    $client = null;

    if (bool(getenv('IS_DEV'))) {
      $client = new \GoCardlessPro\Client([
        'access_token'     => $at,
        'environment'     => \GoCardlessPro\Environment::SANDBOX
      ]);
    } else {
      $client = new \GoCardlessPro\Client([
        'access_token'     => $at,
        'environment'     => \GoCardlessPro\Environment::LIVE
      ]);
    }

    if ($client == null) throw new \Exception('No client');

    return $client;
  }
}
