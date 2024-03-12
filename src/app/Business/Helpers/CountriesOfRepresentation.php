<?php

namespace App\Business\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Symfony\Component\Intl\Countries as IntlCountries;

class CountriesOfRepresentation
{
    private static $homeNations = [
        'GB-ENG' => 'England',
        'GB-NIR' => 'Northern Ireland (Ulster Swimming)',
        'GB-SCT' => 'Scotland',
        'GB-WLS' => 'Wales',
    ];

    public static function all()
    {
        $countries = IntlCountries::getNames(App::currentLocale());
        unset($countries['GB']);

        $homeNations = [
            'GB-ENG' => 'England',
            'GB-NIR' => 'Northern Ireland (Ulster Swimming)',
            'GB-SCT' => 'Scotland',
            'GB-WLS' => 'Wales',
        ];

        return $homeNations + $countries;
    }

    public static function getISOKeys()
    {
        return array_keys(self::all());
    }

    public static function getCountryName($code)
    {
        if (Arr::has(self::$homeNations, $code)) {
            return self::$homeNations[$code];
        }

        return IntlCountries::getName($code, App::currentLocale());
    }
}
