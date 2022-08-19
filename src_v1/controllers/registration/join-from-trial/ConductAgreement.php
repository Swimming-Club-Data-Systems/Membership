<?php

$db = app()->db;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Invited = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash'], true]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT First, Last FROM joinParents WHERE Hash = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash']]);
$parent_name = $query->fetch(PDO::FETCH_ASSOC);

$parent_name = $parent_name['First'] . ' ' . $parent_name['Last'];

$termsId = null;
try {
	$query = $db->prepare("SELECT ID FROM `posts` WHERE `Type` = ? LIMIT 1");
	$query->execute(['terms_conditions']);
  $termsId = $query->fetchColumn();
} catch (PDOException $e) {
	halt(500);
}

$parent = false;

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['AC-CC-Expected'])) {
  // We've just arrived on the conduct form page, so do the parent first.
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-CC-Expected']['Current'] = "Parent";
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-CC-Expected']['URL'] = "me";
  $parent = true;
  header("Location: " . autoUrl("register/ac/code-of-conduct/me"));
} else {
  if ($name != $_SESSION['TENANT-' . app()->tenant->getId()]['AC-CC-Expected']['URL']) {
    header("Location: " . autoUrl("register/ac/code-of-conduct/" . $_SESSION['TENANT-' . app()->tenant->getId()]['AC-CC-Expected']['URL']));
  }
  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AC-CC-Expected']['Current'] == "Parent") {
    $parent = true;
  }
}

$name = $CoCID = null;
if ($parent) {
  $name = $parent_name . " (the parent)";
} else {

  $query = $db->prepare("SELECT ID, First, Last, DoB, SquadSuggestion FROM joinSwimmers WHERE ID = ?");
  $query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-CC-Expected']['Current']]);
  $swimmer = $query->fetch();

  $name = $swimmer['First'] . ' ' . $swimmer['Last'];

  $query = $db->prepare("SELECT SquadCoC FROM squads WHERE SquadID = ?");
  $query->execute([$swimmer['SquadSuggestion']]);
  $CoCID = $query->fetchColumn();

}

$pagetitle = "Code of Conduct for " . $name;
$use_white_background = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>Code of Conduct <br><small class="text-muted">For <?=$name?></small></h1>
  <div class="row">
    <div class="col-sm-10 col-md-8">
      <form method="post" class="needs-validation" action="<?=autoUrl("register/ac/code-of-conduct")?>" novalidate>
        <?php if ($parent) {?>
					<p class="lead">
						You must accept the terms of this code of conduct to continue
					</p>

					<div id="code_of_conduct">
						<?= getPostContent(8) ?>
					</div>

					<p>
						By clicking continue, you acknowledge that you agree to the above code
						of conduct.
					</p>
        <?php } else { ?>

          <p class="lead">
            Your swimmers must agree to the relevant squad code of conduct in
            order to join the club.
          </p>

          <p>
            Agreeing to these documents online is equivalent to signing a paper
            form.
          </p>

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

          <h2><?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> Squad Code of Conduct</h2>
    			<?php if ($CoCID != null) { ?>
    				<div id="code_of_conduct">
    					<?= getPostContent($CoCID) ?>
    				</div>
          <?php } else { ?>
            <p>
    					A code of conduct does not exist for the allocated squad.
            </p>
          <?php } ?>

          <h2 class="h1">Swim England Code of Conduct for Swimmers</h2>
						<h3>General behaviour</h3>
						<ol>
							<li>
								I will treat all members of, and persons associated with, the
								ASA with due dignity and respect.
							</li>
							<li>
								I will treat everyone equally and never discriminate against
								another person associated with Swim England on any grounds including
								that of age, sexual orientation, gender, faith, ethnic origin or
								nationality.
							</li>
							<li>
								I understand that the use of inappropriate or abusive language,
								bullying, harassment, or physical violence will not be tolerated
								and could result in action being taken through the disciplinary
								or child welfare policies.
							</li>
							<li>
								I will display a high standard of behaviour at all times.
							</li>
							<li>
								I will always report any poor behaviour by others to an
								appropriate officer or member of staff.
							</li>
							<li>
								I will recognise and celebrate the good performance and success
								of fellow club and team members.
							</li>
							<li>
								I will respect the privacy of others, especially in the use of
								changing facilities.
							</li>
						</ol>

						<h3>Training</h3>
						<ol>
							<li>
								I will treat my coach and fellow members with respect.
							</li>
							<li>
								I will make my coach aware if I have difficulties in attending
								training sessions as per the rules laid down for my squad.
							</li>
							<li>
								I will arrive in good time on poolside before the training
								session starts to complete poolside warm up as directed by my
								coach.
							</li>
							<li>
								I understand that if I arrive late, I must report to my coach
								before entering the pool.
							</li>
							<li>
								I will ensure that I have all of my equipment with me, e.g.
								paddles, kick boards, hats, goggles, etc.
							</li>
							<li>
								If I need to leave the pool for any reason during training, I
								will inform my coach before doing so.
							</li>
							<li>
								I will listen to what my coach is telling me at all times and
								obey any instructions given.
							</li>
							<li>
								I will always swim to the wall as I would do in a race, and I
								will practice turns as instructed.
							</li>
							<li>
								I will not stop and stand in the lane, or obstruct others from
								completing their training.
							</li>
							<li>
								I will not pull on the ropes as this may injure other members.
							</li>
							<li>
								I will not skip lengths or sets – to do so means I would only be
								cheating myself.
							</li>
							<li>
								I will think about what I am doing during training, and if I
								have any problems, I will discuss them with my coach at an
								appropriate time.
							</li>
							<li>
								If I have any problems with the behaviour of fellow members, I
								will report them at the time to an appropriate adult.
							</li>
						</ol>

						<h3>Competitions</h3>
						<ol>
							<li>
								At competitions, whether they be open meets, national events or
								club galas, I will always behave in a manner that shows respect
								to my coach, the officers, my team mates and the members of all
								competing organisations.
							</li>
							<li>
								I understand that I will be required to attend events and galas
								that the Chief Coach has entered/selected me for, unless agreed
								otherwise by prior arrangement with the relevant official and
								coach.
							</li>
							<li>
								I understand that I must wear appropriate swimwear, tracksuits,
								T-shirts/shorts and hats as per the rules laid down by the
								organisation.
							</li>
							<li>
								I will report to my coach and/or team manager on arrival on
								poolside.
							</li>
							<li>
								I will warm up before the event as directed by the coach in
								charge on that day and ensure I fully prepare myself for the
								race.
							</li>
							<li>
								I will be part of the team. This means I will stay with the team
								on poolside.
							</li>
							<li>
								If I have to leave poolside for any reason, I will inform, and
								in some cases, get the consent of the team manager/coach before
								doing so.
							</li>
							<li>
								After my race, I will report to my coach for feedback.
							</li>
							<li>
								I will support my team mates. Everyone likes to be supported and
								they will be supporting me in return.
							</li>
							<li>
								I will swim down after the race if possible, as advised by my
								coach.
							</li>
							<li>
								My behaviour in the swim down facility must be appropriate and
								respectful to other users at all times.
							</li>
							<li>
								I will never leave an event until either the gala is complete or
								I have the explicit agreement of the coach or team manager.
							</li>
						</ol>

        <?php } ?>

        <div class="mb-3">
          <div class="form-check">
            <input type="checkbox" value="1" class="form-check-input" name="agreement" id="agreement" required>
            <label class="form-check-label" for="agreement">
              I, <?=$name?> agree to the Code of Conduct of <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> as
              outlined above
            </label>
            <div class="invalid-feedback">
              <?=$name?> must agree to the code of conduct.
            </div>
            <div class="valid-feedback">
              Fantastic! <?=$name?> has agreed to the code of conduct.
            </div>
          </div>
        </div>

        <p>
          <button class="btn btn-primary" type="submit">
            Accept and Continue
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
