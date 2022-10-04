<?php

// Inserts the standard letterhead on PDF pages
$addr = json_decode(app()->tenant->getKey('CLUB_ADDRESS'));
$logoPath = null;
if ($logos = app()->tenant->getKey('LOGO_DIR')) {
  $logoPath = getUploadedAssetUrl($logos . 'logo-1024.png');
  // app()->tenant->getFilePath() . 'public/' . mb_substr($logos, 8) . 'logo-1024.png';
}

?>

<div class="row mb-3">
  <div class="split-50">
    <?php if ($logoPath) { ?>
    <img src="<?=$logoPath?>" class="logo">
    <?php } else { ?>
      <h1 class="primary"><?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?></h1>
    <?php } ?>
  </div>
  <div class="split-50 text-end">
    <!-- <p class="mb-0"> -->
      <address>
      <strong><?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?></strong><br>
      <?php
      for ($i = 0; $i < sizeof($addr); $i++) { ?>
        <?=htmlspecialchars($addr[$i])?><br>
        <?php if (isset($addr[$i+1]) && $addr[$i+1] == "") {
          break;
        } ?>
      <?php } ?>
      </address>
    <!-- </p> -->
  </div>
</div>
