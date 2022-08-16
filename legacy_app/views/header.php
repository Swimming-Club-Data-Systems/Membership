<?php

$currentUser = null;
if (Auth::User()->getLegacyUser() !== null) {
  $currentUser = Auth::User()->getLegacyUser();
}
$cvp = 'generic';
// if (tenant()->getLegacyTenant()->isCLS() && $currentUser != null && $currentUser->getUserBooleanOption('UsesGenericTheme')) {
//   $cvp = 'generic';
// } else if (tenant()->getLegacyTenant()->isCLS()) {
//   $cvp = 'chester';
// }

include $cvp . '/header.php';
