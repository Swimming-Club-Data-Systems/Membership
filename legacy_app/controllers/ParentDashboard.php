<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

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

$swimmers = [];
try {
  $query = $db->prepare("SELECT `MemberID` FROM `members` WHERE `members`.`UserID` = ? ORDER BY `MForename` ASC,
	`MSurname` ASC");
  $query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $swimmerIds = $query->fetchAll(PDO::FETCH_COLUMN);
  foreach ($swimmerIds as $id) {
    $swimmers[] = new \Member($id);
  }
} catch (Exception $e) {
  halt(500);
}

try {
  $sql = 'SELECT `MForename`, `MSurname`, `GalaName`, `FeeToPay`, `EntryID` FROM
	((`galaEntries` INNER JOIN `members` ON `members`.`MemberID` =
	`galaEntries`.`MemberID`) INNER JOIN `galas` ON `galas`.`GalaID` =
	`galaEntries`.`GalaID`) WHERE `members`.`UserID` = ? AND `GalaDate` >=
	CURDATE() ORDER BY `GalaDate` ASC, `MForename` ASC, `MSurname` ASC';
  $query = $db->prepare($sql);
  $query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
} catch (Exception $e) {
  halt(500);
}

$galas = $query->fetchAll(PDO::FETCH_ASSOC);

$getCountNewMandates = $db->prepare("SELECT COUNT(*) FROM stripeMandates INNER JOIN stripeCustomers ON stripeMandates.Customer = stripeCustomers.CustomerID WHERE stripeCustomers.User = ? AND stripeMandates.MandateStatus != 'inactive';");
$getCountNewMandates->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
]);
$hasStripeMandate = $getCountNewMandates->fetchColumn() > 0;

$getLocations = $db->prepare("SELECT COUNT(*) FROM `covidLocations` WHERE `Tenant` = ?");
$getLocations->execute([
  $tenant->getId(),
]);
$showCovid = $getLocations->fetchColumn() > 0;

if ($showCovid && $tenant->getBooleanKey('HIDE_CONTACT_TRACING_FROM_PARENTS')) {
  // Hide covid banners
  $showCovid = false;

  // Show if this user is a squad rep
  $getRepCount = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ?");
  $getRepCount->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
  ]);
  $showCovid = $getRepCount->fetchColumn() > 0;
}

// Onboarding sessions
$getOnboarding = $db->prepare("SELECT `id` FROM `onboardingSessions` WHERE `user` = ? AND (`status` = ? || `status` = ?) ORDER BY `created` ASC");
$getOnboarding->execute([
  $user->getId(),
  'pending',
  'in_progress'
]);
$onboardingId = $getOnboarding->fetchColumn();

$username = htmlspecialchars(explode(" ", getUserName($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']))[0]);

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

    <?php if ($onboardingId) { ?>

      <aside class="row mb-4">
        <div class="col-lg-6">
          <div class="cell bg-tenant-brand tenant-colour">

            <h2 class="">You have tasks to complete</h2>

            <?php do {
              $onboarding = \SCDS\Onboarding\Session::retrieve($onboardingId);
            ?>
              <p>
                Complete your <?= htmlspecialchars($onboarding->type) ?> now
              </p>
              <p class="mb-0">
                <a href="<?= htmlspecialchars(autoUrl("onboarding/go-to-session?session=" . urlencode($onboarding->id))) ?>" class="btn btn-dark">
                  Start now <i class="fa fa-chevron-right" aria-hidden="true"></i>
                </a>
              </p>
            <?php } while ($onboardingId = $getOnboarding->fetchColumn()); ?>

          </div>
        </div>
      </aside>

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

    <?php if (!isSubscribed($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 'Notify')) { ?>
      <aside class="row mb-4">
        <div class="col-lg-6">
          <div class="cell bg-tenant-brand tenant-colour">
            <h2 class="mb-0">
              Emails from us
            </h2>

            <p>
              <strong>
                You're missing out on email updates from <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>
              </strong>
            </p>
            <p>
              Head to <a class="text-white" href="<?= autoUrl("my-account/email") ?>">My Account</a>
              to change your email preferences and stay up to date!
            </p>
          </div>
        </div>
      </aside>
    <?php } ?>

    <?php if (false && stripeDirectDebit() && !$hasStripeMandate) { ?>
      <div class="mb-4">
        <h2 class="mb-4">Want to set up a Direct Debit?</h2>
        <div class="news-grid">
          <a href="<?= autoUrl("payments/direct-debit/set-up") ?>">
            <span class="mb-3">
              <span class="title mb-0">
                Setup a Direct Debit Now (New System)
              </span>
            </span>
            <span class="category">
              Payments
            </span>
          </a>
          <a href="https://www.chesterlestreetasc.co.uk/support/directdebit/">
            <span class="mb-3">
              <span class="title mb-0">
                Learn more about Direct Debits
              </span>
            </span>
            <span class="category">
              Payments
            </span>
          </a>
        </div>
      </div>
    <?php } ?>

    <?php if (false && app()->tenant->getGoCardlessAccessToken() && !userHasMandates($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) { ?>
      <div class="mb-4">
        <h2 class="mb-4">Want to set up a Direct Debit?</h2>
        <div class="news-grid">
          <a href="<?= autoUrl("payments") ?>">
            <span class="mb-3">
              <span class="title mb-0">
                Setup a Direct Debit Now
              </span>
            </span>
            <span class="category">
              Payments
            </span>
          </a>
          <a href="https://www.chesterlestreetasc.co.uk/support/directdebit/">
            <span class="mb-3">
              <span class="title mb-0">
                Learn more about Direct Debits
              </span>
            </span>
            <span class="category">
              Payments
            </span>
          </a>
        </div>
      </div>
    <?php } ?>

    <div class="mb-4">
      <h2 class="mb-4">My Members</h2>
      <div class="news-grid">
        <?php
        if (sizeof($swimmers) > 0) {
          foreach ($swimmers as $s) { ?>
            <a href="<?= autoUrl("swimmers/" . $s->getId()) ?>">
              <span class="mb-3">
                <span class="title mb-0">
                  <?= htmlspecialchars($s->getFullName()) ?>
                </span>
                <span>
                  <?= $s->getAge() ?> years old
                </span>
              </span>
              <span class="category">
                <?php $squads = $s->getSquads();
                for ($i = 0; $i < min(sizeof($squads), 2); $i++) {
                  if ($i != 0) { ?>, <?php }
                                      ?><?= htmlspecialchars($squads[$i]->getName()) ?><?php
                                                                                      } ?>
              </span>
            </a>
          <?php }
        } else { ?>
          <p class="mb-0">You do not have any swimmers connected to your account</p>
        <?php } ?>
      </div>
    </div>

    <div class="mb-4">
      <h2 class="mb-4">My Gala Entries</h2>
      <div class="news-grid">
        <?php
        if (sizeof($galas) > 0) {
          foreach ($galas as $g) { ?>
            <a href="<?= autoUrl("galas/entries/" . $g['EntryID']) ?>">
              <span class="mb-3">
                <span class="title mb-0">
                  <?= htmlspecialchars(\SCDS\Formatting\Names::format($g['MForename'], $g['MSurname'])) ?>
                </span>
                <span>
                  &pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $g['FeeToPay']))->toScale(2)) ?>
                </span>
              </span>
              <span class="category">
                <?= htmlspecialchars($g['GalaName']) ?>
              </span>
            </a>
          <?php }
        } else { ?>
          <p class="mb-0">You have no current gala entries</p>
        <?php } ?>
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

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
