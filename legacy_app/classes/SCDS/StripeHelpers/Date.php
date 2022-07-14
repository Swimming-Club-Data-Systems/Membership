<?php

namespace SCDS\StripeHelpers;

class Date {
  public static function toDatabaseFormat($unix) {
    $date = \DateTime::createFromFormat("U", $unix, new \DateTimeZone("UTC"));
    return $date->format("Y-m-d H:i:s");
  }
}
