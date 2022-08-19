<?php

if (isset($use_website_menu)) {
  define('USE_CLS_MENU', $use_website_menu && app()->tenant->isCLS());
}

if (!function_exists('chesterStandardMenu')) {
  function chesterStandardMenu()
  {

    $db = app()->db;
    $user = null;
    if (isset(app()->user)) {
      $user = app()->user;
    }
    $use_website_menu = false;
    if (defined('USE_CLS_MENU')) {
      $use_website_menu = USE_CLS_MENU;
    }
    global $allow_edit;
    global $exit_edit;
    global $edit_link;

    $canPayByCard = false;
    if (getenv('STRIPE') && app()->tenant->getStripeAccount() && app()->tenant->getBooleanKey('GALA_CARD_PAYMENTS_ALLOWED')) {
      $canPayByCard = true;
    }

    $haveSquadReps = false;
    $getRepCount = $db->prepare("SELECT COUNT(*) FROM squadReps INNER JOIN users ON squadReps.User = users.UserID WHERE users.Tenant = ?");
    $getRepCount->execute([
      app()->tenant->getId(),
    ]);
    if ($getRepCount->fetchColumn() > 0) {
      $haveSquadReps = true;
    }

    $hasNotifyAccess = false;
    if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
      $getNotify = $db->prepare("SELECT COUNT(*) FROM (SELECT User FROM squadReps UNION SELECT User FROM listSenders) AS T WHERE T.User = ?");
      $getNotify->execute([
        $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
      ]);
      if ($getNotify->fetchColumn()) {
        $hasNotifyAccess = true;
      }
    }

    $isTeamManager = false;
    if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
      $date = new DateTime('-1 day', new DateTimeZone('Europe/London'));
      $getGalas = $db->prepare("SELECT COUNT(*) FROM teamManagers INNER JOIN galas ON teamManagers.Gala = galas.GalaID WHERE teamManagers.User = ? AND galas.GalaDate >= ?");
      $getGalas->execute([
        $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
        $date->format("Y-m-d")
      ]);
      if ($getGalas->fetchColumn()) {
        $isTeamManager = true;
      }
    }

?>

    <?php if (!(isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) && user_needs_registration($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) && (!isset($use_website_menu) || !$use_website_menu)) { ?>
      <div class="collapse navbar-collapse offcanvas-collapse" id="chesterNavbar">
        <ul class="navbar-nav me-auto">
          <?php if (!empty($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) { ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= htmlspecialchars(autoUrl('')) ?>">Home</a>
            </li>
            <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") { ?>
              <?php
              $getSwimmers = $db->prepare("SELECT MForename Name, MSurname Surname, MemberID ID FROM `members` WHERE `UserID` = ? ORDER BY Name ASC, Surname ASC");
              $getSwimmers->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
              ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="swimmersDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  My Members
                </a>
                <div class="dropdown-menu" aria-labelledby="swimmersDropdown">
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("members")) ?>">Members Home</a>
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Membership Centre</a>
                  <?php if ($swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC)) { ?>
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">My members</h6>
                    <?php do { ?>
                      <a class="dropdown-item" href="<?= autoUrl("swimmers/" . $swimmer['ID']) ?>">
                        <?= htmlspecialchars(\SCDS\Formatting\Names::format($swimmer['Name'], $swimmer['Surname'])) ?>
                      </a>
                    <?php } while ($swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC)); ?>
                  <?php } else { ?>
                    <a class="dropdown-item" href="<?= autoUrl("my-account/add-member") ?>">Link a new member</a>
                  <?php } ?>
                </div>
              </li>
              <?php if (!$user->hasPermissions(['Admin', 'Coach', 'Committee'])) { ?>
                <li class="nav-item">
                  <a class="nav-link" href="<?= htmlspecialchars(autoUrl('timetable')) ?>">
                    Timetable
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= htmlspecialchars(autoUrl('timetable/booking')) ?>">
                    Booking
                  </a>
                </li>
              <?php } ?>
              <li class="nav-item">
                <a class="nav-link" href="<?= htmlspecialchars(autoUrl("log-books")) ?>">Log Books</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= autoUrl("emergency-contacts") ?>">Emergency Contacts</a>
              </li>
            <?php } else { ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="swimmerDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Members &amp; Squads
                </a>
                <div class="dropdown-menu" aria-labelledby="swimmerDropdown">
                  <h6 class="dropdown-header">Members</h6>
                  <a class="dropdown-item" href="<?= autoUrl("members") ?>">Member Directory</a>
                  <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") { ?>
                    <a class="dropdown-item" href="<?= autoUrl("members/new") ?>">Create New Member</a>
                    <a class="dropdown-item" href="<?= autoUrl("memberships/renewal") ?>">Membership Renewal</a>
                    <a class="dropdown-item" href="<?= autoUrl("members/orphaned") ?>" title="View active members who are not connected to a user account">Unconnected Members</a>
                    <?php if (app()->user->hasPermission('Admin')) { ?>
                      <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("qualifications")) ?>">Qualifications</a>
                    <?php } ?>
                  <?php } ?>
                  <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Galas") { ?>
                    <a class="dropdown-item" href="<?= autoUrl("members/access-keys") ?>">Access Keys</a>
                  <?php } ?>
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Membership Centre</a>
                  <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Galas") { ?>
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">Squads</h6>
                    <a class="dropdown-item" href="<?= autoUrl("squads") ?>">Squad Directory</a>
                    <a class="dropdown-item" href="<?= autoUrl("squads/moves") ?>">Squad Moves</a>
                  <?php } ?>
                  <a class="dropdown-item" href="<?= autoUrl("squad-reps") ?>">Squad Reps</a>
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("log-books")) ?>">Log Books</a>
                </div>
              </li>
            <?php } ?>

            <?php if ($user->hasPermissions(['Admin', 'Coach', 'Committee'])) { ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="swimmerDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Registers
                </a>
                <div class="dropdown-menu" aria-labelledby="registerDropdown">
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("attendance")) ?>">Attendance Home</a>
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("attendance/register")) ?>">Take Register</a>
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl('timetable')) ?>">Timetable</a>
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl('timetable/booking')) ?>">Bookings</a>
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("attendance/sessions")) ?>">Manage Squad Sessions</a>
                  <?php if ($user->hasPermissions(['Admin', 'Committee'])) { ?>
                    <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("attendance/venues")) ?>">Manage Venues</a>
                  <?php } ?>
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("attendance/history")) ?>">Attendance History</a>
                </div>
              </li>
            <?php } ?>

            <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Parent") { ?>
              <?php if (
                $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin" ||
                $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Galas"
              ) { ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="usersMenu" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Users
                  </a>
                  <div class="dropdown-menu" aria-labelledby="usersMenu">
                    <a class="dropdown-item" href="<?= autoUrl("users") ?>">
                      User Directory
                    </a>
                    <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("onboarding")) ?>">
                      Create New User (Member Onboarding)
                    </a>
                    <a class="dropdown-item" href="<?= autoUrl("users/add") ?>">
                      Create New User (admin, coach, volunteer)
                    </a>
                    <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
                      <a class="dropdown-item" href="<?= autoUrl("payments/user-mandates") ?>">
                        User Direct Debit Mandates
                      </a>
                    <?php } ?>
                  </div>
                </li>
              <?php } ?>
              <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Galas") { ?>
                <li class="nav-item">
                  <a class="nav-link" href="<?= autoUrl("payments") ?>">Pay</a>
                </li>
              <?php } ?>
              <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") { ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="paymentsAdminDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Pay
                  </a>
                  <div class="dropdown-menu" aria-labelledby="paymentsAdminDropdown">
                    <a class="dropdown-item" href="<?= autoUrl("payments") ?>">Payments Home</a>
                    <a class="dropdown-item" href="<?= autoUrl("payments/history") ?>">Payment Status</a>
                    <!-- New feature coming soon -->
                    <!-- <a class="dropdown-item" href="<?= autoUrl("payments/confirmation") ?>">Payment Confirmation</a> -->
                    <a class="dropdown-item" href="<?= autoUrl("payments/extrafees") ?>">Extra Fees</a>
                    <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin' || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Galas') { ?>
                      <a class="dropdown-item" href="<?= autoUrl("payments/galas") ?>">
                        Charge or refund gala entries
                      </a>
                    <?php } ?>
                    <?php $date = new DateTime('now', new DateTimeZone('Europe/London')); ?>
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header"><?= htmlspecialchars($date->format('F Y')) ?></h6>
                    <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("payments/history/squads/" . $date->format("Y/m"))) ?>">
                      Squad Fees
                    </a>
                    <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("payments/history/extras/" . $date->format("Y/m"))) ?>">
                      Extra Fees
                    </a>
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">Service Provider Accounts</h6>
                    <a class="dropdown-item" href="https://manage.gocardless.com" target="_blank">
                      GoCardless Dashboard
                    </a>
                    <a class="dropdown-item" href="https://dashboard.stripe.com/" target="_blank">
                      Stripe Dashboard
                    </a>
                    <?php if (getenv('STRIPE') && app()->tenant->getStripeAccount()) { ?>
                      <div class="dropdown-divider"></div>
                      <h6 class="dropdown-header">Payment Cards</h6>
                      <a class="dropdown-item" href="<?= autoUrl("payments/cards") ?>">
                        Credit and Debit Cards
                      </a>
                      <a class="dropdown-item" href="<?= autoUrl("payments/card-transactions") ?>">
                        Card Transactions
                      </a>
                      <a class="dropdown-item" href="<?= autoUrl("payments/cards/add") ?>">
                        Add a Credit or Debit Card
                      </a>
                    <?php } ?>
                  </div>
                </li>
              <?php } ?>
              <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Coach" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Galas") { ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="notifyDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Notify
                  </a>
                  <div class="dropdown-menu" aria-labelledby="notifyDropdown">
                    <a class="dropdown-item" href="<?= autoUrl("notify") ?>">Notify Home</a>
                    <a class="dropdown-item" href="<?= autoUrl("notify/new") ?>">New Message</a>
                    <a class="dropdown-item" href="<?= autoUrl("notify/lists") ?>">Targeted Lists</a>
                    <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") { ?>
                      <a class="dropdown-item" href="<?= autoUrl("notify/sms") ?>">SMS Lists</a>
                    <?php } ?>
                    <a class="dropdown-item" href="<?= autoUrl("notify/history") ?>">Previous Messages</a>
                  </div>
                </li>
              <?php } ?>
            <?php } ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="galaDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Galas
              </a>
              <div class="dropdown-menu" aria-labelledby="galaDropdown">
                <a class="dropdown-item" href="<?= autoUrl("galas") ?>">
                  Gala Home
                </a>
                <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") { ?>
                  <a class="dropdown-item" href="<?= autoUrl("galas/entergala") ?>">
                    Enter a Gala
                  </a>
                  <a class="dropdown-item" href="<?= autoUrl("galas/entries") ?>">
                    My Entries
                  </a>
                  <?php if ($canPayByCard) { ?>
                    <a class="dropdown-item" href="<?= autoUrl("galas/pay-for-entries") ?>">
                      Pay For Entries
                    </a>
                  <?php } ?>
                  <?php if ($isTeamManager) { ?>
                    <a class="dropdown-item" href="<?= autoUrl("team-managers") ?>">
                      Team Manager Dashboard
                    </a>
                  <?php } ?>
                <?php } else { ?>
                  <a class="dropdown-item" href="<?= autoUrl("galas/addgala") ?>">Add Gala</a>
                  <a class="dropdown-item" href="<?= autoUrl("galas/entries") ?>">View Entries</a>
                  <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin' || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Galas') { ?>
                    <a class="dropdown-item" href="<?= autoUrl("payments/galas") ?>">
                      Charge or Refund Entries
                    </a>
                  <?php } ?>
                  <a class="dropdown-item" href="<?= autoUrl("team-managers") ?>">
                    Team Manager Dashboard
                  </a>
                <?php } ?>
                <?php if (app()->tenant->isCLS()) { ?>
                  <a class="dropdown-item" href="https://www.chesterlestreetasc.co.uk/competitions/" target="_blank">Gala website <i class="fa fa-external-link"></i></a>
                  <a class="dropdown-item" href="https://www.chesterlestreetasc.co.uk/competitions/category/galas/" target="_blank">Upcoming galas <i class="fa fa-external-link"></i></a>
                  <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") { ?>
                    <a class="dropdown-item" href="https://www.chesterlestreetasc.co.uk/competitions/enteracompetition/guidance/" target="_blank">Help with entries <i class="fa fa-external-link"></i></a>
                  <?php } ?>
                <?php } ?>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("time-converter")) ?>">
                  Time Converter
                </a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("galas/all-galas")) ?>">
                  Gala Directory
                </a>
              </div>
            </li>
            <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $haveSquadReps) { ?>
              <li class="nav-item">
                <a class="nav-link" href="<?= autoUrl("squad-reps") ?>">
                  Squad Reps
                </a>
              </li>
            <?php } ?>
            <?php if ($hasNotifyAccess) { ?>
              <li class="nav-item">
                <a class="nav-link" href="<?= autoUrl("notify") ?>">
                  Notify
                </a>
              </li>
            <?php } ?>
            <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent" && app()->tenant->getKey('ASA_CLUB_CODE') != 'UOSZ') {
              $hasMandate = userHasMandates($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']);
              $getCountNewMandates = $db->prepare("SELECT COUNT(*) FROM stripeMandates INNER JOIN stripeCustomers ON stripeMandates.Customer = stripeCustomers.CustomerID WHERE stripeCustomers.User = ? AND stripeMandates.MandateStatus != 'inactive';");
              $getCountNewMandates->execute([
                $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
              ]);
              $hasStripeMandate = $getCountNewMandates->fetchColumn() > 0;
            ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="paymentsParentDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Pay
                </a>
                <div class="dropdown-menu" aria-labelledby="paymentsParentDropdown">
                  <h6 class="dropdown-header">Direct Debit</h6>
                  <a class="dropdown-item" href="<?= autoUrl("payments") ?>">Payments Home</a>
                  <a class="dropdown-item" href="<?= autoUrl("payments/statements") ?>">My Billing History</a>
                  <?php if (app()->tenant->getGoCardlessAccessToken() && $hasMandate && !stripeDirectDebit(true)) { ?>
                    <a class="dropdown-item" href="<?= autoUrl("payments/mandates") ?>">My Bank Account (Old System)</a>
                  <?php } else if (app()->tenant->getGoCardlessAccessToken() && !stripeDirectDebit(true)) { ?>
                    <a class="dropdown-item" href="<?= autoUrl("payments/setup") ?>">
                      Setup a direct debit (Old System)
                    </a>
                  <?php } ?>
                  <?php if (stripeDirectDebit() && $hasStripeMandate) { ?>
                    <a class="dropdown-item" href="<?= autoUrl("payments/direct-debit") ?>">My Bank Account (New System)</a>
                  <?php } else if (stripeDirectDebit()) { ?>
                    <a class="dropdown-item" href="<?= autoUrl("payments/direct-debit/set-up") ?>">
                      Setup a direct debit (New System)
                    </a>
                  <?php } ?>
                  <a class="dropdown-item" href="<?= autoUrl("payments/statements/latest") ?>">My Latest Statement</a>
                  <a class="dropdown-item" href="<?= autoUrl("payments/fees") ?>">My Fees Since Last Bill</a>
                  <div class="dropdown-divider"></div>
                  <h6 class="dropdown-header">Fee Information</h6>
                  <a class="dropdown-item" href="<?= autoUrl("payments/squad-fees") ?>">
                    Squad and Extra Fees
                  </a>
                  <a class="dropdown-item" href="<?= autoUrl("payments/membership-fees") ?>">
                    Annual Membership Fees
                  </a>
                  <?php if (getenv('STRIPE') && app()->tenant->getStripeAccount()) { ?>
                    <?php if (app()->tenant->getGoCardlessAccessToken()) { ?>
                      <div class="dropdown-divider"></div>
                    <?php } ?>
                    <h6 class="dropdown-header">Card Payments</h6>
                    <a class="dropdown-item" href="<?= autoUrl("payments/cards") ?>">
                      Credit and Debit Cards
                    </a>
                    <a class="dropdown-item" href="<?= autoUrl("payments/card-transactions") ?>">
                      Card Transactions
                    </a>
                    <a class="dropdown-item" href="<?= autoUrl("payments/cards/add") ?>">
                      Add a Credit or Debit Card
                    </a>
                  <?php } ?>
                </div>
              </li>
            <?php } ?>
            <?php if (isset(app()->user) && app()->user->hasPermission('Admin')) { ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="postDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Pages
                </a>
                <div class="dropdown-menu" aria-labelledby="postDropdown">
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl('pages')) ?>">Home</a>
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl('pages/new')) ?>">New Page</a>
                </div>
              </li>
              <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
                <li class="nav-item">
                  <a class="nav-link" href="<?= autoUrl("admin") ?>" title="Adminstrative tools">
                    Admin
                  </a>
                </li>
              <?php } ?>
            <?php } ?>

            <li class="nav-item">
              <a class="nav-link" href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a>
            </li>

            <?php if (app()->tenant->getKey('CLUB_WEBSITE')) { ?>
              <li class="nav-item d-lg-none">
                <a class="nav-link" href="<?= htmlspecialchars(app()->tenant->getKey('CLUB_WEBSITE')) ?>" target="_blank">Club Website <i class="fa fa-external-link" aria-hidden="true"></i></a>
              </li>
            <?php } ?>

          <?php } ?>
          <?php if (empty($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) { ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= htmlspecialchars(autoUrl("login")) ?>">Login</a>
            </li>
            <?php if (isset(app()->tenant) && app()->tenant->getKey('ASA_CLUB_CODE') == 'UOSZ' && false) { ?>
              <li class="nav-item">
                <a class="nav-link" href="<?= htmlspecialchars(autoUrl("register/university-of-sheffield")) ?>">Sign Up (Trials)</a>
              </li>
            <?php } ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= htmlspecialchars(autoUrl("timetable")) ?>">Timetable</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= htmlspecialchars(autoUrl("timeconverter")) ?>">Time Converter</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= htmlspecialchars(autoUrl("log-books")) ?>">Log Books</a>
            </li>
            <?php if (app()->tenant->getKey('CLUB_WEBSITE')) { ?>
              <li class="nav-item d-lg-none">
                <a class="nav-link" href="<?= htmlspecialchars(app()->tenant->getKey('CLUB_WEBSITE')) ?>" target="_blank">Club Website <i class="fa fa-external-link" aria-hidden="true"></i></a>
              </li>
            <?php } ?>
          <?php } ?>
        </ul>
        <?php if (!empty($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) {
          $currentUser = app()->user;
          $user_name = preg_replace("/( +)/", '&nbsp;', htmlspecialchars($currentUser->getFirstName())); ?>
          <ul class="navbar-nav">
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <?= $user_name ?> <i class="fa fa-user-circle-o" aria-hidden="true"></i>
              </a>
              <div class="dropdown-menu dropdown-menu-end">
                <span class="dropdown-item-text">Signed&nbsp;in&nbsp;as&nbsp;<strong><?= $user_name ?></strong></span>
                <div class="dropdown-divider"></div>
                <?php $perms = $currentUser->getPrintPermissions();
                if (sizeof($perms) > 1) { ?>
                  <h6 class="dropdown-header">Switch account mode</h6>
                  <?php foreach ($perms as $perm => $name) { ?>
                    <a class="dropdown-item" href="<?= autoUrl("account-switch?type=" . urlencode($perm)) ?>"><?= htmlspecialchars($name) ?><?php if ($perm == $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) { ?> <i class="text-primary fa fa-check-circle fa-fw" aria-hidden="true"></i><?php } ?></a>
                  <?php } ?>
                  <div class="dropdown-divider"></div>
                <?php } ?>
                <h6 class="dropdown-header">Account settings</h6>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("my-account")) ?>">
                  Your Profile
                </a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("my-account/email")) ?>">
                  Your Email Options
                </a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("my-account/address")) ?>">
                  Your Address
                </a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("my-account/general")) ?>">
                  Your General Options
                </a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("emergency-contacts")) ?>">
                  Your Emergency Contacts
                </a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("my-account/passkeys")) ?>">
                  Your Passkeys
                </a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("my-account/password")) ?>">
                  Your Password
                </a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("my-account/notify-history")) ?>">
                  Your Message History
                </a>
                <?php if ($user->hasPermission('Parent')) { ?>
                  <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("account-switch?type=Parent&redirect=" . urlencode(autoUrl("my-account/add-member")))) ?>">
                    Add Member
                  </a>
                <?php } ?>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("my-account/login-history")) ?>">
                  Your Login History
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" target="_blank" href="<?= htmlspecialchars(platformUrl('help-and-support')) ?>">Help</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= autoUrl("logout") ?>">Logout</a>
              </div>
            </li>
          </ul>
        <?php } ?>
      </div>
      </nav>
    <?php } ?>

<?php }
}

chesterStandardMenu();
