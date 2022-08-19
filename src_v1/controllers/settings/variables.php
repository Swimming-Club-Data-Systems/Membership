<?php

$fluidContainer = true;

$db = app()->db;

$vars = [
  'CLUB_NAME' => null,
  'CLUB_SHORT_NAME' => null,
  'ASA_CLUB_CODE' => null,
  'CLUB_EMAIL' => null,
  'CLUB_TRIAL_EMAIL' => null,
  'EMAIL_DOMAIN' => null,
  'CLUB_WEBSITE' => null,
  'SENDGRID_API_KEY' => null,
  'CLUB_ADDRESS' => null,
  'SYSTEM_COLOUR' => null,
  'ASA_DISTRICT' => null,
  'ASA_COUNTY' => null,
  'HIDE_MEMBER_ATTENDANCE' => null,
  'EMERGENCY_MESSAGE' => false,
  'EMERGENCY_MESSAGE_TYPE' => 'NONE',
  'GOCARDLESS_ACCESS_TOKEN' => null,
  'GOCARDLESS_WEBHOOK_KEY' => null,
  'SHOW_LOGO' => false,
  'LOGO_DIR' => null,
  'HIDE_CONTACT_TRACING_FROM_PARENTS' => false,
  'BLOCK_SQUAD_REPS_FROM_NOTIFY' => false,
  'REQUIRE_FULL_REGISTRATION' => true,
  'REQUIRE_FULL_RENEWAL' => true,
  'ENABLE_BILLING_SYSTEM' => true,
  'GLOBAL_PERSONAL_KEY' => null,
  'GLOBAL_PERSONAL_KEY_ID_NUMBER' => null,
  'REQUIRE_SQUAD_REP_FOR_APPROVAL' => true,
  'HIDE_MOVE_FEE_INFO' => false,
  'DISPLAY_NAME_FORMAT' => 'FL',
  'DEFAULT_GALA_PROCESSING_FEE' => 0,
];

$disabled = [];

foreach ($vars as $key => $value) {
  if (getenv($key)) {
    $vars[$key] = getenv($key);
    $disabled[$key] = ' disabled ';
  } else {
    $vars[$key] = app()->tenant->getKey($key);
    $disabled[$key] = '';
  }
}

if ($vars['CLUB_ADDRESS'] != null) {
  $vars['CLUB_ADDRESS'] = json_decode($vars['CLUB_ADDRESS']);
}

if ($vars['SYSTEM_COLOUR'] == null) {
  $vars['SYSTEM_COLOUR'] = '#007bff';
}

$districts = json_decode(file_get_contents(BASE_PATH . 'includes/regions/regions.json'), true);
$counties = json_decode(file_get_contents(BASE_PATH . 'includes/regions/counties.json'), true);

$clubs = [];

$clubs += ['0000' => [
  'Name' => 'Custom Club',
  'Code' => '0000',
  'District' => 'NONE',
  'County' => 'NONE',
  'MeetName' => 'NONE',
]];

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

$pagetitle = "System Variables";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('settings-vars');
      ?>
    </aside>
    <div class="col-md-9">
      <main>
        <h1>System Variables</h1>
        <p class="lead">Change your club name and other settings.</p>
        <p>If you cannot edit a variable, it is because it has been set as an environment variable at a server level. Contact your administrator if you need to change these.</p>
        <form method="post" class="needs-validation" novalidate>

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED']) && $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED']) { ?>
            <div class="alert alert-success">System variables saved.</div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED']);
          } ?>

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR']) && $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR']) { ?>
            <div class="alert alert-danger">Changes were not saved.</div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR']);
          } ?>

          <h2 id="emergency-message">Emergency Message</h2>
          <div class="row">
            <div class="col col-lg-8">
              <p>
                To help clubs during the coronavirus (COVID-19) outbreak, we've added an emergency message function. The emergency message will be shown in a banner at the top of every page.
              </p>
              <p>
                You can see an example of this banner in the image to the right.
              </p>
            </div>
            <div class="col col-lg-4">
              <img src="<?= htmlspecialchars(autoUrl("img/settings/emergency-message.png", false)) ?>" class="border" alt="Picture of membership software displaying emergency message">
            </div>
          </div>

          <div class="mb-3">
            <label>Message type</label>
            <div class="form-check">
              <input type="radio" value="NONE" id="EMERGENCY_MESSAGE_TYPE_NONE" name="EMERGENCY_MESSAGE_TYPE" class="form-check-input" <?php if ($vars['EMERGENCY_MESSAGE_TYPE'] == 'NONE') { ?>checked<?php } ?> <?= $disabled['EMERGENCY_MESSAGE_TYPE'] ?>>
              <label class="form-check-label" for="EMERGENCY_MESSAGE_TYPE_NONE">No emergency message</label>
            </div>
            <div class="form-check">
              <input type="radio" value="SUCCESS" id="EMERGENCY_MESSAGE_TYPE_SUCCESS" name="EMERGENCY_MESSAGE_TYPE" class="form-check-input" <?php if ($vars['EMERGENCY_MESSAGE_TYPE'] == 'SUCCESS') { ?>checked<?php } ?> <?= $disabled['EMERGENCY_MESSAGE_TYPE'] ?>>
              <label class="form-check-label" for="EMERGENCY_MESSAGE_TYPE_SUCCESS">Safe/good (green)</label>
            </div>
            <div class="form-check">
              <input type="radio" value="WARN" id="EMERGENCY_MESSAGE_TYPE_WARN" name="EMERGENCY_MESSAGE_TYPE" class="form-check-input" <?php if ($vars['EMERGENCY_MESSAGE_TYPE'] == 'WARN') { ?>checked<?php } ?> <?= $disabled['EMERGENCY_MESSAGE_TYPE'] ?>>
              <label class="form-check-label" for="EMERGENCY_MESSAGE_TYPE_WARN">Warning (yellow)</label>
            </div>
            <div class="form-check">
              <input type="radio" value="DANGER" id="EMERGENCY_MESSAGE_TYPE_DANGER" name="EMERGENCY_MESSAGE_TYPE" class="form-check-input" <?php if ($vars['EMERGENCY_MESSAGE_TYPE'] == 'DANGER') { ?>checked<?php } ?> <?= $disabled['EMERGENCY_MESSAGE_TYPE'] ?>>
              <label class="form-check-label" for="EMERGENCY_MESSAGE_TYPE_DANGER">Danger (red)</label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="EMERGENCY_MESSAGE">Emergency message</label>
            <textarea class="form-control font-monospace" rows="7" name="EMERGENCY_MESSAGE" id="EMERGENCY_MESSAGE" <?= $disabled['EMERGENCY_MESSAGE'] ?>><?= htmlspecialchars($vars['EMERGENCY_MESSAGE']) ?></textarea>
          </div>

          <p>
            <button type="submit" class="btn btn-success">
              Save changes
            </button>
          </p>

          <h2>Name and Code</h2>

          <div class="mb-3">
            <label class="form-label" for="CLUB_INFO">Select club</label>
            <select class="form-select" name="CLUB_INFO" id="CLUB_INFO">
              <?php foreach ($clubs as $club) { ?>
                <option <?php if ($club['Code'] == app()->tenant->getKey('ASA_CLUB_CODE')) { ?>selected<?php } ?> value="<?= htmlspecialchars($club['Code']) ?>">
                  <?= htmlspecialchars($club['Name']) ?> (<?= htmlspecialchars($club['Code']) ?>)
                </option>
              <?php } ?>
            </select>
            <small id="CLUB_ADDRESS_HELP" class="form-text text-muted">Selecting your club from this list lets us automatically fill out your clubs details including Swim England code, short name, county and district. We use your county and district to show useful links to your users. You can change your club name once you've selected it.</small>
          </div>

          <div class="mb-3">
            <label class="form-label" for="CLUB_NAME">Club Name</label>
            <input class="form-control" type="text" name="CLUB_NAME" id="CLUB_NAME" value="<?= htmlspecialchars($vars['CLUB_NAME']) ?>" <?= $disabled['CLUB_NAME'] ?>>
          </div>

          <div class="mb-3">
            <label class="form-label" for="CLUB_SHORT_NAME">Club Short Name</label>
            <input class="form-control" type="text" name="CLUB_SHORT_NAME" id="CLUB_SHORT_NAME" value="<?= htmlspecialchars($vars['CLUB_SHORT_NAME']) ?>" <?= $disabled['CLUB_SHORT_NAME'] ?>>
          </div>

          <div class="mb-3">
            <label class="form-label" for="ASA_CLUB_CODE">Swim England Club Code</label>
            <input class="form-control font-monospace" type="text" name="ASA_CLUB_CODE" id="CLUB_SHORT_NAME" value="<?= htmlspecialchars($vars['ASA_CLUB_CODE']) ?>" <?= $disabled['ASA_CLUB_CODE'] ?>>
          </div>

          <div class="mb-3">
            <label class="form-label" for="ASA_COUNTY">Swim England County</label>
            <input class="form-control" type="url" name="ASA_COUNTY" id="ASA_COUNTY" value="<?= htmlspecialchars($counties[$vars['ASA_COUNTY']]['name']) ?>" disabled>
          </div>

          <div class="mb-3">
            <label class="form-label" for="ASA_DISTRICT">Swim England District</label>
            <input class="form-control" type="url" name="ASA_DISTRICT" id="ASA_DISTRICT" value="<?= htmlspecialchars($districts[$vars['ASA_DISTRICT']]['name']) ?>" disabled>
          </div>

          <div class="mb-3">
            <label class="form-label" for="CLUB_WEBSITE">Club Website</label>
            <input class="form-control font-monospace" type="url" name="CLUB_WEBSITE" id="CLUB_WEBSITE" value="<?= htmlspecialchars($vars['CLUB_WEBSITE']) ?>" <?= $disabled['CLUB_WEBSITE'] ?>>
          </div>

          <div class="mb-3">
            <label class="form-label" for="CLUB_ADDRESS">Club Primary Address</label>
            <textarea class="form-control" rows="5" id="CLUB_ADDRESS" name="CLUB_ADDRESS" aria-describedby="CLUB_ADDRESS_HELP" <?= $disabled['CLUB_ADDRESS'] ?>><?php
                                                                                                                                                                if ($vars['CLUB_ADDRESS']) {
                                                                                                                                                                  for ($i = 0; $i < sizeof($vars['CLUB_ADDRESS']); $i++) {
                                                                                                                                                                    echo htmlspecialchars($vars['CLUB_ADDRESS'][$i]);
                                                                                                                                                                    if ($i + 1 < sizeof($vars['CLUB_ADDRESS'])) {
                                                                                                                                                                      echo "\r\n";
                                                                                                                                                                    }
                                                                                                                                                                  }
                                                                                                                                                                }
                                                                                                                                                                ?></textarea>
            <small id="CLUB_ADDRESS_HELP" class="form-text text-muted">Enter the address of your primary location. Do not include your club name and do not place commas at the end of lines.</small>
          </div>

          <h2>Email Options</h2>

          <div class="mb-3">
            <label class="form-label" for="CLUB_EMAIL">Main Club Email Address</label>
            <input class="form-control" type="email" name="CLUB_EMAIL" id="CLUB_EMAIL" value="<?= htmlspecialchars($vars['CLUB_EMAIL']) ?>" <?= $disabled['CLUB_EMAIL'] ?>>
          </div>

          <div class="mb-3">
            <label class="form-label" for="CLUB_TRIAL_EMAIL">Trial Request Email Address</label>
            <input class="form-control" type="email" name="CLUB_TRIAL_EMAIL" id="CLUB_TRIAL_EMAIL" value="<?= htmlspecialchars($vars['CLUB_TRIAL_EMAIL']) ?>" <?= $disabled['CLUB_TRIAL_EMAIL'] ?>>
          </div>

          <div class="mb-3">
            <label class="form-label" for="EMAIL_DOMAIN">Email Domain</label>
            <div class="input-group">
              <span class="input-group-text">@</span>
              <input class="form-control" type="text" name="EMAIL_DOMAIN" id="EMAIL_DOMAIN" value="<?= htmlspecialchars($vars['EMAIL_DOMAIN']) ?>" <?= $disabled['EMAIL_DOMAIN'] ?> aria-describedby="EMAIL_DOMAIN_HELP">
            </div>
            <small id="EMAIL_DOMAIN_HELP" class="form-text text-muted">The system sends emails to members under a subdomain of the above domain.</small>
          </div>

          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="BLOCK_SQUAD_REPS_FROM_NOTIFY" name="BLOCK_SQUAD_REPS_FROM_NOTIFY" <?php if (!bool($vars['BLOCK_SQUAD_REPS_FROM_NOTIFY'])) { ?>checked<?php } ?> <?= $disabled['BLOCK_SQUAD_REPS_FROM_NOTIFY'] ?>>
              <label class="form-check-label" for="BLOCK_SQUAD_REPS_FROM_NOTIFY">Allow squad reps to use Notify</label>
            </div>
          </div>

          <h2>Display options</h2>
          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="HIDE_MEMBER_ATTENDANCE" name="HIDE_MEMBER_ATTENDANCE" <?php if (!bool($vars['HIDE_MEMBER_ATTENDANCE'])) { ?>checked<?php } ?> <?= $disabled['HIDE_MEMBER_ATTENDANCE'] ?>>
              <label class="form-check-label" for="HIDE_MEMBER_ATTENDANCE">Show member attendance percentage to parents</label>
            </div>
          </div>

          <?php if ($vars['LOGO_DIR']) { ?>
            <div class="mb-3">
              <div class="form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="SHOW_LOGO" value="1" name="SHOW_LOGO" <?php if (bool($vars['SHOW_LOGO'])) { ?>checked<?php } ?> <?= $disabled['SHOW_LOGO'] ?>>
                <label class="form-check-label" for="SHOW_LOGO">Show organisation logo in header</label>
              </div>
            </div>
          <?php } ?>

          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="HIDE_CONTACT_TRACING_FROM_PARENTS" value="1" name="HIDE_CONTACT_TRACING_FROM_PARENTS" <?php if (!bool($vars['HIDE_CONTACT_TRACING_FROM_PARENTS'])) { ?>checked<?php } ?> <?= $disabled['HIDE_CONTACT_TRACING_FROM_PARENTS'] ?>>
              <label class="form-check-label" for="HIDE_CONTACT_TRACING_FROM_PARENTS">Show contact tracing links to all member users</label>
            </div>
          </div>

          <p class="mb-2">
            User and member display name settings
          </p>

          <div class="mb-3">
            <div class="form-check">
              <input type="radio" value="FL" id="DISPLAY_NAME_FORMAT_FL" name="DISPLAY_NAME_FORMAT" class="form-check-input" <?php if ($vars['DISPLAY_NAME_FORMAT'] == 'FL') { ?>checked<?php } ?> <?= $disabled['DISPLAY_NAME_FORMAT'] ?>>
              <label class="form-check-label" for="DISPLAY_NAME_FORMAT_FL">First Last</label>
            </div>
            <div class="form-check">
              <input type="radio" value="L,F" id="DISPLAY_NAME_FORMAT_LF" name="DISPLAY_NAME_FORMAT" class="form-check-input" <?php if ($vars['DISPLAY_NAME_FORMAT'] == 'L,F') { ?>checked<?php } ?> <?= $disabled['DISPLAY_NAME_FORMAT'] ?>>
              <label class="form-check-label" for="DISPLAY_NAME_FORMAT_LF">Last, First</label>
            </div>
          </div>

          <h2>Registration and renewal global options</h2>
          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="REQUIRE_FULL_REGISTRATION" value="1" name="REQUIRE_FULL_REGISTRATION" <?php if (bool($vars['REQUIRE_FULL_REGISTRATION'])) { ?>checked<?php } ?> <?= $disabled['REQUIRE_FULL_REGISTRATION'] ?>>
              <label class="form-check-label" for="REQUIRE_FULL_REGISTRATION">Require users to complete all registration forms</label>
            </div>
          </div>

          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="REQUIRE_FULL_RENEWAL" value="1" name="REQUIRE_FULL_RENEWAL" <?php if (bool($vars['REQUIRE_FULL_RENEWAL'])) { ?>checked<?php } ?> <?= $disabled['REQUIRE_FULL_RENEWAL'] ?>>
              <label class="form-check-label" for="REQUIRE_FULL_RENEWAL">Require users to complete all renewal forms. If off renewal jumps direct to membership fees page.</label>
            </div>
          </div>

          <h2>Gala options</h2>
          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="REQUIRE_SQUAD_REP_FOR_APPROVAL" value="1" name="REQUIRE_SQUAD_REP_FOR_APPROVAL" <?php if (bool($vars['REQUIRE_SQUAD_REP_FOR_APPROVAL'])) { ?>checked<?php } ?> <?= $disabled['REQUIRE_SQUAD_REP_FOR_APPROVAL'] ?>>
              <label class="form-check-label" for="REQUIRE_SQUAD_REP_FOR_APPROVAL">Only require entry approval for members in squads with squad reps</label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="DEFAULT_GALA_PROCESSING_FEE">Default per gala entry processing fee</label>
            <div class="input-group">
              <span class="input-group-text">£</span>
              <input class="form-control font-monospace" type="number" min="0" step="0.01" name="DEFAULT_GALA_PROCESSING_FEE" id="DEFAULT_GALA_PROCESSING_FEE" value="<?= htmlspecialchars(MoneyHelpers::intToDecimal($vars['DEFAULT_GALA_PROCESSING_FEE'])) ?>" <?= $disabled['DEFAULT_GALA_PROCESSING_FEE'] ?>>
            </div>
            <div class="small mt-1">To comply with the law on credit/debit card surcharges, you must charge this fee for any payment method you support - even cash or bank transfer</div>
          </div>

          <h2>Billing options</h2>
          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="ENABLE_BILLING_SYSTEM" value="1" name="ENABLE_BILLING_SYSTEM" <?php if (bool($vars['ENABLE_BILLING_SYSTEM'])) { ?>checked<?php } ?> <?= $disabled['ENABLE_BILLING_SYSTEM'] ?>>
              <label class="form-check-label" for="ENABLE_BILLING_SYSTEM">Enable the automated billing system (for Direct Debit)</label>
            </div>
          </div>

          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="HIDE_MOVE_FEE_INFO" value="1" name="HIDE_MOVE_FEE_INFO" <?php if (bool($vars['HIDE_MOVE_FEE_INFO'])) { ?>checked<?php } ?> <?= $disabled['HIDE_MOVE_FEE_INFO'] ?>>
              <label class="form-check-label" for="HIDE_MOVE_FEE_INFO">Hide squad fee information on squad move emails</label>
            </div>
          </div>

          <h2>GoCardless API keys (for legacy direct debit)</h2>

          <p>GoCardless is our legacy Direct Debit system. We're phasing it out and will replace it with Stripe, who we already partner with for card payments. We'll get in touch when you need to make changes.</a></p>

          <div class="mb-3">
            <label class="form-label" for="GOCARDLESS_ACCESS_TOKEN">GoCardless Access Token</label>
            <input class="form-control font-monospace" type="text" name="GOCARDLESS_ACCESS_TOKEN" id="GOCARDLESS_ACCESS_TOKEN" value="<?= htmlspecialchars($vars['GOCARDLESS_ACCESS_TOKEN']) ?>" <?= $disabled['GOCARDLESS_ACCESS_TOKEN'] ?>>
          </div>

          <div class="mb-3">
            <label class="form-label" for="GOCARDLESS_WEBHOOK_KEY">GoCardless Webhook Key</label>
            <input class="form-control font-monospace" type="text" name="GOCARDLESS_WEBHOOK_KEY" id="GOCARDLESS_WEBHOOK_KEY" value="<?= htmlspecialchars($vars['GOCARDLESS_WEBHOOK_KEY']) ?>" <?= $disabled['GOCARDLESS_WEBHOOK_KEY'] ?>>
          </div>

          <p>Your GoCardless webhook endpoint URL is</p>
          <div class="mb-3">
            <input class="form-control font-monospace" type="text" readonly value="<?= htmlspecialchars(webhookUrl("payments/webhooks")) ?>">
          </div>

          <h2>Stripe Setup</h2>

          <p>Connect to SCDS without API keys or webhooks in <a href="<?= htmlspecialchars(autoUrl("settings/stripe")) ?>">Stripe Settings</a>.</p>

          <p>
            We will soon also be moving direct debit payments to Stripe. We will be in touch when this transition starts.
          </p>

          <h2>Customisation</h2>

          <div class="mb-3">
            <label class="form-label" for="SYSTEM_COLOUR">Primary Colour</label>
            <input class="form-control" type="color" name="SYSTEM_COLOUR" id="SYSTEM_COLOUR" value="<?= htmlspecialchars($vars['SYSTEM_COLOUR']) ?>" <?= $disabled['SYSTEM_COLOUR'] ?> aria-describedby="SYSTEM_COLOUR_HELP">
            <small id="SYSTEM_COLOUR_HELP" class="form-text text-muted">The system primary colour is used in PDF documents generated by the membership system.</small>
          </div>

          <h2>Personal Key</h2>
          <p>
            We're adding some membership features which require a Personal Key to access the rankings. Someone from your club should provide their details here.
          </p>

          <p>
            Get your personal key from the <a href="https://www.swimmingresults.org/member_options/">swimmingresults.org</a> website.
          </p>

          <div class="mb-3">
            <label class="form-label" for="GLOBAL_PERSONAL_KEY_ID_NUMBER">Membership Number</label>
            <input class="form-control font-monospace" type="number" name="GLOBAL_PERSONAL_KEY_ID_NUMBER" id="GLOBAL_PERSONAL_KEY_ID_NUMBER" value="<?= htmlspecialchars($vars['GLOBAL_PERSONAL_KEY_ID_NUMBER']) ?>" <?= $disabled['GLOBAL_PERSONAL_KEY_ID_NUMBER'] ?> min="0">
            <div class="invalid-feedback">
              Please enter a Swim England, Swim Wales or Scottish Swimming membership number
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="GLOBAL_PERSONAL_KEY">Personal Key</label>
            <input class="form-control font-monospace" type="text" name="GLOBAL_PERSONAL_KEY" id="GLOBAL_PERSONAL_KEY" value="<?= htmlspecialchars($vars['GLOBAL_PERSONAL_KEY']) ?>" <?= $disabled['GLOBAL_PERSONAL_KEY'] ?> pattern="[a-f0-9]{8}-[a-f0-9]{8}-[a-f0-9]{8}-[a-f0-9]{8}">
            <div class="invalid-feedback">
              Please enter your personal key in the corrent format
            </div>
          </div>

          <p>
            <button class="btn btn-success" type="submit">
              Save
            </button>
          </p>
        </form>
      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
