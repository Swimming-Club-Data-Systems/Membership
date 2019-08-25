<?php

function handleCompletedGalaPayments($paymentIntent, $onSession = false) {
  \Stripe\Stripe::setApiKey(env('STRIPE'));
  $swimsArray = [
    '50Free' => '50 Free',
    '100Free' => '100 Free',
    '200Free' => '200 Free',
    '400Free' => '400 Free',
    '800Free' => '800 Free',
    '1500Free' => '1500 Free',
    '50Back' => '50 Back',
    '100Back' => '100 Back',
    '200Back' => '200 Back',
    '50Breast' => '50 Breast',
    '100Breast' => '100 Breast',
    '200Breast' => '200 Breast',
    '50Fly' => '50 Fly',
    '100Fly' => '100 Fly',
    '200Fly' => '200 Fly',
    '100IM' => '100 IM',
    '150IM' => '150 IM',
    '200IM' => '200 IM',
    '400IM' => '400 IM'
  ];

  global $db;

  $reuse = 1;
  /*
  if (isset($_POST['reuse-card']) && bool($reuse)) {
    $reuse = 1;
  }
  */

  $intent = \Stripe\PaymentIntent::retrieve($paymentIntent);

  $getId = $db->prepare("SELECT ID FROM stripePayments WHERE Intent = ?");
  $getId->execute([
    $intent->id
  ]);
  $databaseId = $getId->fetchColumn();

  if ($databaseId == null) {
    halt(404);
  }

  // If on session, go to success page
  // Webhook handles fulfillment
  if ($onSession && $intent->status == 'succeeded') {
    $_SESSION['CompletedEntryInfo'] = $databaseId;
    unset($_SESSION['GalaPaymentIntent']);
    unset($_SESSION['PaidEntries']);
    unset($_SESSION['GalaPaymentMethodID']);
    unset($_SESSION['AddNewCard']);

    $_SESSION['GalaPaymentSuccess'] = true;

    header("Location: " . autoUrl("galas/pay-for-entries/success"));
    return true;
  } else if ($onSession && $intent->status != 'succeeded') {
    header("Location: " . autoUrl("galas/pay-for-entries/checkout"));
    return false;
  }

  // Get the user
  $getUser = $db->prepare("SELECT `User` FROM stripePayments WHERE Intent = ?");
  $getUser->execute([
    $intent->id
  ]);
  $userId = $getUser->fetchColumn();
  if ($userId == null) {
    $userId = $_SESSION['UserID'];
  }

  if (isset($intent->charges->data[0]->payment_method_details->card->wallet)) {
    $reuse = 0;
  }

  $cardCount = 0;
  $customerId = null;

  $method = null;
  $pm = null;

  $newMethod = true;
  try {
    if (isset($intent->payment_method)) {
      $getMethodCount = $db->prepare("SELECT COUNT(*) FROM stripePayMethods WHERE MethodID = ?");
      $getMethodCount->execute([
        $intent->payment_method
      ]);
      if ($getMethodCount->fetchColumn() > 0) {
        $newMethod = false;
      }
    }
  } catch (Exception $e) {
    // Something is really wrong so stop
    halt(500);
  }

  $getUserEmail = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile FROM users WHERE UserID = ?");
  $getUserEmail->execute([$userId]);
  $user = $getUserEmail->fetch(PDO::FETCH_ASSOC);

  if (isset($newMethod) && $newMethod) {
    // Add payment intent

    $checkIfCustomer = $db->prepare("SELECT COUNT(*) FROM stripeCustomers WHERE User = ?");
    $checkIfCustomer->execute([$userId]);

    $customer = null;
    try {
      if ($checkIfCustomer->fetchColumn() == 0) {
        // Create a Customer:
        $customer = \Stripe\Customer::create([
          'payment_method' => $intent->payment_method,
          "name" => $user['Forename'] . ' ' . $user['Surname'],
          "description" => "Customer for " . $userId . ' (' . $user['EmailAddress'] . ')',
          'email' => $user['EmailAddress'],
          'phone' => $user['Mobile']
        ]);

        // YOUR CODE: Save the customer ID and other info in a database for later.
        $id = $customer->id;
        $addCustomer = $db->prepare("INSERT INTO stripeCustomers (User, CustomerID) VALUES (?, ?)");
        $addCustomer->execute([
          $userId,
          $id
        ]);
      } else {
        $getCustID = $db->prepare("SELECT CustomerID FROM stripeCustomers WHERE User = ?");
        $getCustID->execute([$userId]);
        $customer = \Stripe\Customer::retrieve($getCustID->fetchColumn());

        // Check whether we should update user details
        if ($customer->name != $user['Forename'] . ' ' . $user['Surname'] || $customer->email != $user['EmailAddress'] || $customer->phone != $user['Mobile']) {
          // Some details are not the same so let's update the stripe customer
          $customer = \Stripe\Customer::update(
            $customer->id,
            [
              "name" => $user['Forename'] . ' ' . $user['Surname'],
              'email' => $user['EmailAddress'],
              'phone' => $user['Mobile']
            ]
          );

        }
      }

      $method = $intent->payment_method;
      $pm = \Stripe\PaymentMethod::retrieve($method);

      $customerId = $customer->id;

      if (!isset($pm->customer) || $pm->customer != $customerId) {
        $pm->attach(['customer' => $customerId]);
      }
    
      $name = "Unnamed Card";
    
      // Get the payment method details
      $id = $pm->id;
      $nameOnCard = $pm->card->name;
      $city = $pm->billing_details->address->city;
      $country = $pm->billing_details->address->country;
      $line1 = $pm->billing_details->address->line1;
      $line2 = $pm->billing_details->address->line2;
      $postal_code = $pm->billing_details->address->postal_code;
      $brand = $pm->card->brand;
      $issueCountry = $pm->card->country;
      $expMonth = $pm->card->exp_month;
      $expYear = $pm->card->exp_year;
      $funding = $pm->card->funding;
      $last4 = $pm->card->last4;
      $threeDSecure = $pm->card->three_d_secure_usage->supported;

      $getCardCount = $db->prepare("SELECT COUNT(*) FROM stripePayMethods WHERE Customer = ? AND Fingerprint = ? AND Reusable = ?");
      $getCardCount->execute([
        $customer->id,
        $pm->card->fingerprint,
        1
      ]);

      $cardCount = $getCardCount->fetchColumn();

      if ($cardCount == 0) {  
        $addPaymentDetails = $db->prepare("INSERT INTO stripePayMethods (Customer, MethodID, `Name`, CardName, City, Country, Line1, Line2, PostCode, Brand, IssueCountry, ExpMonth, ExpYear, Funding, Last4, Fingerprint, Reusable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $addPaymentDetails->execute([
          $customer->id,
          $id,
          $name,
          $nameOnCard,
          $city,
          $country,
          $line1,
          $line2,
          $postal_code,
          $brand,
          $issueCountry,
          $expMonth,
          $expYear,
          $funding,
          $last4,
          $pm->card->fingerprint,
          $reuse
        ]);
      }
    } catch (Exception $e) {
      //pre($e);
      $body = $e->getJsonBody();
      $err  = $body['error']['message'];
      if ($onSession) {
        $_SESSION['PayCardError'] = true;
        $_SESSION['PayCardErrorMessage'] = $err;
        header("Location: " . autoUrl("galas/pay-for-entries/checkout"));
      } else {
        reportError($e);
      }
      return;
    }
  }

  if ($intent->status == 'succeeded') {
    $db->beginTransaction();

    $updateEntries = $db->prepare("UPDATE galaEntries SET Charged = ? WHERE StripePayment = ?");
    $addToStripePayments = $db->prepare("UPDATE stripePayments SET Method = ?, Amount = ?, Currency = ?, Paid = ?, AmountRefunded = ? WHERE Intent = ?");
    $addPaymentItems = $db->prepare("INSERT INTO stripePaymentItems (Payment, `Name`, `Description`, Amount, Currency, AmountRefunded) VALUES (?, ?, ?, ?, ?, ?)");
    $getEntries = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE StripePayment = ?");

    $getPaymentMethod = $db->prepare("SELECT ID FROM stripePayMethods WHERE MethodID = ?");
    $getPaymentMethod->execute([$intent->payment_method]);
    $paymentMethodId = $getPaymentMethod->fetchColumn();
    if ($paymentMethodId == null) {
      if ($cardCount > 0) {
        $getCardFromOtherDetails = $db->prepare("SELECT ID FROM stripePayMethods WHERE Customer = ? AND Fingerprint = ? AND Reusable = ?");
        $getCardFromOtherDetails->execute([
          $customerId,
          $pm->card->fingerprint,
          1
        ]);
        $paymentMethodId = $getCardFromOtherDetails->fetchColumn();
        if ($paymentMethodId == null) {
          halt(404);
        }
      }
    }

    $date = new DateTime('@' . $intent->created, new DateTimeZone('UTC'));

    try {
      $addToStripePayments->execute([
        $paymentMethodId,
        $intent->amount,
        $intent->currency,
        true,
        0,
        $intent->id
      ]);

      $updateEntries->execute([
        true,
        $databaseId
      ]);

      $getEntries->execute([$databaseId]);

      while ($entry = $getEntries->fetch(PDO::FETCH_ASSOC)) {
        $addPaymentItems->execute([
          $databaseId,
          'Gala entry',
          'Gala entry number ' . $entry['EntryID'],
          (int) $entry['FeeToPay']*100,
          $intent->currency,
          0
        ]);
      }

      if ($pm == null) {
        $pm = \Stripe\PaymentMethod::retrieve($intent->payment_method);
      }

      if ($onSession) {
        $_SESSION['CompletedEntryInfo'] = $databaseId;
      }

      $message = "<p>Your payment receipt for gala entries.</p>";

      $getEntries->execute([$databaseId]);

      while ($entry = $getEntries->fetch(PDO::FETCH_ASSOC)) {
        $count = 0;
        $message .= '<p>' . htmlspecialchars($entry['MForename'] . ' ' . $entry['MSurname']) . ' for ' . htmlspecialchars($entry['GalaName']) . '</p><ul>';
        foreach($swimsArray as $colTitle => $text) {
          if ($entry[$colTitle]) {
            $count++;
            $message .= '<li>' . $text . '</li>';
          }
        }
        $message .= '</ul>';
      }
      
      if ($pm != null && isset($pm->card)) {
        $message .= '<p>Paid with ' . getCardBrand($pm->card->brand) . ' ' . $pm->card->funding . ' &middot;&middot;&middot;&middot; ' . $pm->card->last4 . '.</p>';
      }
      $message .= '<p>Total paid £' . number_format($intent->amount/100, 2) . '. Payment reference #' . $databaseId . '.</p>';

      $emailDb = $db->prepare("INSERT INTO notify (`UserID`, `Status`, `Subject`, `Message`, `ForceSend`, `EmailType`) VALUES (?, ?, ?, ?, 1, 'Payments')");

      if (isset($intent->charges->data[0]->billing_details->email)) {
        $email = $intent->charges->data[0]->billing_details->email;
        $name = $user['Forename'] . ' ' . $user['Surname'];
        if (isset($intent->charges->data[0]->billing_details->name)) {
          $name = $intent->charges->data[0]->billing_details->name;
        }
        $message .= '<p>This receipt has been sent to the email address indicated by the payment service you used, which may not be your club account email address.</p>';
        $sendingEmail = null;
        if (bool(env('IS_CLS'))) {
          $sendingEmail = "payments@" . env('EMAIL_DOMAIN');
        } else {
          $sendingEmail = mb_strtolower(trim(env('ASA_CLUB_CODE'))) . "-payments@" . env('EMAIL_DOMAIN');
        }
        notifySend(null, 'Payment Receipt', $message, $name, $email, [
          "Email" => $sendingEmail,
          "Name" => env('CLUB_NAME'),
          "Unsub" => [
            "Allowed" => false,
            "User" => $userId,
            "List" =>	"Payments"
          ]
        ]);
        $emailDb->execute([
          $userId,
          'Sent',
          'Payment Receipt',
          $message
        ]);
      } else {
        $emailDb->execute([
          $userId,
          'Queued',
          'Payment Receipt',
          $message
        ]);
      }

      $db->commit();

      if ($onSession) {
        unset($_SESSION['GalaPaymentIntent']);
        unset($_SESSION['PaidEntries']);
        unset($_SESSION['GalaPaymentMethodID']);
        unset($_SESSION['AddNewCard']);

        $_SESSION['GalaPaymentSuccess'] = true;

        header("Location: " . autoUrl("galas/pay-for-entries/success"));
      } else {
        return true;
      }
    } catch (Exception $e) {
      pre($e);
      $db->rollBack();
    }
  } else {
    if ($onSession) {
      header("Location: " . autoUrl("galas/pay-for-entries/checkout"));
    } else {
      return false;
    }
  }
}