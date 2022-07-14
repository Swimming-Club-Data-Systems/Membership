<?php

$pagetitle = "No network connection";

include BASE_PATH . "views/header.php";

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>You're offline</h1>
      <p class="lead">There's no internet connection right now and we don't have a cached copy of this page to show you.</p>

      <hr>
      <p>Please try the following:</p>
      <ul>
        <li>Check your WiFi is turned on and connected or that your ethernet cables are securely plugged into your computer and the wall.</li>
        <li>Check whether you can access any other websites.</li>
        <li>Restart your router or access point if issues persist.</li>
      </ul>
      <p>Network Error - Offline.</p>
      <hr>

      <p class="mt-2">We're unable to provide help and support for network issues. Try contacting your internet service provider if the issue persists.</p>

    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>
