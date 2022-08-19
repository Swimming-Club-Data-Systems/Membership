<?php

http_response_code(404);
$pagetitle = "Error 404 - Tenant not found";

$clubDetails = null;
$code = mb_strtoupper($club);

if (($handle = fopen(BASE_PATH . "includes/regions/clubs.csv", "r")) !== false) {
  while (($data = fgetcsv($handle, 1000)) !== false) {
    if ($data[1] == $code) {
      $clubDetails = [
        'Name' => $data[0],
        'Code' => $data[1],
        'District' => $data[2],
        'County' => $data[3],
        'MeetName' => $data[4],
      ];
      break;
    }
  }
  fclose($handle);
}

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <?php if ($clubDetails) { ?>
        <h1><?= htmlspecialchars($clubDetails['Name']) ?> is not an SCDS Customer</h1>
        <p class="lead">
          <a href="mailto:sales@myswimmingclub.uk">Contact sales</a> to get SCDS Membership for your club.
        </p>

        <p>
          If you were looking for a different club, <a href="<?= htmlspecialchars(autoUrl('clubs')) ?>">please try searching our list</a>.
        </p>

        <p>
          HTTP Error 404 - File or directory not found.
        </p>

        <hr>

        <p class="mt-2">
          Contact our <a href="mailto:support@myswimmingclub.uk" title="Support Hotline">support hotline</a> if the issue persists.
        </p>
      <?php } else { ?>
        <h1>No club</h1>
        <p class="lead">
          We could not find a tenant matching <span class="font-monospace"><?= htmlspecialchars($club) ?></span>.
        </p>

        <p>
          <a href="mailto:sales@myswimmingclub.uk">Contact sales</a> to get SCDS Membership for your club.
        </p>

        <p>
          <a href="<?= htmlspecialchars(autoUrl('clubs')) ?>">Please try searching our list</a> to find your club.
        </p>

        <p>
          HTTP Error 404 - File or directory not found.
        </p>

        <hr>

        <p class="mt-2">
          Contact our <a href="mailto:support@myswimmingclub.uk" title="Support Hotline">support hotline</a> if the issue persists.
        </p>
      <?php } ?>

    </div>
  </div>
</div>

<?php $footer = new \SCDS\RootFooter();
$footer->render(); ?>