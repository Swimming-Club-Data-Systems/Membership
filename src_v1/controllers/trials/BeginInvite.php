<?php

$db = app()->db;
$tenant = app()->tenant;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Tenant = ?");
$query->execute([
  $hash,
  $tenant->getId(),
]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT First, Last, Email, Hash FROM joinParents WHERE Hash = ? AND Tenant = ?");
$query->execute([
  $hash,
  $tenant->getId()
]);

$parent = $query->fetch(PDO::FETCH_ASSOC);

$query = $db->prepare("SELECT ID, First, Last, SquadSuggestion, SquadName, SquadFee FROM joinSwimmers INNER JOIN squads ON squads.SquadID = joinSwimmers.SquadSuggestion WHERE Parent = ? AND SquadSuggestion IS NOT NULL AND joinSwimmers.Tenant = ? ORDER BY First ASC, Last ASC");
$query->execute([
  $hash,
  $tenant->getId()
]);

$swimmers = $query->fetchAll(PDO::FETCH_ASSOC);

$pagetitle = "Join Club - " . htmlspecialchars($parent['First'] . ' ' . $parent['Last']);
$use_white_background = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>Invite <?=htmlspecialchars($parent['First'] . ' ' . $parent['Last'])?></h1>
  <div class="row">
    <div class="col-sm-10 col-md-8">
      <form method="post">
        <p class="lead">
          You are about to invite <?=htmlspecialchars($parent['First'] . ' ' .
          $parent['Last'])?> as a parent to join the club with their swimmers.
        </p>
        <p>
          <?=htmlspecialchars($parent['First'])?> will receive an email from our
          system with instructions on how to set a password. All swimmers will
          be connected to the account automatically.
        </p>
        <p>
          Swimmers will only be added to our database when
          <?=htmlspecialchars($parent['First'])?> sets up their user account.
          Swimmers will not appear on our systems until this is done.
        </p>

        <h2>Review Swimmers</h2>
        <p>
          Please check that the swimmers listed below are the ones that you
          expect to be joining the club.
        </p>

        <?php foreach ($swimmers as $s) { ?>
          <div class="cell">
            <h3><?=htmlspecialchars($s['First'] . ' ' . $s['Last'])?></h3>
            <p><?=htmlspecialchars($s['SquadName'])?> at &pound;<?=number_format($s['SquadFee'], 2)?></p>
          </div>
        <?php } ?>

        <h2>Review Parent Email Address</h2>
        <p>
          Please check the email address for
          <?=htmlspecialchars($parent['First'])?> is correct. It will be used to
          set up their account. If a user account already exists with the
          parent's email address, the swimmers will be added to that existing
          account.
        </p>

        <div class="mb-3">
          <label class="form-label" for="email-addr">Email address</label>
          <input type="email" class="form-control" id="email-addr" name="email-addr" placeholder="name@example.com" value="<?=htmlspecialchars($parent['Email'])?>">
        </div>

        <p>
          <button class="btn btn-primary" type="submit">
            Invite to club
          </button>
        </p>

      </form>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
