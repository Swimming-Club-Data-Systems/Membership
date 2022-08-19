<?php

$db = app()->db;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Invited = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash'], true]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$termsId = null;
try {
	$query = $db->prepare("SELECT ID FROM `posts` WHERE `Type` = ? LIMIT 1");
	$query->execute(['terms_conditions']);
  $termsId = $query->fetchColumn();
} catch (PDOException $e) {
	halt(500);
}

$query = $db->prepare("SELECT ID, First, Last, DoB FROM joinSwimmers WHERE Parent = ? AND SquadSuggestion IS NOT NULL ORDER BY First ASC, Last ASC");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash']]);
$swimmers = $query->fetchAll();

$selected = $_SESSION['TENANT-' . app()->tenant->getId()]['AC-TC-Selected'];

$pagetitle = "Membership Terms";
$use_white_background = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>Club Administration</h1>
  <div class="row">
    <div class="col-sm-10 col-md-8">
      <form method="post" class="needs-validation" novalidate>
        <p class="lead">
          You and your swimmers must agree to the terms and conditions of
          membership and relevant squad codes of conduct in order to join the club.
        </p>

        <p>
          Agreeing to these documents online is equivalent to signing a paper
          form.
        </p>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AC-TC-ErrorNames'])) { ?>
          <div class="alert alert-warning">
            <p class="mb-0">
              All swimmers must agree to the terms and conditions. The following
              have not.
            </p>
            <ul class="mb-0">
            <?php
            foreach ($_SESSION['TENANT-' . app()->tenant->getId()]['AC-TC-ErrorNames'] as $name) {
              ?><li><?=$name?></li><?php
            }
            ?>
            </ul>
            You must agree to the Terms and Conditions in order to join the club
          </div>
        <?php }
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['AC-TC-ErrorNames']); ?>

        <div class="alert alert-info">
          <p class="mb-0">
            <strong>
              Have swimmers under 13?
            </strong>
          </p>
          <p class="mb-0">
            You'll need to confirm that you've explained the terms &amp;
            conditions and code of conduct to each of these swimmers.
          </p>
        </div>


        <h2>Terms and Conditions of the Club</h2>
				<div class="focus-highlight" id="ts-and_cs">
					<?=getPostContent($termsId)?>
				</div>

        <p>
					The Member, and the Parent or Guardian (in the case of a person under
					the age of 18 years), hereby acknowledges that they have read the Club
					Rules and the Policies and Procedures Documentation of <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>,
					copies of which can be obtained from <a
					href="https://www.chesterlestreetasc.co.uk/policies"
					target="_blank">our website</a>. I confirm my understanding and
					acceptance that such rules (as amended from time to time) shall govern
					my membership of the club. I further acknowledge and accept the
					responsibilities of membership as set out in these rules and
					understand that it is my duty to read and abide by them (including any
					amendments). By providing my agreement, I consent to be bound by the
					Code of Conduct, Constitution, Rules and Policy Documents of the club.
        </p>

        <div class="alert alert-warning">
					<p class="mb-0">
						<strong>
							Each swimmer must agree to this section separately
						</strong>
					</p>
					<p class="mb-0">
						We've provided a box where each swimmer can tick for themselves.
					</p>
        </div>

        <!-- FOR EACH SWIMMER AND THEIR UNIQUE IDENTIFIER, SHOW A CHECKBOX -->
        <?php foreach ($swimmers as $swimmer) {
          $id = $swimmer['ID'] . '-tc-confirm'; ?>
          <div class="mb-3">
						<div class="form-check">
							<input type="checkbox" value="1" class="form-check-input" name="<?=$id?>" id="<?=$id?>" <?=$selected[$id]?> required>
							<label class="form-check-label" for="<?=$id?>">
								I, <?=$swimmer['First']?> <?=$swimmer['Last']?>
								agree to the Terms and Conditions of <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> as outlined
								above
							</label>
              <div class="invalid-feedback">
                <?=$swimmer['First']?> must agree to the terms and conditions.
              </div>
              <div class="valid-feedback">
                Fantastic! <?=$swimmer['First']?> has agreed to the terms and conditions.
              </div>
						</div>
          </div>
        <?php } ?>

        <p>
          <button class="btn btn-primary" type="submit">
            Next
          </button>
        </p>

      </form>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
