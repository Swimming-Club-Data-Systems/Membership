<?php

if ($this->showContent) {

?>
  </div>
<?php

  include 'footer-content.php';
}

?>

<!-- Modals and Other Hidden HTML -->
<?php

$script = autoUrl(getCompiledAsset('main.js'), false);

?>
<script rel="preload" src="<?= htmlspecialchars($script) ?>"></script>
<?php if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) || !$_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) { ?>
  <script src="<?= htmlspecialchars(autoUrl("js/app.js", false)) ?>"></script>
<?php } ?>

<?php if (isset($this->js)) { ?>
  <!-- Load per page JS -->
  <?php foreach ($this->js as $script) { ?>
    <script <?php if ($script['module']) { ?>type="module" <?php } ?> src="<?= htmlspecialchars($script['url']) ?>"></script>
  <?php } ?>
<?php } ?>

<?php if (!bool(getenv('IS_DEV'))) { ?>
  <!-- Cloudflare Web Analytics -->
  <script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "579ac2dc2ea54799918144a5e7d894ef"}'></script><!-- End Cloudflare Web Analytics -->
<?php } ?>

</body>

</html>