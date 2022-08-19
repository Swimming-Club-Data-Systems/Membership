<?php

$currentUser = null;
if (isset(app()->user)) {
  $currentUser = app()->user;
}
$cvp = 'generic';

$bg = "";
if (isset($customBackground) && $customBackground) {
  $bg = $customBackground;
}

// if (isset(app()->tenant) && app()->tenant->isCLS() && $currentUser != null && $currentUser->getUserBooleanOption('UsesGenericTheme')) {
//   $cvp = 'generic';
// } else if (isset(app()->tenant) && app()->tenant->isCLS()) {
//   $cvp = 'chester';
// }

include $cvp . '/GlobalHead.php';

?>

<body class="<?= $bg ?> account--body <?php if (isset($pageHead['body-class'])) {
                                        foreach ($pageHead['body-class'] as $item) { ?> <?= $item ?> <?php }
                                                                                                  } ?>" <?php if (isset($pageHead['body'])) {
                                                                                                          foreach ($pageHead['body'] as $item) { ?> <?= $item ?> <?php }
                                                                                                                                                              } ?>>