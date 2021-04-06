<?php

$pagetitle = "Notify";



include BASE_PATH . "views/header.php";
?>

<div class="container">
	<div class="row">
		<div class="col-lg-8 mb-5">
			<h1>Notify from <?=htmlspecialchars(nezamy_app()->tenant->getKey('CLUB_NAME'))?></h1>
			<p class="lead">
				Notify is the SCDS member mailing
				list solution.
			</p>
			<p>
				This General Data Protection Regulation Compliant system enables rapid
				communication with our members. The  system allows us to target emails
				to parents of selected squads, those who have entered certain galas or those in select groups and supports modern email standards.
			</p>
			<p>
				To unsubscribe or resubscribe to messages sent by Notify, go to <a
				href="<?php echo autoUrl("my-account"); ?>">My Account</a>. You can also
				control your SMS Messaging preferences there.
			</p>
      <p>
        Many emails will also come with an unsubscribe link at the end, though
        mandatory information emails won't.
      </p>

			<p>Please note that occasionally your club may send you an email regardless of your opt-in/opt-out settings if there is a legitimate business purpose behind doing so.</p>
		</div>
	</div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
