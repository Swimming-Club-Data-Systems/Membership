<?php

$fluidContainer = true;

$pagetitle = "System Settings";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 order-1 order-md-0">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('settings-home');
      ?>
    </aside>
    <div class="col-md-9 order-0 order-md-1">
      <main>
        <h1>System Settings</h1>
        <p class="lead">Manage system options</p>

        <p>You can change a wide range of system settings. The most important are <strong>System variables</strong> which include details such as your club name and preferred colour as well as API keys for third party services such as GoCardless.</p>

        <p>You can also connect or create your Stripe account for card payments and direct debit payments.</p>

        <p>You're also able to set club and Swim England membership fees, and set rules for discounts.</p>

        <p>Select a category from the menu to make changes.</p>
      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
