<?php

require 'GlobalHead.php';

?>

<body class="<?= $bg ?> account--body <?php if (isset($pageHead['body-class'])) {
                                        foreach ($pageHead['body-class'] as $item) { ?> <?= $item ?> <?php }
                                                                                                  } ?>" <?php if (isset($pageHead['body'])) {
                                                                                                          foreach ($pageHead['body'] as $item) { ?> <?= $item ?> <?php }
                                                                                                                                                              } ?>>

  <?php
  require "header-content.php";

  ?>

<div class="have-full-height focus-highlight">