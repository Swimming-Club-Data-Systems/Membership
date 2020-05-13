<?php

$db = app()->db;

$disabled = "";

$date = date("Y-m-d");
$insertPayment = $db->prepare("INSERT INTO paymentsPending (`Date`, `Status`, UserID, `Name`, Amount, Currency, PMkey, `Type`, MetadataJSON) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$markAsRefunded = $db->prepare("UPDATE galaEntries SET Refunded = ?, AmountRefunded = ? WHERE EntryID = ?");
$notify = $db->prepare("INSERT INTO notify (UserID, `Status`, `Subject`, `Message`, EmailType) VALUES (?, ?, ?, ?, ?)");
$setRefundAmount = $db->prepare("UPDATE stripePayments SET `AmountRefunded` = ? WHERE `Intent` = ?");

$getGala = $db->prepare("SELECT GalaName `name`, GalaFee fee, GalaVenue venue, GalaFeeConstant fixed FROM galas WHERE GalaID = ?");
$getGala->execute([$id]);
$gala = $getGala->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
	halt(404);
}

$getEntries = $db->prepare("SELECT members.UserID `user`, 50Free, 100Free, 200Free, 400Free, 800Free, 1500Free, 50Back, 100Back, 200Back, 50Breast, 100Breast, 200Breast, 50Fly, 100Fly, 200Fly, 100IM, 150IM, 200IM, 400IM, MForename, MSurname, EntryID, Charged, FeeToPay, MandateID, EntryProcessed Processed, Refunded, galaEntries.AmountRefunded, Intent, users.UserID FROM (((((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) LEFT JOIN users ON members.UserID = users.UserID) LEFT JOIN paymentPreferredMandate ON users.UserID = paymentPreferredMandate.UserID) LEFT JOIN stripePayments ON galaEntries.StripePayment = stripePayments.ID) WHERE galaEntries.GalaID = ? AND Charged = ? AND EntryProcessed = ? ORDER BY MForename ASC, MSurname ASC");
$getEntries->execute([$id, '1', '1']);

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

while ($entry = $getEntries->fetch(PDO::FETCH_ASSOC)) {
	$amount = (int) ($_POST[$entry['EntryID'] . '-refund']*100);
	$toPay = (int) ($entry['FeeToPay']*100);
	$amountRefundable = $toPay - ($entry['AmountRefunded']);

	if ($amount > 0 && $amount <= $amountRefundable) {
		$hasNoDD = ($entry['MandateID'] == null) || (getUserOption($entry['user'], 'GalaDirectDebitOptOut'));
		$count = 0;
		
		$swimsList = '<ul>';
		foreach($swimsArray as $colTitle => $text) {
			if ($entry[$colTitle]) {
				$swimsList .= '<li>' . $text . '</li>';
			}
		}
		$swimsList .= '</ul>';

		try {
			$db->beginTransaction();

			$amountString = number_format($_POST[$entry['EntryID'] . '-refund'], 2);
			$totalString = number_format(($amount/100) + ($entry['AmountRefunded']/100), 2);

			if (!$hasNoDD && $entry['Intent'] == null) {
				// Refund via direct debit bills
				$name = 'REJECTIONS REFUND ' . $entry['MForename'] . ' ' . $entry['MSurname'] . '\'s Gala Entry into ' . $gala['name'] .  ' (Entry #' . $entry['EntryID'] . ')';

				$jsonArray = [
					"Name" => $name,
					"type" => [
						"object" => 'GalaEntry',
						"id" => $id,
						"name" => $gala['name']
					]
				];
				$json = json_encode($jsonArray);

				$insertPayment->execute([
					$date,
					'Pending',
					$entry['UserID'],
					'Gala Entry (#' . $entry['EntryID'] . ')',
					$amount,
					'GBP',
					null,
					'Refund',
					$json
				]);
			} else if ($entry['Intent'] != null && app()->tenant->getKey('STRIPE')) {
				// Refund to card used
				\Stripe\Stripe::setApiKey(app()->tenant->getKey('STRIPE'));
				$intent = \Stripe\PaymentIntent::retrieve($entry['Intent']);
				$intent->charges->data[0]->refund(['amount' => $amount]);

				// Update amount refunded on payment
				if (isset($intent->charges->data[0]->amount_refunded)) {
					$setRefundAmount->execute([
						$intent->charges->data[0]->amount_refunded,
						$intent->id,
					]);
				}
			}

			$markAsRefunded->execute([
				true,
				$amount + $entry['AmountRefunded'],
				$entry['EntryID']
			]);

			$message = '<p>We\'ve issued a refund for ' . htmlspecialchars($entry['MForename']) .  '\'s entry into ' . htmlspecialchars($gala['name']) . '.</p>';

			$message .= '<p>This refund is to the value of <strong>&pound;' . $amountString . '</strong>.</p>';

			if ($amount + $entry['AmountRefunded'] > $entry['AmountRefunded']) {
				$message .= '<p>Please note that this brings the total amount refunded for this gala to &pound;' . $totalString . '</p>';
			}

			if ($entry['MandateID'] != null && !getUserOption($entry['user'], 'GalaDirectDebitOptOut') && $entry['Intent'] == null) {
				$message .= '<p>This refund has been applied as a credit to your club account. This means you will either;</p>';
				$message .= '<ul><li>If you have not paid the bill by direct debit for this gala yet, you will automatically charged the correct amount for ' . $gala['name'] . ' on your next bill as reductions will be applied automatically</li><li>If you have already paid the bill by direct debit for this gala, the credit applied to your account will give you a discount on next month\'s bill</li></ul>';
			} else if ($entry['Intent'] != null) {
				$message .= '<p>We\'ve refunded this payment to your original payment card.</p>';
			} else {
				$message .= '<p>As you don\'t pay your club fees by direct debit or have opted out of paying for galas by direct debit, you\'ll need to collect this refund from the treasurer or gala coordinator.</p>';
			}

			$message .= '<p>Kind Regards<br> The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';

			$notify->execute([
				$entry['UserID'],
				'Queued',
				'Refund for Rejections: ' . $entry['MForename'] .  '\'s ' . $gala['name'] . ' Entry',
				$message,
				'Galas'
			]);

			$db->commit();
		} catch (Exception $e) {
			// A problem occured
			$db->rollBack();
			$_SESSION['TENANT-' . app()->tenant->getId()]['ChargeUsersFailure'] = true;
		}
	} else if ($amount > $amountRefundable) {
		$_SESSION['TENANT-' . app()->tenant->getId()]['OverhighChargeAmount'][$entry['EntryID']] = true;
	}
}

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['ChargeUsersFailure'])) {
	$_SESSION['TENANT-' . app()->tenant->getId()]['ChargeUsersSuccess'] = true;
}

header("Location: " . currentUrl());