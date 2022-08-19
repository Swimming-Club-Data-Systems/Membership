<?php

// Confirmation - Main form
// If a reference was used, this is the only form the treasurer will need.

$date = new DateTime('now', new DateTimeZone('Europe/London'));

?>

<form action="<?=autoUrl("payments/confirmation/form-1")?>" method="post">

  <div class="row">
    <div class="col-6">
      <div class="mb-3">
        <label class="form-label" for="payment-ref">Reference</label>
        <input type="text" class="font-monospace form-control" id="payment-ref" name="payment-ref" aria-describedby="payment-ref-help" placeholder="Enter reference">
        <small id="payment-ref-help" class="form-text text-muted">
          Enter the reference shown on your bank statement
        </small>
      </div>
    </div>

    <div class="col-6">
      <div class="mb-3">
        <label class="form-label" for="payment-date">Date received</label>
        <input type="date" class="font-monospace form-control" id="payment-date" name="payment-date" aria-describedby="payment-date-help" value="<?=$date->format("Y-m-d")?>" placeholder="Use format YYYY-MM-DD, eg <?=$date->format("Y-m-d")?>">
        <small id="payment-date-help" class="form-text text-muted">
          Enter the date shown on your bank statement
        </small>
      </div>
    </div>
  </div>

  <p>
    <a class="" data-bs-toggle="collapse" href="#additional-fees-box" role="button" aria-expanded="false" aria-controls="additional-fees-box">
      Does your bank charge fees for deposits? Click here to add any fees relating to this payment.
    </a>
  </p>

  <div class="collapse" id="additional-fees-box">
    <div class="mb-3">
      <label class="form-label" for="payment-fees">Bank fees</label>
      <div class="input-group">
        <div class="input-group-text font-monospace">&pound;</div>
        <input type="number" class="font-monospace form-control" id="payment-fees" name="payment-fees" aria-describedby="payment-fees-help" value="0" placeholder="0" pattern="[0-9]*([\.,][0-9]*)?" min="0" step="0.01">
      </div>
      <small id="payment-fees-help" class="form-text text-muted">
        Enter fees incurred taking this payment
      </small>
    </div>
  </div>

  <p>
    <button class="btn btn-success" type="submit">
      Find payment
    </button>
  </p>

</form>