<?php

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

$locked = $veto = false;
if (isset($_POST['parent-veto']) && $_POST['parent-veto']) {
  $veto = true;
}
if (isset($_POST['lock-entry']) && $_POST['lock-entry']) {
  $locked = true;
}

$db = app()->db;
$tenant = app()->tenant;

$galaDetails = $db->prepare("SELECT GalaName `name`, GalaDate `ends`, CoachEnters, GalaFee fee, GalaFeeConstant gfc, HyTek, ProcessingFee FROM galas WHERE GalaID = ? AND Tenant = ?");
$galaDetails->execute([
  $id,
  $tenant->getId()
]);
$gala = $galaDetails->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

if (!$gala['CoachEnters']) {
  halt(404);
}

$galaData = new GalaPrices($db, $id);

$galaDate = new DateTime($gala['ends'], new DateTimeZone('Europe/London'));
$nowDate = new DateTime('now', new DateTimeZone('Europe/London'));

$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
$getSessions->execute([$id]);
$sessions = $getSessions->fetchAll(PDO::FETCH_ASSOC);

$getAvailableSwimmers = $db->prepare("SELECT Member, members.UserID parent, MForename fn, MSurname sn, DateOfBirth dob, gs.`Name` gsname, members.ASANumber `se` FROM (((galaSessionsCanEnter ca INNER JOIN galaSessions gs ON gs.ID = ca.Session) INNER JOIN members ON ca.Member = members.MemberID) LEFT JOIN galaEntries ge ON ge.GalaID = gs.Gala AND ge.MemberID = members.MemberID) WHERE gs.Gala = ? AND ca.CanEnter = ? AND ge.EntryID IS NULL ORDER BY sn ASC, fn ASC");
$getAvailableSwimmers->execute([$id, true]);
$swimmers = $getAvailableSwimmers->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

// Insert entry to database
$insert = $db->prepare("INSERT INTO galaEntries (GalaID, MemberID, FeeToPay, Charged, Locked, Vetoable, EntryProcessed, ProcessingFee, 25Free, 50Free, 100Free, 200Free, 400Free, 800Free, 1500Free, 25Back, 50Back, 100Back, 200Back, 25Breast, 50Breast, 100Breast, 200Breast, 25Fly, 50Fly, 100Fly, 200Fly, 100IM, 150IM, 200IM, 400IM) VALUES (:gala, :member, :fee, :charged, :locked, :vetoable, :processed, :processFee, :val25Free, :val50Free, :val100Free, :val200Free, :val400Free, :val800Free, :val1500Free, :val25Back, :val50Back, :val100Back, :val200Back, :val25Breast, :val50Breast, :val100Breast, :val200Breast, :val25Fly, :val50Fly, :val100Fly, :val200Fly, :val100IM, :val150IM, :val200IM, :val400IM)");

$addToNotify = $db->prepare("INSERT INTO notify (`UserID`, `Status`, `Subject`, `Message`, `ForceSend`, `EmailType`) VALUES (?, ?, ?, ?, ?, ?)");

$now = new DateTime('now', new DateTimeZone('Europe/London'));
$eOY = new DateTime('last day of December ' . $now->format('Y'), new DateTimeZone('Europe/London'));

$users = [];

$db->beginTransaction();

try {
  if ($swimmers != null) {
    foreach ($swimmers as $member => $info) {
    
      // Date of birth data
      $dob = new DateTime($info[0]['dob'], new DateTimeZone('Europe/London'));
      $ageOnLastDay = $now->diff($dob);
      $ageAtEOY = $eOY->diff($dob);
      
      htmlspecialchars($info[0]['sn'] . ', ' . $info[0]['fn']);
      foreach ($info as $row) {
        htmlspecialchars($row['gsname']);
      }
      $dob->format('j F Y');
      $ageOnLastDay->format('%y');
      $ageAtEOY->format('%y');
      htmlspecialchars($info[0]['se']);

      $entering = [
        'gala' => $id,
        'member' => $member,
        'fee' => 0,
        'charged' => false,
        'locked' => $locked,
        'vetoable' => $veto,
        'forename' => $info[0]['fn'],
        'surname' => $info[0]['sn'],
      ];
      $count = 0;
      $price = 0;
      $hasEntry = false;
      foreach ($swimsArray as $ev => $name) {
        if (isset($_POST[$member . '-' . $ev]) && bool($_POST[$member . '-' . $ev]) && $galaData->getEvent($ev)->isEnabled()) {
          $entering += [$ev => true];
          $hasEntry = true;
          $count++;
          $price += $galaData->getEvent($ev)->getPrice();
        } else {
          $entering += [$ev => false];
        }
        $insert->bindValue('val' . $ev, $entering[$ev], PDO::PARAM_BOOL);
      }

      if ($gala['ProcessingFee']) {
        $price += $gala['ProcessingFee'];
      }

      $entering['fee'] = (string) (\Brick\Math\BigInteger::of((string) $price))->toBigDecimal()->withPointMovedLeft(2)->toScale(2);

      $insert->bindValue('gala', $entering['gala'], PDO::PARAM_INT);
      $insert->bindValue('member', $member, PDO::PARAM_INT);
      $insert->bindValue('fee', $entering['fee'], PDO::PARAM_STR);
      $insert->bindValue('processFee', $gala['ProcessingFee'], PDO::PARAM_INT);
      $insert->bindValue('processed', false, PDO::PARAM_BOOL);
      $insert->bindValue('charged', $entering['charged'], PDO::PARAM_BOOL);
      $insert->bindValue('locked', $entering['locked'], PDO::PARAM_BOOL);
      $insert->bindValue('vetoable', $entering['vetoable'], PDO::PARAM_BOOL);
      //pre($insert->debugDumpParams());
      if ($hasEntry) {
        // Make the entry
        $insert->execute();

        if (isset($users[$info[0]['parent']])) {
          $users[$info[0]['parent']][] = $entering;
        } else {
          $users += [$info[0]['parent'] => [$entering]];
        }
      }

      //pre($entering);
    }
  }

  //pre($users);

  foreach ($users as $user => $entries) {
    $subject = 'Entry into ' . $gala['name'];
    if (sizeof($entries) > 1) {
      $subject = 'Entries into ' . $gala['name'];
    }

    $message = '<p>' . htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['Forename'] . ' ' . $_SESSION['TENANT-' . app()->tenant->getId()]['Surname']) . ' has entered ';
    for ($i = 0; $i < sizeof($entries); $i++) {
      $message .= htmlspecialchars($entries[$i]['forename']);
      if ($i < sizeof($entries) - 2) {
        $message.= ', ';
      } else if ($i < sizeof($entries) - 1) {
        $message.= ' and ';
      }
    }
    $message .= ' into ' . htmlspecialchars($gala['name']) . '. Full details of the entries are as follows;</p>';

    $totalCost = \Brick\Math\BigDecimal::zero();
    foreach ($entries as $entry) {
      $message .= '<p>' . htmlspecialchars($entry['forename'] . ' ' . $entry['surname']) . ' has been entered into;</p><ul>';
      foreach ($swimsArray as $ev => $name) {
        if (isset($entry[$ev]) && $entry[$ev]) {
          $message .= '<li>' . $name . ', <em>&pound;' . $galaData->getEvent($ev)->getPriceAsString() . '</em></li>';
        }
      }
      $message .= '</ul>';
      $message .= '<p>The cost of ' . htmlspecialchars($entry['forename']) . '\'s entry is <strong>&pound;' . $entry['fee'] . '</strong>.</p>';
      $totalCost->plus($entry['fee']);
    }

    if (sizeof($entries) > 1) {
      $message .= '<p>The total cost of your swimmer\'s entries is <strong>&pound;' . (string) $totalCost->toScale(2) . '</strong>.<p>';
    }

    if ($gala['ProcessingFee'] > 0) {
      $message .= "<p>For this gala there is a per entry processing fee of <strong>&pound;" . htmlspecialchars(MoneyHelpers::intToDecimal($gala['ProcessingFee'])) . "</strong>.</p>";
    }

    if (bool($gala['HyTek'])) {
      $message .= '<p>This is a HyTek gala. As a result you must provide entry times manually. <a href="' . autoUrl("galas") . '">Log into your account to do this</a>.<p>';
    }

    if ($locked || $veto) {
      $message .= '<p>';
    }

    if ($locked) {
      $message .= 'Your coach or gala administrator has locked all entries for this gala. This means they will only let your swimmers swim in events they have chosen.';
    }

    if ($veto && $locked) {
      $message .= ' Your coach allows you to veto this entry, meaning you can withdraw your swimmer from all swims at this gala though you can\'t change which events you have entered.';
    } else if ($veto) {
      $message .= 'Your coach allows you to veto this entry, meaning you can withdraw your swimmer from all swims at this gala or make changes to just some entries.';
    }

    $message .= '</p>';

    $addToNotify->bindValue(1, $user, PDO::PARAM_INT);
    $addToNotify->bindValue(2, 'Queued', PDO::PARAM_STR);
    $addToNotify->bindValue(3, $subject, PDO::PARAM_STR);
    $addToNotify->bindValue(4, $message, PDO::PARAM_STR);
    $addToNotify->bindValue(5, true, PDO::PARAM_BOOL);
    $addToNotify->bindValue(6, 'Galas', PDO::PARAM_STR);
    $addToNotify->execute();
  }

  $db->commit();
  $_SESSION['TENANT-' . app()->tenant->getId()]['SuccessStatus'] = true;
} catch (Exception $e) {
  reportError($e);
  $db->rollBack();
  $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStatus'] = true;
}

header("Location: " . autoUrl("galas/" . $id . "/select-entries"));