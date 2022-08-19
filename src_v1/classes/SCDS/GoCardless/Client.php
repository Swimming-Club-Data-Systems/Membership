<?php

namespace SCDS\GoCardless;

class Client
{

  /**
   * Returns the current tenant's GoCardless client
   */
  public static function get()
  {
    $at = app()->tenant->getKey('GOCARDLESS_ACCESS_TOKEN');

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
