<?php

$db = app()->db;

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$pagetitle = 'Direct Debit Instruction - Onboarding';

$good = $hasGoCardless = false;

if (isset($_SESSION['SetupMandateSuccess'])) {
  $good = true;
}

$ddi = null;
if (app()->tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP')) {
  // Get DD details
  // Get mandates
  $getMandates = $db->prepare("SELECT ID, Mandate, Last4, SortCode, `Address`, Reference, `URL`, `Status` FROM stripeMandates WHERE Customer = ? AND (`Status` = 'accepted' OR `Status` = 'pending') ORDER BY CreationTime DESC");
  $getMandates->execute([
    $user->getStripeCustomer()->id,
  ]);
  $mandate = $getMandates->fetch(PDO::FETCH_ASSOC);

  $ddi = null;
  if ($mandate) {
    $ddi = new stdClass();
    $good = true;
    $ddi->last4 = $mandate['Last4'];
    $ddi->sortCode = implode("-", str_split($mandate['SortCode'], 2));
  }
} else if ($tenant->getGoCardlessAccessToken()) {
  $good = userHasMandates($user->getId());
  $hasGoCardless = $good;
}

include BASE_PATH . "views/head.php";

?>

<div class="min-vh-100 mb-n3 overflow-auto">
  <div class="bg-light">
    <div class="container">
      <div class="row justify-content-center py-5">
        <div class="col-lg-8 col-md-10">

          <?php if ($logos) { ?>
            <img src="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75.png')) ?>" srcset="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@2x.png')) ?> 2x, <?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@3x.png')) ?> 3x" alt="" class="img-fluid d-block mx-auto">
          <?php } else { ?>
            <img src="<?= htmlspecialchars(autoUrl('public/img/corporate/scds.png')) ?>" height="75" width="75" alt="" class="img-fluid d-block mx-auto">
          <?php } ?>

        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row justify-content-center py-5">
      <div class="col-lg-8 col-md-10">
        <h1 class="text-center">Set up a Direct Debit Instruction</h1>

        <p class="lead mb-5 text-center">
          Your club has asked to you set up a Direct Debit for your monthly payments.
        </p>

        <form method="post" class="needs-validation" novalidate>

          <?php if (isset($_SESSION['FormError']) && $_SESSION['FormError']) { ?>
            <div class="alert alert-danger">
              <p class="mb-0">
                <strong>An error occurred when we tried to save the changes</strong>
              </p>
            </div>
          <?php unset($_SESSION['FormError']);
          } ?>

          <?php if (isset($_SESSION['SetupMandateSuccess'])) { ?>
            <div class="alert alert-success">
              <p class="mb-0">
                <strong>We've started setting up your direct debit instruction</strong>
              </p>
              <p class="mb-0">
                Account <?= htmlspecialchars($_SESSION['SetupMandateSuccess']['SortCode']) ?>, <?= htmlspecialchars($_SESSION['SetupMandateSuccess']['Last4']) ?>
              </p>
            </div>
          <?php } ?>

          <?php if ($ddi) { ?>
            <div class="card card-body mb-3">
              <p class="mb-0">
                <strong>You already have a Direct Debit Instruction set up</strong>
              </p>
              <p class="">
                Account <?= htmlspecialchars($ddi->sortCode) ?>, ****<?= htmlspecialchars($ddi->last4) ?>
              </p>

              <p class="mb-0">
                You can make changes to your Direct Debit Instruction later in the <em>Pay</em> section of your account.
              </p>
            </div>
          <?php } ?>

          <?php if ($hasGoCardless) { ?>
            <div class="card card-body mb-3">
              <p class="mb-0">
                <strong>You already have a Direct Debit Instruction set up</strong>
              </p>
              <p class="">
                Details are available in your account.
              </p>

              <p class="mb-0">
                You can make changes to your Direct Debit Instruction later in the <em>Pay</em> section of your account.
              </p>
            </div>
          <?php } ?>

          <?php if (getenv('STRIPE') && app()->tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP') && !$good) { ?>
            <!-- STRIPE -->
            <p>
              <a href="<?= htmlspecialchars(autoUrl('onboarding/go/direct-debit/stripe/set-up')) ?>" class="btn btn-success">Set up now</a>
            </p>

            <p>
              We will redirect you to our payment provider, Stripe to securely set up your Direct Debit Instruction.
            </p>
          <?php } else if ($tenant->getGoCardlessAccessToken() && !$good) { ?>
            <!-- GOCARDLESS -->
            <p>
              <a href="<?= htmlspecialchars(autoUrl('onboarding/go/direct-debit/go-cardless/set-up')) ?>" class="btn btn-success">Set up now</a>
            </p>

            <p>
              We will redirect you to our payment provider, GoCardless to securely set up your Direct Debit Instruction.
            </p>
          <?php } else { ?>
            <!-- NONE -->
          <?php } ?>

          <?php if ($good) { ?>
            <p>
              <button type="submit" class="btn btn-success">Confirm</button>
            </p>
          <?php } ?>

          <?php if ($tenant->getBooleanKey('ALLOW_DIRECT_DEBIT_OPT_OUT') && !($hasGoCardless || $ddi)) { ?>
            <p>
              <button type="submit" class="btn btn-dark">
                Proceed without setting up a Direct Debit Instruction
              </button>
            </p>
          <?php } else if (!($hasGoCardless || $ddi)) { ?>
            <p>
              If, for any reason you are unable to set up a Direct Debit, please speak to a member of club staff. They have the discretion to skip this task.
            </p>
          <?php } ?>

        </form>
      </div>
    </div>
  </div>
</div>

<?php

if (isset($_SESSION['SetupMandateSuccess'])) {
  unset($_SESSION['SetupMandateSuccess']);
}

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();

?>