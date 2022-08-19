<?php

if (!isset($_SESSION['SCDS-SuperUser'])) {
  http_response_code(302);
  header("location: " . autoUrl('admin/login'));
  return;
}

$clubs = [];

$row = 1;
if (($handle = fopen(BASE_PATH . "includes/regions/clubs.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000)) !== false) {
    if ($row > 1) {
      $clubs += [$data[1] => [
        'Name' => $data[0],
        'Code' => $data[1],
        'District' => $data[2],
        'County' => $data[3],
        'MeetName' => $data[4],
      ]];
    }
    $row++;
  }
  fclose($handle);
}

$pagetitle = "Add Tenant - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>Club Registration</h1>
      <p class="lead">We just need a few details about your organisation to get started.</p>

      <form method="post">

        <h2>About your club</h2>

        <div class="mb-3">
          <label class="form-label" for="club">Club</label>
          <select class="form-select" name="club" id="club">
            <option selected value="select">Select a club</option>
            <option value="not-se">Not Swim England Registered</option>
            <?php foreach ($clubs as $club) { ?>
              <option value="<?= htmlspecialchars($club['Code']) ?>">
                <?= htmlspecialchars($club['Name']) ?> (<?= htmlspecialchars($club['Code']) ?>)
              </option>
            <?php } ?>
          </select>
        </div>

        <div class="mb-3">
            <label class="form-label" for="CLUB_NAME">Club Name</label>
            <input class="form-control" type="text" name="CLUB_NAME" id="CLUB_NAME" placeholder="Anytown ASC">
          </div>

          <div class="mb-3">
            <label class="form-label" for="CLUB_SHORT_NAME">Club Short Name</label>
            <input class="form-control" type="text" name="CLUB_SHORT_NAME" id="CLUB_SHORT_NAME" placeholder="AT ASC">
          </div>

          <div class="mb-3">
            <label class="form-label" for="CLUB_WEBSITE">Club Website</label>
            <input class="form-control font-monospace" type="url" name="CLUB_WEBSITE" id="CLUB_WEBSITE" placeholder="https://anytownasc.org.uk">
          </div>

          <div class="mb-3">
            <label class="form-label" for="CLUB_ADDRESS">Club Primary Address</label>
            <textarea class="form-control" rows="5" id="CLUB_ADDRESS" name="CLUB_ADDRESS" aria-describedby="CLUB_ADDRESS_HELP"></textarea>
            <small id="CLUB_ADDRESS_HELP" class="form-text text-muted">Enter the address of your primary location. Do not include your club name and do not place commas at the end of lines.</small>
          </div>

        <h2>About you</h2>

        <div class="row">
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="fn">First name</label>
              <input type="text" name="fn" id="fn" class="form-control" required autocomplete="given-name" placeholder="First">
            </div>
          </div>
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="ln">Last name</label>
              <input type="text" name="ln" id="ln" class="form-control" required autocomplete="family-name" placeholder="Last">
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="user-email">Email address</label>
          <input type="email" name="user-email" id="user-email" class="form-control" required autocomplete="email" placeholder="yourname@example.com">
        </div>

        <div class="mb-3">
          <label class="form-label" for="user-password">Password</label>
          <input type="password" name="user-password" id="user-password" class="form-control" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" autocomplete="new-password" required placeholder="Password">
        </div>

        <div class="mb-3">
          <label class="form-label" for="user-phone">Telephone</label>
          <input type="tel" name="user-phone" id="user-phone" class="form-control" pattern="\+{0,1}[0-9]*" autocomplete="tel" required placeholder="01234 567890">
        </div>

        <p>
          <button type="submit" class="btn btn-primary">Sign up</button>
        </p>

      </form>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\RootFooter();
$footer->render(); ?>