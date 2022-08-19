<?php

$db = app()->db;
$tenant = app()->tenant;

$swimsArray = ['25Free','50Free','100Free','200Free','400Free','800Free','1500Free','25Back','50Back','100Back','200Back','25Breast','50Breast','100Breast','200Breast','25Fly','50Fly','100Fly','200Fly','100IM','150IM','200IM','400IM',];
$swimsTextArray = ['25&nbsp;Free','50&nbsp;Free','100&nbsp;Free','200&nbsp;Free','400&nbsp;Free','800&nbsp;Free','1500&nbsp;Free','25&nbsp;Back','50&nbsp;Back','100&nbsp;Back','200&nbsp;Back','25&nbsp;Breast','50&nbsp;Breast','100&nbsp;Breast','200&nbsp;Breast','25&nbsp;Fly','50&nbsp;Fly','100&nbsp;Fly','200&nbsp;Fly','100&nbsp;IM','150&nbsp;IM','200&nbsp;IM','400&nbsp;IM',];
$swimsTimeArray = ['25FreeTime','50FreeTime','100FreeTime','200FreeTime','400FreeTime','800FreeTime','1500FreeTime','25BackTime','50BackTime','100BackTime','200BackTime','25BreastTime','50BreastTime','100BreastTime','200BreastTime','25FlyTime','50FlyTime','100FlyTime','200FlyTime','100IMTime','150IMTime','200IMTime','400IMTime',];
$entriesArray = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

$sql = null;

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
  $sql = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE galas.Tenant = ? AND `EntryID` = ? AND members.UserID = ? ORDER BY `galas`.`GalaDate` DESC;");
  $sql->execute([
    $tenant->getId(),
    $id,
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
  ]);
} else {
  $sql = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE galas.Tenant = ? AND `EntryID` = ? ORDER BY `galas`.`GalaDate` DESC;");
  $sql->execute([
    $tenant->getId(),
    $id
  ]);
}
$row = $sql->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
  halt(404);
}

$galaData = new GalaPrices($db, $row["GalaID"]);

$closingDate = new DateTime($row['ClosingDate'], new DateTimeZone('Europe/London'));
$theDate = new DateTime('now', new DateTimeZone('Europe/London'));

if (bool($row['Charged']) || bool($row['EntryProcessed']) || ($closingDate < $theDate && ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Admin' && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Galas')) || (bool($row['Locked']) && ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Admin' && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Galas'))) {
  halt(404);
}

$entryList = "";
$numEntered = 0;
$price = 0;
for ($i = 0; $i < sizeof($entriesArray); $i++) {
  if (bool($_POST[$swimsArray[$i]]) && $galaData->getEvent($swimsArray[$i])->isEnabled()) {
    $entriesArray[$i] = true;
    $numEntered++;
    $price += $galaData->getEvent($swimsArray[$i])->getPrice();
    $entryList .= '<li>' . $swimsTextArray[$i] . ', <em>&pound;' . $galaData->getEvent($swimsArray[$i])->getPriceAsString() . '</em></li>';
  }
}

if ($row['ProcessingFee'] > 0) {
  $price += $row['ProcessingFee'];
}

$galaFee = (string) (\Brick\Math\BigInteger::of((string) $price))->toBigDecimal()->withPointMovedLeft(2);

try {
  $update = $db->prepare("UPDATE galaEntries SET 25Free = ?, 50Free = ?, 100Free = ?, 200Free = ?, 400Free = ?, 800Free = ?, 1500Free = ?, 25Back = ?, 50Back = ?, 100Back = ?, 200Back = ?, 25Breast = ?, 50Breast = ?, 100Breast = ?, 200Breast = ?, 25Fly = ?, 50Fly = ?, 100Fly = ?, 200Fly = ?, 100IM = ?, 150IM = ?, 200IM = ?, 400IM = ?, FeeToPay = ?, ProcessingFee = ? WHERE EntryID = ?");
  $updateArray = array_merge($entriesArray, [$galaFee, $row['ProcessingFee'], $id]);
  $update->execute($updateArray);

  $sql = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE `EntryID` = ? ORDER BY `galas`.`GalaDate` DESC;");
  $sql->execute([$id]);
  $row = $sql->fetch(PDO::FETCH_ASSOC);

  $subject = "Your Updated " . $row['GalaName'] . " Entry";
  $message = "";
  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') {
    $message .= "<p><strong>Changes have been made to this gala entry by a member of staff. This is a courtesy email for you.</strong></p>";
  }
  $message .= "<p>Here are the swims selected for " . htmlspecialchars($row['MForename'] . " " . $row['MSurname']) . "'s updated " . htmlspecialchars($row['GalaName']) . " entry.</p>";
  $message .= "<ul>" . $entryList . "</ul>";
  $message .= "<p>You have entered " . (new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($numEntered) . " events. The <strong>total fee payable is &pound;" . $galaFee . "</strong>.</p>";
  if ($row['ProcessingFee'] > 0) {
    $message .= "<p>Your entry includes a processing fee of <strong>&pound;" . htmlspecialchars(MoneyHelpers::intToDecimal($row['ProcessingFee'])) . "</strong>.</p>";
  }
  $message .= '<p>If you have any questions, please contact the ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' gala team as soon as possible.</p>';
  $notify = "INSERT INTO notify (`UserID`, `Status`, `Subject`, `Message`,
  `ForceSend`, `EmailType`) VALUES (?, 'Queued', ?, ?, 1, 'Galas')";
  $db->prepare($notify)->execute([$row['UserID'], $subject, $message]);
  $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess'] = true;
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError'] = true;
}

header("Location: " . autoUrl("galas/entries/" . $id));