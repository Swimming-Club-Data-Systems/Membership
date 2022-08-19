<?php

// https://chesterlestreetasc.co.uk/wp-json/wp/v2/posts
// https://www.swimming.org/sport/wp-json/wp/v2/posts

$obj = null;
if (app()->tenant->isCLS()) {
	$file = getCachedFile(CACHE_DIR . 'CLS-ASC-News.json', 'https://chesterlestreetasc.co.uk/wp-json/wp/v2/posts?rand_id=' . time(), 10800);
	$obj = json_decode($file);
}

$file = getCachedFile(CACHE_DIR . 'SE-News.json', 'https://www.swimming.org/sport/wp-json/wp/v2/posts?rand_id=' . time(), 10800);
$asa = json_decode($file);

$file = getCachedFile(CACHE_DIR . 'SE-NE.xml', 'https://asaner.org.uk/feed?rand_id=' . time(), 10800);
$asa_ne = null;
try {
	$asa_ne = new SimpleXMLElement($file);
} catch (Exception $e) {
}

$db = app()->db;
$tenant = app()->tenant;

$username = htmlspecialchars(explode(" ", getUserName($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']))[0]);

$day = (new DateTime('now', new DateTimeZone('Europe/London')))->format("w");
$time = (new DateTime('-15 minutes', new DateTimeZone('Europe/London')))->format("H:i:s");
$time30 = (new DateTime('-30 minutes', new DateTimeZone('Europe/London')))->format("H:i:s");

$sql = "SELECT SessionID, SessionName, VenueName, StartTime, EndTime FROM (`sessions` INNER JOIN sessionsVenues ON sessions.VenueID = sessionsVenues.VenueID) WHERE SessionDay = :day AND StartTime <= :timenow AND (EndTime > :timenow OR EndTime > :time30) AND DisplayFrom <= CURDATE() AND DisplayUntil >= CURDATE() AND `sessions`.`Tenant` = :tenant ORDER BY StartTime ASC, EndTime ASC";
$getSessionSquads = $db->prepare("SELECT SquadName, ForAllMembers FROM `sessionsSquads` INNER JOIN `squads` ON sessionsSquads.Squad = squads.SquadID WHERE sessionsSquads.Session = ? ORDER BY SquadFee DESC, SquadName ASC;");

$query = $db->prepare($sql);
$query->execute(['day' => $day, 'timenow' => $time, 'time30' => $time30, 'tenant' => $tenant->getId()]);
$sessions = $query->fetchAll(PDO::FETCH_ASSOC);
// $sessions = [];

$query = $db->prepare("SELECT EmailAddress FROM users WHERE UserID = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$userInfo = $query->fetch(PDO::FETCH_ASSOC);

$getLocations = $db->prepare("SELECT COUNT(*) FROM `covidLocations` WHERE `Tenant` = ?");
$getLocations->execute([
	$tenant->getId(),
]);
$showCovid = $getLocations->fetchColumn() > 0;

$pagetitle = "Home";
include BASE_PATH . "views/header.php";

?>

<div class="front-page mb-n3">
	<div class="container-xl">

		<h1><?= helloGreeting() ?> <?= $username ?></h1>
		<p class="lead mb-4">Welcome to your account</p>

		<?php if ($bankHoliday = isBankHoliday()) { ?>
			<aside class="row mb-4">
				<div class="col-lg-6">
					<div class="cell bg-tenant-brand tenant-colour">
						<h2 class="mb-0"><?php if ($bankHoliday['bunting']) { ?>It's <?= htmlspecialchars($bankHoliday['title']) ?>!<?php if ($bankHoliday['notes']) { ?> <em><?= htmlspecialchars($bankHoliday['notes']) ?></em>.<?php } ?><?php } else { ?>Today is <?= htmlspecialchars($bankHoliday['title']) ?>.<?php if ($bankHoliday['notes']) { ?> <em><?= htmlspecialchars($bankHoliday['notes']) ?></em>.<?php } ?><?php } ?></h2>
						<p class="lead mb-0">There may be session cancellations or alterations today.</p>
					</div>
				</div>
			</aside>
		<?php } ?>

		<?php if (app()->user->hasPermission('Admin') && $tenant->getKey('GOCARDLESS_ACCESS_TOKEN') && !$tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) { ?>
			<div class="alert alert-info">
				<p class="mb-0">
					<strong>Plan your migration to Stripe for your Direct Debit payments</strong>
				</p>
				<p class="mb-0">
					The GoCardless service is deprecated and will eventually be turned off. To start planning your migration to the Stripe powered service, please talk to SCDS for help and support.
				</p>
			</div>
		<?php } ?>

		<div class="mb-4">
			<h2 class="mb-4">COVID-19 Tools</h2>
			<div class="news-grid">

				<?php if ($showCovid) { ?>
					<a href="<?= autoUrl('covid/contact-tracing/check-in') ?>">
						<span class="mb-3">
							<span class="title mb-0">
								Register your attendance
							</span>
							<span>
								Through Contact Tracing, we can help the NHS
							</span>
						</span>
						<span class="category">
							COVID Tools
						</span>
					</a>
				<?php } ?>

				<a href="<?= autoUrl('covid/health-screening') ?>">
					<span class="mb-3">
						<span class="title mb-0">
							Health Screening Survey
						</span>
						<span>
							Complete your periodic screening survey for training
						</span>
					</span>
					<span class="category">
						COVID Tools
					</span>
				</a>

				<a href="<?= autoUrl('covid/risk-awareness') ?>">
					<span class="mb-3">
						<span class="title mb-0">
							<?php if (mb_strtoupper(app()->tenant->getKey('ASA_CLUB_CODE')) == 'UOSZ') { ?><?= htmlspecialchars(UOS_RETURN_FORM_NAME) ?><?php } else { ?>Risk Awareness Declaration<?php } ?>
						</span>
						<span>
							Declare that you understand the risks of returning to training
						</span>
					</span>
					<span class="category">
						COVID Tools
					</span>
				</a>

			</div>
		</div>

		<!--<p class="mb-4">We're always looking for feedback! If you have any, <a href="mailto:feedback@myswimmingclub.uk">send us an email</a>.</p>-->

		<?php if (sizeof($sessions) > 0) { ?>
			<?php $date = (new DateTime('now', new DateTimeZone('Europe/London')))->format("Y-m-d"); ?>
			<div class="mb-4">
				<h2 class="mb-4">Take Register for Current Sessions</h2>
				<div class="mb-4">
					<div class="news-grid">
						<?php for ($i = 0; $i < sizeof($sessions); $i++) {
							$getSessionSquads->execute([
								$sessions[$i]['SessionID'],
							]);
							$squadNames = $getSessionSquads->fetchAll(PDO::FETCH_ASSOC);
						?>
							<a href="<?= htmlspecialchars(autoUrl("attendance/register?date=" . urlencode($date) . "&session=" . urlencode($sessions[$i]['SessionID']))) ?>" title="<?= htmlspecialchars($sessions[$i]['SessionName']) ?>, <?= htmlspecialchars($sessions[$i]['VenueName']) ?>">
								<div>
									<span class="title mb-0">
										Take <?php for ($y = 0; $y < sizeof($squadNames); $y++) { ?><?php if ($y > 0) { ?>, <?php } ?><?= htmlspecialchars($squadNames[$y]['SquadName']) ?><?php } ?> Register
									</span>
									<span class="d-flex mb-3">
										<?= date("H:i", strtotime($sessions[$i]['StartTime'])) ?> - <?= date("H:i", strtotime($sessions[$i]['EndTime'])) ?>
									</span>
								</div>
								<span class="category">
									<?= htmlspecialchars($sessions[$i]['SessionName']) ?>, <?= htmlspecialchars($sessions[$i]['VenueName']) ?>
								</span>
							</a>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>

		<div class="mb-4">
			<h2 class="mb-4">Quick Tasks</h2>
			<div class="news-grid">

				<a href="<?= autoUrl('attendance/register') ?>">
					<span class="mb-3">
						<span class="title mb-0">
							Take Register
						</span>
						<span>
							Take the register for your squad
						</span>
					</span>
					<span class="category">
						Attendance
					</span>
				</a>

				<a href="<?= autoUrl('swimmers') ?>">
					<span class="mb-3">
						<span class="title mb-0">
							View Swimmer Notes
						</span>
						<span>
							Check important medical and other notes from parents
						</span>
					</span>
					<span class="category">
						Swimmers
					</span>
				</a>

				<a href="<?= autoUrl('squads/moves') ?>">
					<span class="mb-3">
						<span class="title mb-0">
							View Upcoming Squad Moves
						</span>
						<span>
							Check which swimmers are changing squads
						</span>
					</span>
					<span class="category">
						Squads
					</span>
				</a>

			</div>
		</div>

		<?php if (app()->tenant->isCLS()) { ?>
			<div class="mb-4">
				<h2 class="mb-4">Club News</h2>
				<div class="news-grid">
					<?php
					$max_posts = 6;
					if (sizeof($obj) < $max_posts) {
						$max_posts = sizeof($obj);
					}
					for ($i = 0; $i < $max_posts; $i++) { ?>
						<a href="<?= htmlspecialchars($obj[$i]->link) ?>" target="_blank" title="<?= ($obj[$i]->title->rendered) ?>">
							<span class="mb-3">
								<span class="title mb-0">
									<?= ($obj[$i]->title->rendered) ?>
								</span>
							</span>
							<span class="category">
								News
							</span>
						</a>
					<?php } ?>
				</div>
			</div>
		<?php } ?>

		<?php if ($asa != null && $asa != "") { ?>
			<div class="mb-4">
				<h2 class="mb-4">Swim England News</h2>
				<div class="news-grid">
					<?php
					$max_posts = 6;
					if (sizeof($asa) < $max_posts) {
						$max_posts = sizeof($asa);
					}
					for ($i = 0; $i < $max_posts; $i++) { ?>
						<a href="<?= htmlspecialchars($asa[$i]->link) ?>" target="_blank" title="<?= ($asa[$i]->title->rendered) ?>">
							<span class="mb-3">
								<span class="title mb-0">
									<?= ($asa[$i]->title->rendered) ?>
								</span>
							</span>
							<span class="category">
								News
							</span>
						</a>
					<?php } ?>
				</div>
			</div>
		<?php } ?>

		<?php if (app()->tenant->getKey('ASA_DISTRICT') == 'E' && $asa_ne != null) { ?>
			<div class="mb-4">
				<h2 class="mb-4">Swim England North East News</h2>
				<div class="news-grid">
					<?php
					$max_posts = 6;
					if (sizeof($asa_ne->channel->item) < $max_posts) {
						$max_posts = sizeof($asa_ne->channel->item);
					}
					for ($i = 0; $i < $max_posts; $i++) { ?>
						<a href="<?= htmlspecialchars($asa_ne->channel->item[$i]->link) ?>" target="_blank" title="<?= htmlspecialchars($asa_ne->channel->item[$i]->title) ?> (<?= htmlspecialchars($asa_ne->channel->item[$i]->category) ?>)">
							<span class="mb-3">
								<span class="title mb-0">
									<?= htmlspecialchars($asa_ne->channel->item[$i]->title) ?>
								</span>
							</span>
							<span class="category">
								<?= htmlspecialchars($asa_ne->channel->item[$i]->category) ?>
							</span>
						</a>
					<?php } ?>
				</div>
			</div>
		<?php } ?>

		<?php
		if (strpos($userInfo['EmailAddress'], '@chesterlestreetasc.co.uk')) {
		?>

			<div class="mb-4">
				<h2 class="mb-4">Access G Suite</h2>
				<div class="news-grid">
					<a href="https://mail.google.com/a/chesterlestreetasc.co.uk" target="_blank">
						<span class="mb-3">
							<span class="title mb-0">
								Gmail
							</span>
							<span>
								Access your club email
							</span>
						</span>
						<span class="category">
							G Suite
						</span>
					</a>

					<a href="https://drive.google.com/a/chesterlestreetasc.co.uk" target="_blank">
						<span class="mb-3">
							<span class="title mb-0">
								Google Drive
							</span>
							<span>
								Create Docs, Sheets, Slides and more - Club letterhead templates are available
							</span>
						</span>
						<span class="category">
							G Suite
						</span>
					</a>

					<a href="https://calendar.google.com/a/chesterlestreetasc.co.uk" target="_blank">
						<span class="mb-3">
							<span class="title mb-0">
								Google Calendar
							</span>
							<span>
								Manage your schedule and plan meetings with ease
							</span>
						</span>
						<span class="category">
							G Suite
						</span>
					</a>

					<a href="https://docs.google.com/a/chesterlestreetasc.co.uk/document/u/1/?tgif=d&ftv=1" target="_blank">
						<span class="mb-3">
							<span class="title mb-0">
								Use Club Letterheads
							</span>
							<span>
								Effortlessly create great club documents
							</span>
						</span>
						<span class="category">
							G Suite
						</span>
					</a>

					<a href="https://www.google.com/accounts/AccountChooser?hd=chesterlestreetasc.co.uk&continue=https://apps.google.com/user/hub" target="_blank">
						<span class="mb-3">
							<span class="title mb-0">
								More Applications
							</span>
							<span>
								Collaborate in real-time
							</span>
						</span>
						<span class="category">
							G Suite
						</span>
					</a>
				</div>
			</div>

		<?php
		}
		?>

	</div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
