<?php

namespace App\Business\Helpers;

use Illuminate\Support\Facades\App;
use Symfony\Component\Intl\Countries as IntlCountries;

class Countries
{
  public static function all()
  {
    return IntlCountries::getNames(App::currentLocale());
  }

  public static function getISOKeys()
  {
    return array_keys(self::all());
  }

  public static function getCountryName($code)
  {
    return IntlCountries::getName($code, App::currentLocale());
  }
}
