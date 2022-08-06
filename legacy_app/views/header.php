<?php

$currentUser = null;
if (isset(app()->user)) {
  $currentUser = app()->user;
}
$cvp = 'generic';
// if (tenant()->getLegacyTenant()->isCLS() && $currentUser != null && $currentUser->getUserBooleanOption('UsesGenericTheme')) {
//   $cvp = 'generic';
// } else if (tenant()->getLegacyTenant()->isCLS()) {
//   $cvp = 'chester';
// }

include $cvp . '/header.php';
