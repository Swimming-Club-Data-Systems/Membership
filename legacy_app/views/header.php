<?php

/**
 * Putting this in a fn deals with some wierd laravel stuff
 */
function legacy_app_display_header() {

  global $pagetitle, $fluidContainer;

  $currentUser = nezamy_app()->user;
  $cvp = 'generic';
  if (nezamy_app()->tenant->isCLS() && $currentUser != null && $currentUser->getUserBooleanOption('UsesGenericTheme')) {
    $cvp = 'generic';
  } else if (nezamy_app()->tenant->isCLS()) {
    $cvp = 'chester';
  }
  
  include $cvp . '/header.php';
}

legacy_app_display_header();