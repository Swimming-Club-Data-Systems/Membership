<?php

$currentUser = null;
if (Auth::User()->getLegacyUser() !== null) {
  $currentUser = Auth::User()->getLegacyUser();
}
$cvp = 'generic';

$bg = "";
if (isset($customBackground) && $customBackground) {
  $bg = $customBackground;
}

// if (tenant() && tenant()->getLegacyTenant()->isCLS() && $currentUser != null && $currentUser->getUserBooleanOption('UsesGenericTheme')) {
//   $cvp = 'generic';
// } else if (tenant() && tenant()->getLegacyTenant()->isCLS()) {
//   $cvp = 'chester';
// }

include $cvp . '/GlobalHead.php';

?>

<body class="<?= $bg ?> <?php if (defined("USE_TAILWIND") && USE_TAILWIND) { ?>font-sans antialiased min-h-full bg-gray-100 text-gray-600 text-sm<?php } ?> account--body <?php if (isset($pageHead['body-class'])) {
                                        foreach ($pageHead['body-class'] as $item) { ?> <?= $item ?> <?php }
                                                                                                  } ?>" <?php if (isset($pageHead['body'])) {
                                                                                                          foreach ($pageHead['body'] as $item) { ?> <?= $item ?> <?php }
                                                                                                                                                              } ?>>