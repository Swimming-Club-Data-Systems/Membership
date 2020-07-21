<?php

$db = app()->db;


$parentCode = app()->tenant->getKey('ParentCodeOfConduct');

if (isset($id)) {
	$getDetails = $db->prepare("SELECT * FROM ((`squads` INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad) INNER JOIN `members` ON squadMembers.member = members.MemberID) WHERE `MemberID` = ?");
  $getDetails->execute([
    $id
  ]);
	$row = $getDetails->fetch(PDO::FETCH_ASSOC);

	$linkExists = false;
	$link = "";

	if (isset($row['SquadCoC']) && $row['SquadCoC'] != "") {
		$linkExists = true;
		$link = $row['SquadCoC'];
	}

	$pagetitle = htmlspecialchars($row['MForename'] . ' ' . $row['MSurname']) . " - Code of Conduct";
	include BASE_PATH . "views/header.php";
  include BASE_PATH . "views/renewalTitleBar.php";
	?>
<div class="container">
  <div class="row">
    <div class="col-lg-8">
      <div class="">
        <form method="post">
          <h1>Code of Conduct Acceptance</h1>
          <p class="lead">For <?=htmlspecialchars($row['MForename'] . ' ' . $row['MSurname'])?></p>

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RenewalErrorInfo'])) {
							echo $_SESSION['TENANT-' . app()->tenant->getId()]['RenewalErrorInfo'];
							unset($_SESSION['TENANT-' . app()->tenant->getId()]['RenewalErrorInfo']);
						} ?>

          <div class="alert alert-warning">
            <p>
              <strong>
                You must ensure that <?=htmlspecialchars($row['MForename'])?> is present to agree to this code of conduct before you continue as they must read and agree to this code of conduct.
              </strong>
            </p>

						<p>
							Please help explain the code of conduct if <?=htmlspecialchars($row['MForename'])?> does not understand it.
						</p>

            <p class="mb-0">
							Membership <?php if ($renewal == 0) { ?>registration<?php } else { ?>renewal<?php } ?> agreements are binding once the process is completed.
            </p>
          </div>

          <hr>

          <h2 class="h1"><?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> Squad Code of Conduct</h2>
          <?php if ($linkExists) { ?>
          <div id="code_of_conduct">
            <?=getPostContent($link)?>
          </div>
          <?php } else if ($parentCode != null) { ?>
          <p>
            A code of conduct does not exist for the allocated squad(s). A general code of conduct is available instead in the form of the Parental Code of Conduct and Swim England Code of Conduct for Swimmers (both below).
          </p>

          <?= getPostContent($parentCode) ?>

          <?php } else { ?>

          <p>
            A code of conduct does not exist for the allocated squad. A general code of conduct is available instead in the form of the Swim England Code of Conduct for Swimmers (both below).
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

					<div class="cell">
						<div class="form-group">
							<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input" id="agree" name="agree" value="1">
								<label class="custom-control-label" for="agree">
									I, <?=htmlspecialchars($row['MForename'] . ' ' . $row['MSurname'])?> agree to this code of conduct
								</label>
							</div>
						</div>

						<p class="mb-0">
							<button type="submit" class="btn btn-success">Continue</button>
						</p>
					</div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); } else {
    $pagetitle = "Code of	Conduct Acceptance";
    include BASE_PATH . "views/header.php";
		include BASE_PATH . "views/renewalTitleBar.php";
  ?>

<div class="container">
  <div class="row">
    <div class="col-lg-8">
      <div class="">
        <form method="post">
          <h1>Parent Code of Conduct</h1>
					<?php if ($parentCode != null) { ?>
          <p class="lead">
            You must accept the terms of this code of conduct to continue
          </p>

          <div class="alert alert-warning">
            <p>
              If you are not a parent of any members but a club member in your own right, please skip this step.
            </p>

            <p class="mb-0">
              <button type="submit" class="btn btn-warning">Skip parent agreement</button>
            </p>
          </div>

          <div id="code_of_conduct">
            <?= getPostContent($parentCode) ?>
          </div>

          <p>
            By clicking continue, you acknowledge that you agree to the above code of conduct.
          </p>
					<?php } else { ?>
					<p class="lead">
            No parent code of conduct is available. Please continue.
          </p>
					<?php } ?>

          <div>
            <button type="submit" class="btn btn-success">Accept and
              Continue</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
}
