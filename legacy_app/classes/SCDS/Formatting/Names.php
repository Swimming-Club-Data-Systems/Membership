<?php

namespace SCDS\Formatting;

class Names
{
  public static function format($first, $last)
  {

    $style = 'FL';
    if (tenant()->getLegacyTenant() && config('DISPLAY_NAME_FORMAT')) {
      $style = config('DISPLAY_NAME_FORMAT');
    }

    if (app()->user && app()->user->getUserOption('DISPLAY_NAME_FORMAT')) {
      $style = tenant()->getLegacyTenant()->getUserOption('DISPLAY_NAME_FORMAT');
    }

    if ($style == 'L,F') {
      return $last . ', ' . $first;
    } else {
      return $first . ' ' . $last;
    }
  }
}
