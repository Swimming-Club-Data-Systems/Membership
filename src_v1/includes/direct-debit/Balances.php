<?php

/**
 * getAccountBalance gets the account balance of a user.
 * An account balance consists of unpaid fees
 *
 * @param int $user the id of the user
 * @return int account balance
 */
function getAccountBalance($user) {
  global $db;

  $getBalance = $db->prepare("SELECT SUM(paymentsPending.Amount) FROM paymentsPending LEFT JOIN payments ON paymentsPending.PMkey = payments.PMkey WHERE paymentsPending.UserID = ? AND (paymentsPending.Status = 'Pending' OR paymentsPending.Status = 'Queued' OR payments.Status = 'pending_api_request' OR payments.Status = 'pending_customer_approval' OR payments.Status = 'pending_submission' OR payments.Status = 'submitted' OR payments.Status = 'processing' OR payments.Status = 'requires_payment_method' OR payments.Status = 'requires_action' OR payments.Status = 'requires_confirmation') AND paymentsPending.Type = ?");
  $getBalance->execute([
    $user,
    'Payment'
  ]);
  $balance = $getBalance->fetchColumn();

  $getBalance->execute([
    $user,
    'Refund'
  ]);
  $credits = $getBalance->fetchColumn();
  
  return ((int) $balance - (int) $credits);
}