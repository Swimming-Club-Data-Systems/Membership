<?php

$pagetitle = "Entering a gala";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("galas"))?>">Galas</a></li>
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("galas/entergala"))?>">Enter gala</a></li>
      <li class="breadcrumb-item active" aria-current="page">Help</li>
    </ol>
  </nav>
  <?php } ?>

  <div class="row">
    <div class="col-lg-8">
      <h1>Entering a gala</h1>
      <p class="lead">We’re happy to now be able to let you enter competitions online. The new system asks you the important information which we need to know in order to make an entry and then submits it for you.</p>

      <p>Please be aware that the system does not support open water events. For these competitions your club will let you know the entry procedure.</p>

      <ul>
        <li>The name of the swimmer</li>
        <li>The name of the gala</li>
        <li>Select the events you would like to enter</li>
        <li><em>and you may be asked to supply times for each event if the gala is run using the HyTek system.</em></li>
      </ul>

      <p>You will need to fill out the form for each swimmer you are entering – You are unable to make multiple entries in one go.</p>

      <h2>Your Confirmation Email</h2>

      <p>You will automatically receive an email from us listing all of the details that you provided and the events you have entered. Please be aware that this email is only for verification that information submitted was correct and for your piece of mind that we have received your entry.</p>

      <p>This confirmation email is not however proof that your child has been entered into a gala or that your child has been accepted into a gala by the club hosting it. It is up to you to check the accepted entries when they are published. Please retain the email for your records.</p>

      <h2>Amending an Entry</h2>

      <p>If you need to amend an entry, log into your account again. You can see all your entries, and edit them if they have either not been processed, paid for, or the closing date has not passed.</p>

      <h2>Paying for entries</h2>

      <p>Supported payment methods vary by club. Follow your own club's guidance on payment methods.</p>

      <p>Galas can be paid for by credit or debit card, as part of your next monthly direct debit payment or if you tell the treasurer or a squad rep, cash, cheque or bank transfer.</p>

      <p>If your club supports card payments, you'll see options to pay by card after making an entry or in the <strong>Galas</strong> menu. Simply follow the onscreen instructions to make a payment. If you’re making more than one entry, wait until after you’ve made all entries to pay by card as this helps save us money on transaction fees.</p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();