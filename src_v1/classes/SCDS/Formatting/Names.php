<?php

namespace SCDS\Formatting;

class Names
{
  public static function format($first, $last)
  {

    $style = 'FL';
    if (app()->tenant && app()->tenant->getKey('DISPLAY_NAME_FORMAT')) {
      $style = app()->tenant->getKey('DISPLAY_NAME_FORMAT');
    }

    if (app()->user && app()->user->getUserOption('DISPLAY_NAME_FORMAT')) {
      $style = app()->tenant->getUserOption('DISPLAY_NAME_FORMAT');
    }

    if ($style == 'L,F') {
      return $last . ', ' . $first;
    } else {
      return $first . ' ' . $last;
    }
  }
}
