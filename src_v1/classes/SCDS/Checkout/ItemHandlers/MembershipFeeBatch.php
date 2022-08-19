<?php

namespace SCDS\Checkout\ItemHandlers;

class MembershipFeeBatch
{

  public static function paid($item, $stripePayment, $intentId)
  {

    $db = app()->db;
    $tenant = app()->tenant;
    \Stripe\Stripe::setApiKey(getenv('STRIPE'));

    // Get batch item
    $getBatchItem = $db->prepare("SELECT membershipBatchItems.ID, membershipBatchItems.Batch, membershipBatchItems.Membership, membershipBatchItems.Member, membershipBatchItems.Amount, membershipBatchItems.Notes, members.MForename, members.MSurname, clubMembershipClasses.Name, membershipBatchItems.Year, membershipYear.StartDate, membershipYear.EndDate FROM ((((membershipBatchItems INNER JOIN members ON members.MemberID = membershipBatchItems.Member) INNER JOIN clubMembershipClasses ON clubMembershipClasses.ID = membershipBatchItems.Membership) INNER JOIN membershipBatch ON membershipBatch.ID = membershipBatchItems.Batch) INNER JOIN membershipYear ON membershipYear.ID = membershipBatchItems.Year) WHERE membershipBatchItems.ID = ?");
    $getBatchItem->execute([
      $item->attributes->id,
    ]);
    $batchItem = $getBatchItem->fetch(\PDO::FETCH_OBJ);

    // Get info about the Stripe payment and method
    $intent = \Stripe\PaymentIntent::retrieve(
      [
        'id' => $intentId,
        'expand' => ['customer', 'payment_method', 'charges.data.balance_transaction'],
      ],
      [
        'stripe_account' => $tenant->getStripeAccount(),
      ]
    );

    $paymentInfo = json_encode([
      'type' => 'stripe',
      'data' =>
      [
        'intent_id' => $stripePayment,
        'method_id' => $intent->payment_method->id,
        'payment_intent' => $intent,
      ]
    ]);

    // Update the batch to say it is paid
    $updateBatch = $db->prepare("UPDATE membershipBatch SET Completed = ?, PaymentDetails = ? WHERE ID = ?");
    $updateBatch->execute([
      (int) true,
      $paymentInfo,
      $batchItem->Batch,
    ]);

    // Check if the batch relates to am onboarding session
    try {
      $get = $db->prepare("SELECT id FROM onboardingSessions WHERE batch = ?");
      $get->execute([
        $batchItem->Batch
      ]);
      $batch = $get->fetchColumn();

      if ($batch) {
        $session = \SCDS\Onboarding\Session::retrieve($batch);
        $session->completeTask('fees');
      }
    } catch (\Exception $e) {
      reportError($e);
    }

    $addPaymentItems = $db->prepare("INSERT INTO stripePaymentItems (Payment, `Name`, `Description`, Amount, Currency, AmountRefunded) VALUES (?, ?, ?, ?, ?, ?)");
    $addPaymentItems->execute([
      $stripePayment,
      'Membership fees',
      $batchItem->MForename . ' ' . $batchItem->MSurname . ' - ' . $batchItem->Name,
      $item->amount,
      $item->currency,
      0,
    ]);

    $time = new \DateTime('now', new \DateTimeZone('UTC'));

    // Add membership record!
    $addMembership = $db->prepare("INSERT INTO `memberships` (`Member`, `Year`, `Membership`, `Amount`, `StartDate`, `EndDate`, `Purchased`, `PaymentInfo`, `Notes`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);");
    $addMembership->execute([
      $batchItem->Member,
      $batchItem->Year,
      $batchItem->Membership,
      $batchItem->Amount,
      $batchItem->StartDate,
      $batchItem->EndDate,
      $time->format('Y-m-d H:i:s'),
      $paymentInfo,
      $batchItem->Notes,
    ]);
  }
}
