<?php

$avoidDirectDebit = app()->tenant->getBooleanKey('ALLOW_DIRECT_DEBIT_OPT_OUT') && isset($_POST['avoid-dd']) && bool($_POST['avoid-dd']);

$paymentItems = [];

$db = app()->db;


$location = autoUrl("");

$db->beginTransaction();

try {
	$date = new \DateTime('now', new DateTimeZone('UTC'));
	$asaDate = $clubDate = $date->format("Y-m-d");

	if (getenv('CUSTOM_SCDS_CLUB_CHARGE_DATE') && $renewal != 0) {
		$date = new \DateTime(getenv('CUSTOM_SCDS_CLUB_CHARGE_DATE'), new DateTimeZone('UTC'));
		$clubDate = $date->format("Y-m-d");
	}

	if (getenv('CUSTOM_SCDS_ASA_CHARGE_DATE') && $renewal != 0) {
		$date = new \DateTime(getenv('CUSTOM_SCDS_ASA_CHARGE_DATE'), new DateTimeZone('UTC'));
		$asaDate = $date->format("Y-m-d");
	}

	$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
	$partial_reg = isPartialRegistration();

	$partial_reg_require_topup = false;
	if ($partial_reg) {
		$db = app()->db;
		$sql = "SELECT COUNT(*) FROM `members` WHERE UserID = ? AND RR = 0 AND ClubPays = 0";
		$query = $db->prepare($sql);
		$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
		if ($query->fetchColumn() == 1) {
			$partial_reg_require_topup = true;
		}
	}

	$month = (new DateTime('now', new DateTimeZone('Europe/London')))->format('m');

	$discounts = json_decode(app()->tenant->getKey('MembershipDiscounts'), true);
	$clubDiscount = $swimEnglandDiscount = 0;
	if ($discounts != null && isset($discounts['CLUB'][$month])) {
		$clubDiscount = $discounts['CLUB'][$month];
	}
	if ($discounts != null && isset($discounts['ASA'][$month])) {
		$swimEnglandDiscount = $discounts['ASA'][$month];
	}

	$sql = $db->prepare("SELECT COUNT(*) FROM `members` WHERE `members`.`UserID` = ? AND `ClubPays` = '0'");
	$sql->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);

	$clubFee = 0;
	$totalFee = 0;

	$payingSwimmerCount = $sql->fetchColumn();

	$clubFees = \SCDS\Membership\ClubMembership::create($db, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $partial_reg);

	$clubFee = $clubFees->getFee();

	if ($partial_reg) {
		$sql = "SELECT * FROM members WHERE `members`.`UserID` = ? AND `members`.`RR` = 1";
	} else {
		$sql = "SELECT * FROM members WHERE `members`.`UserID` = ?";
	}
	$getMembers = $db->prepare($sql);
	$getMembers->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);

	$member = $getMembers->fetchAll(PDO::FETCH_ASSOC);
	$count = sizeof($member);

	$paymentItems[] = [
		'description' => 'Club Membership Fee',
		'amount' => $clubFee,
		'type' => 'debit',
		'date' => $clubDate
	];

	if ($clubDiscount > 0 && $renewal == 0) {
		$totalFee += $clubFee*(1-($clubDiscount/100));
		$paymentItems[] = [
			'description' => 'Club Membership Fee',
			'amount' => $clubFee* ($clubDiscount/100),
			'type' => 'credit',
			'date' => $clubDate
		];
	} else {
		$totalFee += $clubFee;
	}

	$asaFees = [];

	$asa1 = app()->tenant->getKey('ASA-County-Fee-L1') + app()->tenant->getKey('ASA-Regional-Fee-L1') + app()->tenant->getKey('ASA-National-Fee-L1');
	$asa2 = app()->tenant->getKey('ASA-County-Fee-L2') + app()->tenant->getKey('ASA-Regional-Fee-L2') + app()->tenant->getKey('ASA-National-Fee-L2');
	$asa3 = app()->tenant->getKey('ASA-County-Fee-L3') + app()->tenant->getKey('ASA-Regional-Fee-L3') + app()->tenant->getKey('ASA-National-Fee-L3');

	for ($i = 0; $i < $count; $i++) {
		if ($member[$i]['ASACategory'] == 1 && !$member[$i]['ClubPays']) {
			$asaFees[$i] = $asa1;
		} else if ($member[$i]['ASACategory'] == 2  && !$member[$i]['ClubPays']) {
			$asaFees[$i] = $asa2;
		} else if ($member[$i]['ASACategory'] == 3  && !$member[$i]['ClubPays']) {
			$asaFees[$i] = $asa3;
		} else {
			$asaFees[$i] = 0;
		}

		$paymentItems[] = [
			'description' => $member[$i]['MForename'] . ' Cat ' . $member[$i]['ASACategory'] . ' SE Membership',
			'amount' => $asaFees[$i],
			'type' => 'debit',
			'member' => $member[$i]['MemberID'],
			'date' => $asaDate
		];

		if ($member[$i]['RRTransfer']) {
			// $totalFee += $asaFees[$i];
			$paymentItems[] = [
				'description' => $member[$i]['MForename'] . ' SE Transfer',
				'amount' => $asaFees[$i],
				'type' => 'credit',
				'date' => $clubDate
			];
		} else if ($swimEnglandDiscount > 0 && $renewal == 0) {
			$totalFee += $asaFees[$i]*(1-($swimEnglandDiscount/100));
			$paymentItems[] = [
				'description' => $member[$i]['MForename'] . ' SE Transfer',
				'amount' => $asaFees[$i] * ($swimEnglandDiscount/100),
				'type' => 'credit',
				'date' => $clubDate
			];
		} else {
			$totalFee += $asaFees[$i];
		}
	}

	// Print array for testing
	// reportError($paymentItems);

	$clubFeeString = number_format($clubFee/100,2,'.','');
	$totalFeeString = number_format($totalFee/100,2,'.','');

	$sql = $db->prepare("SELECT COUNT(*) FROM `paymentPreferredMandate` WHERE `UserID` = ?");
	$sql->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
	$hasDD = false;
	if ($sql->fetchColumn() == 1) {
		$hasDD = true;
	}

	$getCountNewMandates = $db->prepare("SELECT COUNT(*) FROM stripeMandates INNER JOIN stripeCustomers ON stripeMandates.Customer = stripeCustomers.CustomerID WHERE stripeCustomers.User = ? AND stripeMandates.MandateStatus != 'inactive';");
	$getCountNewMandates->execute([
		$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
	]);
	$hasStripeMandate = $getCountNewMandates->fetchColumn() > 0;

	if ($hasDD || $hasStripeMandate || (!app()->tenant->getGoCardlessAccessToken() &&  !stripeDirectDebit(true)) || $avoidDirectDebit) {
		if ($hasDD || $hasStripeMandate) {
			// INSERT Payment into pending
			$date = new \DateTime('now', new DateTimeZone('Europe/London'));
			$date->setTimezone(new DateTimeZone('UTC'));
			$description = "Membership Renewal";
			if ($renewal == 0) {
				$description = "Club Registration Fee";
			}
			for ($i = 0; $i < $count; $i++) {
				$description .= ", " . $member[$i]['MForename'];
			}
			$insert = $db->prepare("INSERT INTO `paymentsPending` (`Date`, `Status`, `UserID`, `Name`, `Amount`, `Currency`, `Type`) VALUES (?, ?, ?, ?, ?, ?, ?)");

			/*
			$insert->execute([
				$date->format('Y-m-d'),
				'Pending',
				$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
				$description,
				$totalFee,
				'GBP',
				'Payment'
			]);
			$payID = $db->lastInsertId();
			*/

			foreach ($paymentItems as $charge) {
				$type = 'Payment';
				if ($charge['type'] == 'credit') {
					$type = 'Refund';
				}
				$insert->execute([
					$charge['date'],
					'Pending',
					$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
					$charge['description'],
					$charge['amount'],
					'GBP',
					$type
				]);

				if (isset($charge['member'])) {
					$payID[$charge['member']] = $db->lastInsertId();
				}
			}

			if ($renewal != 0) {
				// Foreach check if in renewal members
				$countInRenewalMembers = $db->prepare("SELECT COUNT(*) FROM renewalMembers WHERE MemberID = ? AND RenewalID = ?");
				$insert = $db->prepare("INSERT INTO `renewalMembers` (`PaymentID`, `MemberID`, `RenewalID`, `Date`, `CountRenewal`, `Renewed`) VALUES (?, ?, ?, ?, ?, ?)");
				$update = $db->prepare("UPDATE renewalMembers SET PaymentID = ?, `Date` = ?, CountRenewal = ?, Renewed = ? WHERE MemberID = ? AND RenewalID = ?");

				for ($i = 0; $i < $count; $i++) {
					$countInRenewalMembers->execute([
						$member[$i]['MemberID'],
						$renewal
					]);

					if ($countInRenewalMembers->fetchColumn() > 0) {
						// Update them
						$update->execute([
							$payID[$member[$i]['MemberID']],
							$date->format("Y-m-d H:i:s"),
							true,
							true,
							$member[$i]['MemberID'],
							$renewal
						]);
					} else {
						// Add them
						$insert->execute([
							$payID[$member[$i]['MemberID']],
							$member[$i]['MemberID'],
							$renewal,
							$date->format("Y-m-d H:i:s"),
							true,
							true
						]);
					}
				}
			}
		}

		$progress = $db->prepare("UPDATE `renewalProgress` SET `Stage` = `Stage` + 1 WHERE `RenewalID` = ? AND `UserID` = ?");
		$progress->execute([
			$renewal,
			$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
		]);

		if (user_needs_registration($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
			$sql = "UPDATE `users` SET `RR` = 0 WHERE `UserID` = ?";
			$query = $db->prepare($sql);
			$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
			
			$query = $db->prepare("UPDATE `members` SET `RR` = 0, `RRTransfer` = 0 WHERE `UserID` = ?");
			$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
			
			// Remove from status tracker
			$delete = $db->prepare("DELETE FROM renewalProgress WHERE UserID = ? AND RenewalID = ?");
			$delete->execute([
				$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
				$renewal
			]);
			$location = autoUrl("");
		} else {
			$location = autoUrl("renewal/go");
		}
	} else {
		$location = autoUrl("renewal/payments/setup");
	}

	$db->commit();
} catch (Exception $e) {
	$db->rollBack();
	reportError($e);
	$location = autoUrl("renewal/go");
}

header("Location: " . $location);