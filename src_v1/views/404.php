<?php

$trace = debug_backtrace();

http_response_code(404);
$pagetitle = "Error 404 - Page not found";
$currentUser = null;
if (isset(app()->user)) {
  $currentUser = app()->user;
}
if ($currentUser == null && false) {
  $clubLogoColour = 'text-white logo-text-shadow';
  $navTextColour = 'navbar-dark';
  $clubLinkColour = 'btn-light';

  if (app()->tenant->getKey('SYSTEM_COLOUR') && getContrastColor(app()->tenant->getKey('SYSTEM_COLOUR'))) {
    $clubLogoColour = 'text-dark';
    $navTextColour = 'navbar-light';
    $clubLinkColour = 'btn-dark';
  }

  include BASE_PATH . "views/head.php";

?>
  <div class="py-3 mb-3 text-white membership-header <?= $clubLogoColour ?>">
    <div class="container-xl">
      <h1 class="mb-0">
        <a href="<?= autoUrl("") ?>" class="<?= $clubLogoColour ?>">
          <strong>
            <?= mb_strtoupper(htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))) ?>
          </strong>
        </a>
      </h1>
    </div>
  </div>
<?php
} else {
  include BASE_PATH . "views/header.php";
}
?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>The page you requested cannot be found</h1>

      <?php if ($currentUser) {

        // Get the user's access levels
        $permissions = $currentUser->getPermissions();

        if (sizeof($permissions) > 1) { ?>
          <p class="lead">
            If you expected to see something here, you may want to try reloading this page with an appropriate access level.
          </p>

          <div class="card">
            <div class="card-header">
              Reload this page as
            </div>
            <div class="list-group list-group-flush">
              <?php
              $url = rtrim(app('request')->curl, '/');
              $queries = app('request')->query;
              $i = 0;
              foreach ($queries as $key => $value) {
                if ($i == 0) {
                  $url .= '?';
                } else {
                  $url .= '&';
                }
                $url .= $key . '=' . urlencode($value);
              }
              ?>
              <?php foreach ($permissions as $permission) {
                $disabled = $permission == $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
              ?>
                <a href="<?= htmlspecialchars(autoUrl("account-switch?type=" . urlencode($permission) . "&redirect=" . urlencode($url))) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php if ($disabled) { ?>user-select-none disabled<?php } ?>">
                  <?= htmlspecialchars($permission) ?> <?php if ($disabled) { ?><span>Current mode <i class="text-primary fa fa-check-circle fa-fw" aria-hidden="true"></i></span><?php } ?>
                </a>
              <?php
              } ?>
            </div>
          </div>

        <?php } else { ?>
          <p class="lead">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable. You may also not be authorised to view the page.
          </p>
        <?php } ?>
      <?php } ?>

      <hr>
      <p>Please try the following:</p>
      <ul>
        <li>Make sure that the Web site address displayed in the address bar of your browser is spelled and formatted
          correctly.</li>
        <li>If you reached this page by clicking a link, contact the Web site administrator to alert them that the link
          is incorrectly formatted.</li>
        <li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
      </ul>
      <p>HTTP Error 404 - File or directory not found.</p>
      <hr>

      <p class="mt-2"><a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email SCDS</a> for help and support if the issue persists.</p>

      <div id="trace" class="font-monospace d-none">
        <?php foreach ($trace as $key => $item) { ?>
          <?php if (isset($item['file']) && isset($item['line'])) { ?>
            <strong><?= htmlspecialchars('./' . str_replace(BASE_PATH, '', $item['file'])) ?></strong> <?= htmlspecialchars($item['line']) ?><?php if (isset($item['function'])) { ?> (<?= htmlspecialchars($item['function']) ?>)<?php } ?><br>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>