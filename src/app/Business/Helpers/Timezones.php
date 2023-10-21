<?php

namespace App\Business\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Timezones
{
    public static function getTimezoneSelectList()
    {
        return Arr::map(\DateTimeZone::listIdentifiers(), function ($item) {
            $split = Str::of($item)->split('[/]');

            return [
                'key' => $item,
                'name' => count($split) > 1 ? $split[0].' / '.Str::replace('_', ' ', $split[1]) : $split[0],
                'value' => $item,
            ];
        });
    }
}
