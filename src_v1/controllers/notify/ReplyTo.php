<?php

$pagetitle = 'Your Reply-To Address';

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("notify"))?>">Notify</a></li>
      <li class="breadcrumb-item active" aria-current="page">Reply address</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Your Reply-To Address</h1>
      <p class="lead">You can set your reply-to address here.</p>
      <p>Your reply-to address is where replies will to emails sent using notify will be sent if you select <strong>My reply-to email address</strong> when sending.</p>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['SetReplySuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['SetReplySuccess']) { ?>
        <div class="alert alert-success">
          <p class="mb-0"><strong>We've saved your reply to email</strong></p>
          <p class="mb-0">If your email address is not valid, it may mean your emails are not sent.</p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['SetReplySuccess']); } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['SetReplyFalse']) && $_SESSION['TENANT-' . app()->tenant->getId()]['SetReplyFalse']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0"><strong>Your email address was not valid</strong></p>
          <p class="mb-0">Please try again.</p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['SetReplyFalse']); } ?>

      <form method="post">
        <div class="mb-3">
          <label class="form-label" for="reply">Reply-To email address</label>
          <input type="email" class="form-control" id="reply" name="reply" placeholder="Enter email" value="<?=htmlspecialchars(getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 'NotifyReplyAddress'))?>">
        </div>

        <p>
          <button class="btn btn-primary" type="submit">
            Save
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();