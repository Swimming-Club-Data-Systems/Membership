<?php

$currentUser = null;
if (isset(app()->user)) {
  $currentUser = app()->user;
}
$cvp = 'generic';
// if (app()->tenant->isCLS() && $currentUser != null && $currentUser->getUserBooleanOption('UsesGenericTheme')) {
//   $cvp = 'generic';
// } else if (app()->tenant->isCLS()) {
//   $cvp = 'chester';
// }

include $cvp . '/header.php';
