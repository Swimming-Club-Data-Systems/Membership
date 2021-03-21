<?php

$pagetitle = "Membership Software by Swimming Club Data Systems";

include BASE_PATH . "views/root/header.php";

?>

<div class="container">
  <div class="row">
    <div class="col-lg-8">
      <h1>Membership management software for swimming clubs</h1>
      <p class="lead">Manage your members, subscriptions, competition entries and more.</p>
      <p>
        For sales enquiries, please contact <a href="mailto:sales@myswimmingclub.uk">sales@myswimmingclub.uk</a>.
      </p>
    </div>
  </div>
</div>

<div class="bg-primary text-light py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h2>Members and squads</h2>
        <p class="lead">Manage your squads and members.</p>

        <ul class="mb-0">
          <li>Manage your member's and their personal details</li>
          <li>Assign members to multiple squads</li>
          <li>Track personal best times</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="bg-light text-dark py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h2>Member communications</h2>
        <p class="lead">Contact your members and parents easily.</p>

        <ul class="mb-0">
          <li>Contact parents quickly and easily by email</li>
          <li>Create custom groups for email messages</li>
          <li>Get replies to emails you send</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="bg-primary text-light py-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-9">
        <h2>Automated payments</h2>
        <p class="lead">Collect payments by Direct Debit and one-off payments by card.</p>

        <ul class="mb-0">
          <li>Take payments easily and securely</li>
          <li>A white-label experience for card payments</li>
          <li>An optional white-label experience for direct-debit payments</li>
          <li>Automated billing - no need to set up plans for each family or member</li>
          <li>Stripe Partner</li>
        </ul>

        <div class="mb-5 d-md-none"></div>
      </div>

      <div class="col">
        <div class="row align-items-center">
          <div class="col">
            <a href="https://stripe.com/gb" title="Stripe" target="_blank"><img class="img-fluid" src="<?= htmlspecialchars(autoUrl('public/img/stripe/stripe-white.svg')) ?>" alt="Stripe Logo"></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="bg-light text-dark py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h2>Online gala entries</h2>
        <p class="lead">Your members can enter their competitions online.</p>

        <ul class="mb-0">
          <li>Fast and secure paperless gala entries</li>
          <li>Various entry methods</li>
          <li>Squad rep features</li>
          <li>Simple secure payment by card or on account</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="bg-primary text-light py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h2>Online registers</h2>
        <p class="lead">Take your registers online and keep your coaches up to date.</p>

        <ul class="mb-0">
          <li>Up to date attendance information</li>
          <li>Attendance monitoring</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="bg-light text-dark py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h2>Paperless registration and renewal</h2>
        <p class="lead">Banish paper and make registration and renewal easy.</p>

        <ul class="mb-0">
          <li>Members securely review data</li>
          <li>No need to rewrite forms</li>
          <li>Secure payment for membership fees</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="bg-primary text-light py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h2>Much more</h2>
        <p class="lead">Online photo permissions, medical forms and more.</p>

        <ul class="mb-0">
          <li>Custom photo permissions can be updated at any time</li>
          <li>Online medical forms for members</li>
          <li>Medical and photography information reported to coaches on registers</li>
          <li>Printable backup forms</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="bg-light text-dark py-5 mb-n3">
  <div class="container">
    <div class="row">
      <div class="col">
        <h2>Used by clubs across the North East</h2>
        <p class="lead mb-4">Feature development is driven by the needs of our clubs.</p>

        <div class="row align-items-center club-logos">
          <div class="col-md">
            <a href="https://www.rdasc.org.uk/" target="_blank">
              <img src="<?= autoUrl("public/img/customer-clubs/rice.png") ?>" title="Richmond Dales ASC" alt="Richmond Dales ASC" class="img-fluid mx-auto d-block">
            </a>
            <div class="mb-3 d-md-none"></div>
          </div>
          <div class="col-md">
            <a href="https://www.newcastleswimteam.co.uk/" target="_blank">
              <img src="<?= autoUrl("public/img/customer-clubs/newe.png") ?>" title="Newcastle Swim Team" alt="Newcastle Swim Team" class="img-fluid mx-auto d-block">
            </a>
            <div class="mb-3 d-md-none"></div>
          </div>
          <div class="col-md">
            <a href="https://www.darlingtonasc.co.uk/" target="_blank">
              <img src="<?= autoUrl("public/img/customer-clubs/dare.png") ?>" title="Darlington ASC" alt="Darlington ASC" class="img-fluid mx-auto d-block">
            </a>
            <div class="mb-3 d-md-none"></div>
          </div>
          <div class="col-md">
            <a href="https://main.nasc.co.uk/" target="_blank">
              <img src="<?= autoUrl("public/img/customer-clubs/nore.png") ?>" title="Northallerton ASC" alt="Northallerton ASC" class="img-fluid mx-auto d-block">
            </a>
            <div class="mb-3 d-md-none"></div>
          </div>
          <div class="col-md">
            <a href="https://www.chesterlestreetasc.co.uk/" target="_blank">
              <img src="<?= autoUrl("public/img/chesterLogo.svg") ?>" title="Chester-le-Street ASC" alt="Chester-le-Street ASC" class="img-fluid mx-auto d-block">
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\RootFooter();
$footer->render(); ?>