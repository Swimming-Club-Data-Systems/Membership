<?php

// Confirmation - Main form
// If a reference was used, this is the only form the treasurer will need.

$date = new DateTime('now', new DateTimeZone('Europe/London'));

?>

<form action="<?=autoUrl("payments/confirmation/form-2")?>" method="post">

  <div class="row">
    <div class="col-6">
      <div class="mb-3">
        <label class="form-label" for="payment-amount">Amount</label>
        <div class="input-group">
          <div class="input-group-text font-monospace">&pound;</div>
          <input type="number" class="font-monospace form-control" id="payment-amount" name="payment-amount" aria-describedby="payment-amount-help" placeholder="0" pattern="[0-9]*([\.,][0-9]*)?" min="0" step="0.01">
        </div>
        <small id="payment-amount-help" class="form-text text-muted">
          The amount shown on your bank statement
        </small>
      </div>
    </div>

    <div class="col-6">
      <div class="mb-3">
        <label class="form-label" for="payment-name">Surname</label>
        <input type="text" class="font-monospace form-control" id="payment-name" name="payment-name" aria-describedby="payment-name-help" placeholder="eg <?=htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['Surname'])?>">
        <small id="payment-date-help" class="form-text text-muted">
          Enter the surname of the name shown on your bank statement*
        </small>
      </div>
    </div>
  </div>

  <p>
    <button class="btn btn-success" type="submit">
      Find payment
    </button>
  </p>

  <p>
    * Surnames are more likely to match if the bank account is in the name of the spouse/partner of the club account holder.
  </p>

</form>