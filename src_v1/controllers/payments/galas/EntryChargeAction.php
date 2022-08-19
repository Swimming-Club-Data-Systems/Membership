<?php

if (!SCDS\CSRF::verify()) {
	halt(404);
}

if (!SCDS\FormIdempotency::verify()) {
	halt(404);
}

$db = app()->db;
$tenant = app()->tenant;

$disabled = "";

$date = date("Y-m-d");
$insertPayment = $db->prepare("INSERT INTO paymentsPending (`Date`, `Status`, UserID, `Name`, Amount, Currency, PMkey, `Type`, MetadataJSON) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$markAsCharged = $db->prepare("UPDATE galaEntries SET Charged = ?, PaymentID = ?, FeeToPay = ? WHERE EntryID = ?");
$notify = $db->prepare("INSERT INTO notify (UserID, `Status`, `Subject`, `Message`, EmailType) VALUES (?, ?, ?, ?, ?)");

$getMandates = $db->prepare("SELECT ID, Mandate, Last4, SortCode, `Address`, Reference, `URL`, `Status` FROM stripeMandates WHERE Customer = ? AND (`Status` = 'accepted' OR `Status` = 'pending') ORDER BY CreationTime DESC LIMIT 1");

$getGala = $db->prepare("SELECT GalaName `name`, GalaFee fee, GalaVenue venue, GalaFeeConstant fixed FROM galas WHERE GalaID = ? AND Tenant = ?");
$getGala->execute([
	$id,
	$tenant->getId()
]);
$gala = $getGala->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
	halt(404);
}

$galaData = new GalaPrices($db, $id);

$getEntries = $db->prepare("SELECT members.UserID `user`, 25Free, 50Free, 100Free, 200Free, 400Free, 800Free, 1500Free, 25Back, 50Back, 100Back, 200Back, 25Breast, 50Breast, 100Breast, 200Breast, 25Fly, 50Fly, 100Fly, 200Fly, 100IM, 150IM, 200IM, 400IM, MForename, MSurname, EntryID, Charged, FeeToPay, MandateID, members.UserID, GalaName, galaEntries.ProcessingFee FROM ((((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) LEFT JOIN users ON members.UserID = users.UserID) LEFT JOIN paymentPreferredMandate ON users.UserID = paymentPreferredMandate.UserID) WHERE galaEntries.GalaID = ? AND Charged = ? AND EntryProcessed = ? ORDER BY MForename ASC, MSurname ASC");
$getEntries->execute([$id, '0', '1']);

$swimsArray = [
	'25Free' => '25&nbsp;Free',
	'50Free' => '50&nbsp;Free',
	'100Free' => '100&nbsp;Free',
	'200Free' => '200&nbsp;Free',
	'400Free' => '400&nbsp;Free',
	'800Free' => '800&nbsp;Free',
	'1500Free' => '1500&nbsp;Free',
	'25Back' => '25&nbsp;Back',
	'50Back' => '50&nbsp;Back',
	'100Back' => '100&nbsp;Back',
	'200Back' => '200&nbsp;Back',
	'25Breast' => '25&nbsp;Breast',
	'50Breast' => '50&nbsp;Breast',
	'100Breast' => '100&nbsp;Breast',
	'200Breast' => '200&nbsp;Breast',
	'25Fly' => '25&nbsp;Fly',
	'50Fly' => '50&nbsp;Fly',
	'100Fly' => '100&nbsp;Fly',
	'200Fly' => '200&nbsp;Fly',
	'100IM' => '100&nbsp;IM',
	'150IM' => '150&nbsp;IM',
	'200IM' => '200&nbsp;IM',
	'400IM' => '400&nbsp;IM'
];

$rowArray = [1, null, null, null, null, null, 2, 1,  null, null, 2, 1, null, null, 2, 1, null, null, 2, 1, null, null, 2];
$rowArrayText = ["Freestyle", null, null, null, null, null, 2, "Backstroke",  null, null, 2, "Breaststroke", null, null, 2, "Butterfly", null, null, 2, "Individual Medley", null, null, 2];

while ($entry = $getEntries->fetch(PDO::FETCH_ASSOC)) {

	if ((string) $_POST[$entry['EntryID'] . '-amount'] != "") {
		$amountDec = \Brick\Math\BigDecimal::of((string) $_POST[$entry['EntryID'] . '-amount']);
		$amount = $amountDec->withPointMovedRight(2)->toInt();

		$hasNoGCDD = ($entry['MandateID'] == null) || (getUserOption($entry['user'], 'GalaDirectDebitOptOut'));
		$stripeCusomer = (new User($entry['user']))->getStripeCustomerID();
		if ($stripeCusomer) {
			$getMandates->execute([
				$stripeCusomer,
			]);
		}
		$mandate = $getMandates->fetch(PDO::FETCH_ASSOC);

		$hasNoSDD = !$mandate || (getUserOption($entry['user'], 'GalaDirectDebitOptOut'));

		$hasNoDD = ($hasNoSDD && $tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) || ($hasNoGCDD && !$tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT'));

		if ($amount > 0 && $amount <= 15000 && !$hasNoDD) {
			$count = 0;

			$swimsList = '<ul>';
			foreach ($swimsArray as $colTitle => $text) {
				if ($entry[$colTitle]) {
					$price = "";
					if ($galaData->getEvent($colTitle)->isEnabled()) {
						$price = ', <em>&pound;' . $galaData->getEvent($colTitle)->getPriceAsString() . '</em>';
					}
					$swimsList .= '<li>' . $text . $price . '</li>';
				}
			}
			$swimsList .= '</ul>';

			try {
				$db->beginTransaction();

				$amountString = (string) $amountDec->toScale(2);

				$name = $entry['MForename'] . ' ' . $entry['MSurname'] . '\'s Gala Entry into ' . $gala['name'] .  ' (Entry #' . $entry['EntryID'] . ')';

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
					mb_strimwidth('Gala Entry - ' . $entry['MForename'] . ' ' . $entry['MSurname'] . ' - ' . $entry['GalaName'] . ' - #' . $entry['EntryID'], 0, 495, '...'),
					$amount,
					'GBP',
					null,
					'Payment',
					$json
				]);

				$paymentId = $db->lastInsertId();

				$markAsCharged->execute([
					true,
					$paymentId,
					$amountString,
					$entry['EntryID']
				]);

				$message = '<p>We\'ve charged <strong>&pound;' . $amountString . '</strong> to your account for ' . htmlspecialchars($entry['MForename']) .  '\'s entry into ' . htmlspecialchars($gala['name']) . '.</p><p>You will be able to see this charge in your pending charges and from the first day of next month, on your bill statement. You\'ll be charged for this as part of your next direct debit payment to ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . '.</p>';

				$message .= '<p>You entered the following events;</p>';
				$message .= $swimsList;

				if ($entry['ProcessingFee'] > 0) {
					$message .= '<p>Your entry fee includes a processing fee of ' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($entry['ProcessingFee']), 'gbp')) . '.</p>';
				}

				$message .= '<p>Kind Regards<br> The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';

				$notify->execute([
					$entry['UserID'],
					'Queued',
					'Payments: ' . $entry['MForename'] .  '\'s ' . $gala['name'] . ' entry',
					$message,
					'Galas'
				]);

				$db->commit();
			} catch (Exception $e) {
				// A problem occured
				$db->rollBack();
				$_SESSION['TENANT-' . app()->tenant->getId()]['ChargeUsersFailure'] = true;
			}
		} else if ($amount > 15000) {
			$_SESSION['TENANT-' . app()->tenant->getId()]['OverhighChargeAmount'][$entry['EntryID']] = true;
		}
	}
}

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['ChargeUsersFailure'])) {
	$_SESSION['TENANT-' . app()->tenant->getId()]['ChargeUsersSuccess'] = true;
}
header("Location: " . autoUrl("payments/galas/" . $id));
