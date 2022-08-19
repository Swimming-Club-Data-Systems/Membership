<?php

include BASE_PATH . 'includes/regions/countries-iso3166.php';

/**
 * Return value as a FILTER_VALIDATE_BOOLEAN
 */
function bool($var)
{
  return filter_var($var, FILTER_VALIDATE_BOOLEAN);
}

/**
 * Verify a user by email/pass
 */
function verifyUser($user, $password)
{
  $db = app()->db;

  $username = trim($user);
  $password = trim($password);

  try {
    $query = $db->prepare("SELECT Password, UserID, AccessLevel FROM users WHERE EmailAddress = :user LIMIT 1");
    $query->execute(['user' => $username]);
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    $count = sizeof($result);

    if ($count == 1) {
      $hash = $result[0]['Password'];

      if (password_verify($password, $hash)) {
        if ($result[0]['AccessLevel'] != 'Parent') {
          return true;
        } else {
          // Verify parent has connected child
          $sql = "SELECT COUNT(*) FROM `members` WHERE `UserID` = ?";
          try {
            $query = $db->prepare($sql);
            $query->execute([$result[0]['UserID']]);
            if ($query->fetchColumn() == 0) {
              halt(404);
            }
          } catch (PDOException $e) {
            halt(500);
          }
        }
      }
    }
  } catch (PDOException $e) {
    halt(500);
  }
  return false;
}

function notifySend($to, $subject, $emailMessage, $name = null, $emailaddress = null, $from = null)
{

  // use PHPMailer\PHPMailer\PHPMailer;
  // use Aws\Ses\SesClient;
  // use Aws\Ses\Exception\SesException;


  // Create an SesClient.
  $client = new Aws\SesV2\SesV2Client([
    'region' => getenv('AWS_S3_REGION'),
    'version' => 'latest'
  ]);

  $mailObject = new \CLSASC\SuperMailer\CreateMail();
  $mail = new PHPMailer\PHPMailer\PHPMailer(true);
  //echo $mailObject->getFormattedHtml();
  //echo $mailObject->getFormattedPlain();

  if (!isset($from['Email'])) {
    $from['Email'] = "noreply@" . getenv('EMAIL_DOMAIN');
  }
  if (!isset($from['Name']) && isset(app()->tenant)) {
    $from['Name'] = app()->tenant->getKey('CLUB_NAME');
  } else if (!isset($from['Name'])) {
    $from['Name'] = 'SCDS Membership MT';
  }

  $cellClass = 'style="display:table;background:#eee;padding:10px;margin 0 auto 10px auto;width:100%;"';
  $htmlMessage = str_replace('class="cell"', $cellClass, $emailMessage);

  $mailObject->setHtmlContent($htmlMessage);

  // if (isset($from['PlainText']) && $from['PlainText']) {
  //   // $message = $emailMessage;
  //   // $mailObject->setHtmlContent($from['PlainText']);
  // }

  if (isset($from['Unsub']['Allowed']) && $from['Unsub']['Allowed']) {
    $mailObject->setUnsubscribable();
  }

  // if ($from['Email'] == "notify@" . getenv('EMAIL_DOMAIN') || $from['Email'] == "payments@" . getenv('EMAIL_DOMAIN')) {
  //   $email->addHeader("List-Archive", autoUrl("myaccount/notify/history"));
  // }

  if (isset($from['CC']) && $from['CC'] != null) {
    foreach ($from['CC'] as $ccEmailAddress => $ccName) {
      $mail->addCC($ccEmailAddress, $ccName);
    }
  }

  if (isset($from['BCC']) && $from['BCC'] != null) {
    foreach ($from['BCC'] as $bccEmailAddress => $bccName) {
      $mail->addBCC($bccEmailAddress, $bccName);
    }
  }

  $plain = $mailObject->getFormattedPlain();
  $html = $mailObject->getFormattedHtml();

  if (isset($from['Unsub']['Allowed']) && $from['Unsub']['Allowed']) {
    $unsubLink = autoUrl("notify/unsubscribe/" . dechex($from['Unsub']['User']) .  "/" . urlencode($emailaddress) . "/" . urlencode($from['Unsub']['List']));
    $plain = str_replace('-unsub_link-', $unsubLink, $plain);
    $html = str_replace('-unsub_link-', $unsubLink, $html);
  }

  if ($client) {
    if (isset($from['ReplyTo'])) {
      $mail->addReplyTo($from['ReplyTo']['Email'], $from['ReplyTo']['Name']);
    } else if (isset(app()->tenant)) {
      $mail->addReplyTo(app()->tenant->getKey('CLUB_EMAIL'), app()->tenant->getKey('CLUB_NAME'));
    }
    $mail->setFrom($from['Email'], $from['Name']);
    $mail->Subject = $subject;
    $mail->addAddress($emailaddress, $name);
    if (!(isset($from['PlainTextOnly']) && $from['PlainTextOnly'])) {
      $mail->isHTML(true);
      $mail->Body = $html;
    } else {
      $mail->Body = $plain;
    }
    $mail->AltBody = $plain;

    $mail->XMailer = 'Membership by Swimming Club Data Systems';
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // Attempt to assemble the above components into a MIME message.
    if (!$mail->preSend()) {
      throw new Exception($mail->ErrorInfo);
    } else {
      // Create a new variable that contains the MIME message.
      $message = $mail->getSentMIMEMessage();
    }

    // Try to send the message.
    try {
      $result = $client->sendEmail([
        'Content' => [
          'Raw' => [
            'Data' => $message
          ]
        ]
      ]);
      // If the message was sent, show the message ID.
      $messageId = $result->get('MessageId');
      // echo ("Email sent! Message ID: $messageId" . "\n");
      return true;
    } catch (Aws\Ses\Exception\SesException $error) {
      // If the message was not sent, show a message explaining what went wrong.
      // pre($error->getAwsErrorMessage());
      // exit();
      throw new Exception("The email was not sent. Error message: "
        . $error->getAwsErrorMessage() . "\n");
    }
  } else {
    throw new Exception('Mailer failed');
  }

  return false;
}

function getAttendanceByID($link = null, $id, $weeks = "all")
{
  $db = app()->db;
  $tenant = app()->tenant;

  $hideAttendance = !bool($tenant->getKey('HIDE_MEMBER_ATTENDANCE'));
  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent' || $hideAttendance) {

    try {
      $fromDate = new DateTime('1970-01-01', new DateTimeZone('Europe/London'));
      if ((int) $weeks > 0) {
        $fromDate = new DateTime('-' . (int) $weeks . ' weeks', new DateTimeZone('Europe/London'));
      }
      $toDate = new DateTime('now', new DateTimeZone('Europe/London'));

      $history = AttendanceHistory::getHistory($id, $fromDate->format('Y-m-d'), $toDate->format('Y-m-d'));

      return number_format($history->getPercentageMandatory(), 1, ".", "");
    } catch (Exception $e) {
      return 'INVALID DATA ';
    }
  }

  return 'DATA HIDDEN ';
}

function mySwimmersTable($link = null, $userID)
{
  $db = app()->db;
  // Get the information about the swimmer
  $swimmers = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname,
  members.ASAPaid, members.ClubPaid, users.Forename, users.Surname, users.EmailAddress,
  members.ASANumber FROM (members INNER JOIN
  users ON members.UserID = users.UserID) WHERE members.UserID = ?");
  $getSquads = $db->prepare("SELECT SquadName squad, SquadFee fee, squadMembers.Paying FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad WHERE Member = ?");
  // squads.SquadName, squads.SquadFee
  $swimmers->execute([$userID]);
  $swimmer = $swimmers->fetch(PDO::FETCH_ASSOC);

  if ($swimmer != null) { ?>
    <ul class="list-group mb-3">
      <?php do {
        $getSquads->execute([
          $swimmer['MemberID']
        ]);
      ?>
        <li class="list-group-item">
          <div class="row">
            <div class="col-sm-6">
              <p class="mb-0">
                <strong><a href="<?= autoUrl("swimmers/" . $swimmer['MemberID']) ?>"><?= htmlspecialchars($swimmer['MForename'] . " " . $swimmer['MSurname']) ?></a></strong>
              </p>
              <ul class="mb-0 list-unstyled">
                <?php if ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)) {
                  do { ?>
                    <li><?= htmlspecialchars($squad['squad']) ?>, <em><?php if (!bool($squad['Paying']) || (int) $squad['fee'] == 0) { ?><del>&pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $squad['fee']))->toScale(2) ?></del> &pound;0/month<?php } else { ?>&pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $squad['fee']))->toScale(2) ?>/month<?php } ?></em></li>
                  <?php } while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC));
                } else { ?>
                  <li>No squads</li>
                <?php } ?>
              </ul>
              <div class="mb-3 d-sm-none"></div>
            </div>
            <div class="col text-sm-end">
              <p class="mb-0">
                <a href="https://www.swimmingresults.org/biogs/biogs_details.php?tiref=<?= htmlspecialchars($swimmer['ASANumber']) ?>" target="_blank" title="Swim England Biographical Data"><?= htmlspecialchars($swimmer['ASANumber']) ?> <i class="fa fa-external-link" aria-hidden="true"></i></a>
              </p>
              <p class="mb-0">
                <?= htmlspecialchars(getAttendanceByID(null, $swimmer['MemberID'], 4)) ?>% attendance
              </p>

            </div>
          </div>
        </li>
      <?php } while ($swimmer = $swimmers->fetch(PDO::FETCH_ASSOC)); ?>
    </ul>

  <?php } else { ?>

    <div class="alert alert-warning">
      <p class="mb-0">
        <strong>There are no members connected.</strong>
      </p>
    </div>

  <?php }
}

function generateRandomString($length)
{
  $characters =
    '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

function courseLengthString($string)
{
  $courseLength = null;
  if ($string == "SHORT") {
    $courseLength = "Short Course";
  } else if ($string == "LONG") {
    $courseLength = "Long Course";
  } else {
    $courseLength = "Non Standard Pool Distance";
  }
  return $courseLength;
}

function myMonthlyFeeTable($link = null, $userID)
{
  try {
    $fs = new FeeSummer((int) (new DateTime('now', new DateTimeZone('Europe/London')))->format('n'));
    $fees = $fs->sumUser($userID);

    $ret = '<ul class="list-group">';

    // Squad fees
    $ret .= '<li class="list-group-item"><div class="row"><div class="col">Squad fees</div><div class="col text-end">&pound;' . htmlspecialchars((string) \Brick\Math\BigInteger::of((string) $fees['squad_total'])->toBigDecimal()->withPointMovedLeft(2)) . '</div></div></li>';

    // Extra fees
    $ret .= '<li class="list-group-item"><div class="row"><div class="col">Extra fees</div><div class="col text-end">&pound;' . htmlspecialchars((string) \Brick\Math\BigInteger::of((string) $fees['extra_total'])->toBigDecimal()->withPointMovedLeft(2)) . '</div></div></li>';

    $total = $fees['squad_total'] + $fees['extra_total'];

    // Total fees
    $ret .= '<li class="list-group-item font-weight-bold bg-light"><div class="row"><div class="col">Monthly total</div><div class="col text-end">&pound;' . htmlspecialchars((string) \Brick\Math\BigInteger::of((string) $total)->toBigDecimal()->withPointMovedLeft(2)) . '</div></div></li>';

    $ret .= '</ul>';

    return $ret;
  } catch (Exception $e) {
    return '<div class="alert alert-warning">Data currently unavailable</div>';
  }
}

function autoUrl($relative, $includeClub = true)
{
  // Returns an absolute URL
  $rootUrl = getenv('ROOT_URL');

  if (getenv('DOMAIN_TYPE') == 'SUBDOMAIN') {
    $rootUrl = 'https://' . app('request')->hostname . '/';
  }

  if ($includeClub && isset(app()->tenant) && app()->tenant->getDomain()) {
    $rootUrl = 'https://' . app()->tenant->getDomain() . '/';
  }

  return rtrim($rootUrl . $relative, '/');
}

function platformUrl($relative)
{
  // Returns an absolute URL
  $rootUrl = getenv('ROOT_URL');

  return rtrim($rootUrl . $relative, '/');
}

function webhookUrl($relative, $includeClub = true)
{
  // Returns an absolute URL
  $rootUrl = getenv('ROOT_URL');

  if (isset(app()->tenant)) {
    $club = app()->tenant;
    if ($club && $includeClub) {
      if ($club->getCode()) {
        $rootUrl .= mb_strtolower($club->getCode()) . '/';
      } else if ($club->getId()) {
        $rootUrl .= $club->getId() . '/';
      }
    }
  }

  return rtrim($rootUrl . $relative, '/');
}

function monthlyFeeCost($link = null, $user, $format = "decimal")
{
  $db = app()->db;

  $getUserMembers = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname FROM members WHERE UserID = ?");

  $getSquadMetadata = $db->prepare("SELECT squads.SquadName, squads.SquadID, squads.SquadFee, squadMembers.Paying FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad WHERE squadMembers.Member = ?;");

  // Get user members
  $getUserMembers->execute([
    $user
  ]);

  $numMembers = 0;
  $discount = 0;
  $total = 0;

  $discountMembers = [];

  while ($member = $getUserMembers->fetch(PDO::FETCH_ASSOC)) {
    $getSquadMetadata->execute([
      $member['MemberID']
    ]);

    $paying = false;
    $memberTotal = 0;

    while ($squad = $getSquadMetadata->fetch(PDO::FETCH_ASSOC)) {
      if (bool($squad['Paying'])) {
        $paying = true;
      }

      $fee = Brick\Math\BigDecimal::of((string) $squad['SquadFee'])->withPointMovedRight(2)->toInt();
      $memberTotal += $fee;

      if (!bool($squad['Paying'])) {
        $memberTotal -= $fee;
      }
    }

    if ($paying) {
      $numMembers++;

      $memberFees = [
        'fee' => $memberTotal,
        'member' => $member['MForename'] . " " . $member['MSurname']
      ];
      $discountMembers[] = $memberFees;

      $total += $memberTotal;
    }
  }

  // If is CLS handle discounts
  if (app()->tenant->isCLS()) {
    usort($discountMembers, function ($item1, $item2) {
      return $item2['fee'] <=> $item1['fee'];
    });

    $number = 0;
    foreach ($discountMembers as $member) {
      $number++;

      // Calculate discounts if required.
      // Always round discounted value down - Could save clubs pennies!
      $swimmerDiscount = 0;
      try {
        $memberTotalDec = \Brick\Math\BigInteger::of($member['fee'])->toBigDecimal()->withPointMovedLeft(2);
        if ($number == 3) {
          // 20% discount applies
          $swimmerDiscount = $memberTotalDec->multipliedBy('0.20')->toScale(2, Brick\Math\RoundingMode::DOWN)->withPointMovedRight(2)->toInt();
        } else if ($number > 3) {
          // 40% discount applies
          $swimmerDiscount = $memberTotalDec->multipliedBy('0.40')->toScale(2, Brick\Math\RoundingMode::DOWN)->withPointMovedRight(2)->toInt();
        }
      } catch (Exception $e) {
        // Something went wrong so ensure these stay zero!
        $swimmerDiscount = 0;
      }

      if ($swimmerDiscount > 0) {
        // Apply credit to account for discount
        $total -= $swimmerDiscount;
      }
    }
  }

  // return $total;

  $format = strtolower($format);
  if ($format == "decimal") {
    return (string) \Brick\Math\BigDecimal::of((string) $total)->withPointMovedLeft(2)->toScale(2);
  } else if ($format == "int") {
    return $total;
  } else if ($format == "string") {
    return "&pound;" . (string) \Brick\Math\BigDecimal::of((string) $total)->withPointMovedLeft(2)->toScale(2);
  }
}

function monthlyExtraCost($link = null, $userID, $format = "decimal")
{
  $db = app()->db;
  $query = $db->prepare("SELECT extras.ExtraName, extras.ExtraFee FROM ((members
  INNER JOIN `extrasRelations` ON members.MemberID = extrasRelations.MemberID)
  INNER JOIN `extras` ON extras.ExtraID = extrasRelations.ExtraID) WHERE
  members.UserID = ?");
  $query->execute([$userID]);
  $totalCost = \Brick\Math\BigDecimal::zero();

  while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $totalCost = $totalCost->plus(\Brick\Math\BigDecimal::of((string) $row['ExtraFee']));
  }

  $format = strtolower($format);
  if ($format == "decimal") {
    return (string) $totalCost->toScale(2);
  } else if ($format == "int") {
    return $totalCost->withPointMovedRight(2)->toInt();
  } else if ($format == "string") {
    return "&pound;" . (string) $totalCost->toScale(2);
  }
}

function swimmers($link = null, $userID, $fees = false)
{
  $db = app()->db;
  $getSwimmers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM members WHERE members.UserID = ?");
  $getSwimmers->execute([
    $userID,
  ]);

  $getSquads = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Member = ?");

  $swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC);

  $content = '';
  if ($swimmer) {
    $content .= '<ul class="mb-0 list-unstyled">';

    do {
      $getSquads->execute([
        $swimmer['id']
      ]);
      $numSquads = $getSquads->fetchColumn();

      $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
      $numSquadsText = $f->format($numSquads);

      $content .= '<li>' . htmlspecialchars($swimmer['fn'] . ' ' . $swimmer['sn']) . ', in ' . htmlspecialchars($numSquadsText) . ' squads</li>';
    } while ($swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC));

    $content .= '</ul>';
  } else {
    $content .= '<p class="mb-0">No members</p>';
  }

  return $content;
}

function paymentHistory($link = null, $user, $type = null)
{
  $db = app()->db;
  $sql = $db->prepare("SELECT * FROM `payments` WHERE `UserID` = ? ORDER BY `PaymentID` DESC LIMIT 0, 5;");
  $sql->execute([$user]);
  $row = $sql->fetch(PDO::FETCH_ASSOC);

  if ($row != null) { ?>
    <div class="list-group">
      <?php do { ?>
        <a class="list-group-item list-group-item-action" href="<?= htmlspecialchars((autoUrl("payments/statements/" . $row['PaymentID']))) ?>" title="Transaction Statement">
          <div class="row align-items-center">
            <div class="col-9">
              <p class="mb-0 text-primary">
                <strong>
                  <?= htmlspecialchars($row['Name']) ?>
                </strong>
              </p>
              <p class="mb-0">
                <?php echo date('j F Y', strtotime($row['Date'])); ?>
              </p>
            </div>
            <div class="col text-end">
              <p class="mb-0">
                <strong>&pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $row['Amount']))->withPointMovedLeft(2)->toScale(2) ?></strong>
              </p>
              <p class="mb-0">
                View for status
              </p>
            </div>
          </div>
        </a>
      <?php } while ($row = $sql->fetch(PDO::FETCH_ASSOC));  ?>
    </div>
  <?php } else { ?>
    <div class="alert alert-warning mb-0">
      <strong>You have no previous payments</strong> <br>
      Payments and Refunds will appear here once they have been requested from
      your bank.
    </div>
  <?php }
}

function feesToPay($link = null, $user)
{
  $db = app()->db;
  $sql = $db->prepare("SELECT * FROM `paymentsPending` WHERE `UserID` = ? AND `PMkey` IS NULL AND `Status` = 'Pending' ORDER BY `Date` DESC LIMIT 0, 30;");
  $sql->execute([$user]);
  $row = $sql->fetch(PDO::FETCH_ASSOC);
  if ($row != null) { ?>
    <ul class="list-group">
      <?php do { ?>
        <li class="list-group-item">
          <div class="row align-items-center">
            <div class="col-9">
              <p class="mb-0">
                <strong>
                  <?= htmlspecialchars($row['Name']) ?>
                </strong>
              </p>
              <p class="mb-0">
                <?= date('j F Y', strtotime($row['Date'])) ?>
              </p>
            </div>
            <div class="col text-end">
              <p class="mb-0">
                <?php if ($row['Type'] == 'Payment') { ?>
                  &pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $row['Amount']))->withPointMovedLeft(2)->toScale(2) ?>
                <?php } else { ?>
                  -&pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $row['Amount']))->withPointMovedLeft(2)->toScale(2) ?> (Credit)
                <?php } ?>
              </p>
            </div>
          </div>
        </li>
      <?php } while ($row = $sql->fetch(PDO::FETCH_ASSOC));  ?>
    </ul>
  <?php } else { ?>
    <div class="alert alert-warning mb-0">
      <strong>You have no current fees</strong> <br>
      Fee will appear here when they have been added to your account and have not
      been requested from the bank
    </div>
<?php }
}

function getBillingDate($link = null, $user)
{
  $db = app()->db;
  $sql = $db->prepare("SELECT * FROM `paymentSchedule` WHERE `UserID` = ?;");
  $sql->execute([$user]);
  $row = $sql->fetch(PDO::FETCH_ASSOC);

  if ($row != null) {
    $ordinal = null;
    if ($row['Day'] % 10 == 1) {
      $ordinal = "st";
    } else if ($row['Day'] % 10 == 2) {
      $ordinal = "nd";
    } else if ($row['Day'] % 10 == 3) {
      $ordinal = "rd";
    } else {
      $ordinal = "th";
    }
    return $row['Day'] . $ordinal;
  } else {
    return "1st";
  }
}

function userHasMandates($user)
{
  $db = app()->db;
  $sql = $db->prepare("SELECT COUNT(*) FROM `paymentPreferredMandate` WHERE `UserID` = ?");
  $sql->execute([$user]);
  return $sql->fetchColumn() > 0;
}

function paymentExists($payment)
{
  $db = app()->db;
  $sql = $db->prepare("SELECT COUNT(*) FROM `payments` WHERE `PMkey` = ?;");
  $sql->execute([$payment]);
  if ($sql->fetchColumn() == 1) {
    return true;
  } else {
    return false;
  }
}

function mandateExists($mandate)
{
  $db = app()->db;
  $sql = $db->prepare("SELECT COUNT(*) FROM `paymentMandates` WHERE `Mandate` = ?");
  $sql->execute([$mandate]);

  if ($sql->fetchColumn() == 1) {
    return true;
  } else {
    return false;
  }
}

function updatePaymentStatus($PMkey)
{
  $client = null;
  $db = app()->db;
  require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';
  $client = SCDS\GoCardless\Client::get();
  $sql2bool = $payment = $status = null;
  try {
    $payment = $client->payments()->get($PMkey);
    $status = $payment->status;
    $payout = null;
    if (isset($payment->links->payout) && $payment->links->payout != null) {
      $payout = $payment->links->payout;

      // Check if payout exists or not
      createOrUpdatePayout($payout);
    }
    $update = $db->prepare("UPDATE `payments` SET `Status` = ?, `Payout` = ? WHERE `PMkey` = ?");
    $update->execute([$status, $payout, $PMkey]);
  } catch (Exception $e) {
    reportError($e);
    $sql2bool = false;
  }

  // Test failure condition
  // $status = "failed";
  if ($status == "paid_out") {
    try {
      $updatePP = $db->prepare("UPDATE `paymentsPending` SET `Status` = ? WHERE `PMkey` = ?");
      $updatePP->execute(['Paid', $PMkey]);
      $sql2bool = true;
    } catch (Exception $e) {
      reportError($e);
      $sql2bool = false;
    }
  } else if ($status == "failed") {
    $db = app()->db;
    try {
      $query = $db->prepare("SELECT payments.UserID, Name, Amount, Forename, Surname FROM payments INNER JOIN users ON payments.UserID = users.UserID WHERE PMkey = ?");
      $query->execute([$PMkey]);
      $details = $query->fetch(PDO::FETCH_ASSOC);

      $today = new DateTime('now', new DateTimeZone('Europe/London'));
      $today->add(new DateInterval('P10D'));
      $newDay = $today->format("Y-m-d");

      $query = $db->prepare("SELECT COUNT(*) FROM paymentRetries WHERE PMKey = ?");
      $query->execute([$PMkey]);
      $num_retries = $query->fetchColumn();

      // Check if the retry has been added to database
      $getCount = $db->prepare("SELECT COUNT(*) FROM paymentRetries WHERE UserID = ? AND `Day` = ? AND PMKey = ? AND Tried = ?");
      $getCount->execute([
        $details['UserID'],
        $newDay,
        $PMkey,
        0
      ]);

      if ($getCount->fetchColumn() == 0) {
        if ($num_retries < 3) {
          $query = $db->prepare("INSERT INTO paymentRetries (`UserID`, `Day`, `PMKey`, `Tried`) VALUES (?, ?, ?, ?)");
          $query->execute([$details['UserID'], $newDay, $PMkey, 0]);
        }

        $subject = "Payment Failed for " . $details['Name'];
        $message = '
        <p>Your Direct Debit payment of &pound;' . number_format($details['Amount'] / 100, 2, '.', '') . ', ' . $details['Name'] . ' has failed.</p>';
        if ($num_retries < 3) {
          $message .= '<p>We will automatically retry this payment on ' . htmlspecialchars($today->format("j F Y")) . ' (in ten days time).</p>';
          if ($num_retries < 2) {
            $message .= '<p>You don\'t need to take any action. Should this payment fail, we will retry the payment up to ' . (2 - $num_retries) . ' times.</p>';
          } else if ($num_retries == 2) {
            $message .= '<p>You don\'t need to take any action. Should this payment fail however, you will need to contact the club treasurer as we will have retried this direct debit payment 3 times.</p>';
          }
        } else {
          $message .= '<p>We have retried this payment request three times and it has still not succeeded. As a result, you will need to contact the club treasurer to take further action. Failure to pay may lead to the suspension or termination of your membership.</p>';
        }

        $message .= '<p>Kind regards,<br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';
        $query = $db->prepare("INSERT INTO notify (UserID, Status, Subject, Message, ForceSend, EmailType) VALUES (?, ?, ?, ?, ?, ?)");
        $query->execute([$details['UserID'], 'Queued', $subject, $message, 1, 'Payments']);
      }

      $sql2bool = true;
    } catch (Exception $e) {
      reportError($e);
      $sql2bool = false;
      echo "Failure in event process";
    }
  } else if ($status == "customer_approval_denied") {
    $db = app()->db;
    try {
      $query = $db->prepare("SELECT payments.UserID, Name, Amount, Forename, Surname FROM payments INNER JOIN users ON payments.UserID = users.UserID WHERE PMkey = ?");
      $query->execute([$PMkey]);
      $details = $query->fetch(PDO::FETCH_ASSOC);

      $subject = "Payment Failed for " . $details['Name'];
      $message = '
      <p>Your Direct Debit payment of £' . number_format($details['Amount'] / 100, 2, '.', '') . ', ' . $details['Name'] . ' has failed because customer approval was denied. This means your bank requires two people two authorise a direct debit mandate on your account and that this authorisation has not been given. You will be contacted by the treasurer to arrange payment.</p>
      <p>Kind regards,<br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';
      $query = $db->prepare("INSERT INTO notify (UserID, Status, Subject, Message, ForceSend, EmailType) VALUES (?, ?, ?, ?, ?, ?)");
      $query->execute([$details['UserID'], 'Queued', $subject, $message, 1, 'Payments']);

      $sql2bool = true;
    } catch (Exception $e) {
      reportError($e);
      $sql2bool = false;
    }
  } else if ($status == "charged_back") {
    $db = app()->db;
    try {
      $query = $db->prepare("SELECT payments.UserID, Name, Amount, Forename, Surname FROM payments INNER JOIN users ON payments.UserID = users.UserID WHERE PMkey = ?");
      $query->execute([$PMkey]);
      $details = $query->fetch(PDO::FETCH_ASSOC);

      $subject = $details['Name'] . " Charged Back";
      $message = '
      <p>Your Direct Debit payment of �' . number_format($details['Amount'] / 100, 2, '.', '') . ', ' . $details['Name'] . ' has been charged back to us. You will be contacted by the treasurer to arrange payment of any outstanding amount.</p>
      <p>Please note that fraudulently charging back a Direct Debit payment is a criminal offence, covered by the 2006 Fraud Act. We recommend that if your are unsure about the amount we are charging you, you should try and contact us first.</p>
      <p>Kind regards,<br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';
      $query = $db->prepare("INSERT INTO notify (UserID, Status, Subject, Message, ForceSend, EmailType) VALUES (?, ?, ?, ?, ?, ?)");
      $query->execute([$details['UserID'], 'Queued', $subject, $message, 1, 'Payments']);

      $sql2bool = true;
    } catch (Exception $e) {
      reportError($e);
      $sql2bool = false;
    }
  } else {
    $sql2bool = true;
  }
  if ($status == null) {
    return false;
  }
  if ($sql2bool) {
    return true;
  } else {
    return false;
  }
}

function paymentStatusString($status, $stripeFailureCode = null)
{
  switch ($status) {
    case "paid_out":
      return "Paid out";
    case "paid_manually":
      return "Paid manually";
    case "pending_customer_approval":
      return "Waiting for customer to approve payment";
    case "pending_submission":
      return "Payment created, pending submission to bank";
    case "submitted":
      return "Payment sent to bank";
    case "confirmed":
      return "Payment confirmed as collected";
    case "cancelled":
      return "Payment cancelled";
    case "customer_approval_denied":
      return "Customer has denied approval for the payment. Contact the customer directly";
    case "failed":
      return "Payment failed";
    case "charged_back":
      return "Payment charged back";
    case "cust_not_dd":
      return "Customer has no Direct Debit mandate";
    case "requires_payment_method": {
        switch ($stripeFailureCode) {
          case "account_closed":
            return "Bank account closed";
          case "bank_ownership_changed":
            return "Account transferred to new PSP";
          case "debit_not_authorized":
            return "Customer told bank payment was not authorised";
          case "generic_could_not_process":
            return "Payment could not be processed";
          case "insufficient_funds":
            return "Account has insufficient funds";
          case "invalid_account_number":
            return "Account number not valid: Not a GBP account or does not support BACS Direct Debit";
          default:
            return "Requires a Payment Method";
        }
      }
    case "requires_confirmation":
      return "Payment Intent requires confirmation";
    case "requires_action":
      return "Requires action, such as authentication";
    case "processing":
      return "Payment processing";
    case "succeeded":
      return "Payment successful";
    case "canceled":
      return "Payment cancelled";
    case "pending_api_request":
      return "Pending submission to service provider";
    default:
      return "Unknown Status Code";
  }
}

function bankDetails($user, $detail)
{
  $db = app()->db;
  $sql = $db->prepare("SELECT * FROM `paymentPreferredMandate` INNER JOIN `paymentMandates` ON paymentPreferredMandate.MandateID = paymentMandates.mandateID WHERE paymentPreferredMandate.UserID = ?;");
  $sql->execute([$user]);

  $row = $sql->fetch(PDO::FETCH_ASSOC);

  if ($row == null) {
    return "Unknown";
  }

  switch ($detail) {
    case "bank_name":
      return $row['BankName'];
    case "account_holder":
      return $row['AccountHolderName'];
    case "account_number_end":
      return $row['AccountNumEnd'];
    case "mandate":
      return $row['Mandate'];
    case "bank_account":
      return $row['BankAccount'];
    case "customer":
      return $row['Customer'];
    default:
      return "Unknown";
  }
}

function getUserName($user)
{
  $db = app()->db;
  $sql = $db->prepare("SELECT `Forename`, `Surname` FROM `users` WHERE `UserID` = ?;");
  $sql->execute([$user]);
  $row = $sql->fetch(PDO::FETCH_ASSOC);
  if ($row != null) {
    return $row['Forename'] . " " . $row['Surname'];
  }
  return false;
}

function getSwimmerName($swimmer)
{
  $db = app()->db;
  $sql = $db->prepare("SELECT `MForename`, `MSurname` FROM `members` WHERE `MemberID` = ?;");
  $row = $sql->fetch(PDO::FETCH_ASSOC);
  if ($row != null) {
    return $row['MForename'] . " " . $row['MSurname'];
  }
  return false;
}

function setupPhotoPermissions($id)
{
  $db = app()->db;
  try {
    $sql = $db->prepare("SELECT COUNT(*) FROM `memberPhotography` WHERE `MemberID` = ?;");
    $sql->execute([$id]);
    if ($sql->fetchColumn() == 0) {
      $sql = $db->prepare("INSERT INTO `memberPhotography` (`MemberID`, `Website`, `Social`, `Noticeboard`, `FilmTraining`, `ProPhoto`) VALUES (?, '0', '0', '0', '0', '0');");
      $sql->execute([$id]);
      return true;
    }
  } catch (Exception $e) {
    return false;
  }
  return false;
}

function setupMedicalInfo($id)
{
  $db = app()->db;
  try {
    $sql = $db->prepare("SELECT * FROM `memberMedical` WHERE `MemberID` = ?;");
    $sql->execute([$id]);
    if ($sql->fetchColumn() == 0) {
      $sql = $db->prepare("INSERT INTO `memberMedical` (`MemberID`, `Conditions`, `Allergies`, `Medication`) VALUES (?, '', '', '');");
      $sql->execute([$id]);
      return true;
    }
  } catch (Exception $e) {
    return false;
  }
  return false;
}

function ordinal($num)
{
  $ordinal = null;
  if ($num % 10 == 1) {
    $ordinal = "st";
  } else if ($num % 10 == 2) {
    $ordinal = "nd";
  } else if ($num % 10 == 3) {
    $ordinal = "rd";
  } else {
    $ordinal = "th";
  }
  return $num . $ordinal;
}

use Symfony\Component\DomCrawler\Crawler;

function curl($url)
{
  $ch = curl_init();  // Initialising cURL
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Origin: ' . app('request')->hostname));
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36');
  curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Setting cURL's option to return the webpage data
  $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
  $error = true;
  //pre(curl_getinfo($ch, CURLINFO_HTTP_CODE));
  if (!curl_errno($ch) && (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200' || curl_getinfo($ch, CURLINFO_HTTP_CODE) || '404' && curl_getinfo($ch, CURLINFO_HTTP_CODE) || '400')) {
    $error = false;
  }
  //pre($error);
  curl_close($ch);    // Closing cURL
  if (!$error) {
    return $data;   // Returning the data from the function
  } else {
    return false;
  }
}

function curl_scrape_between($data, $start, $end)
{
  //echo $data . "<br>";
  $data = stristr($data, $start); // Stripping all data from before $start
  //echo $data . "<br>";
  $data = substr($data, strlen($start));  // Stripping $start
  //echo $data . "<br>";
  $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
  //echo $stop . "<br>";
  $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
  //echo $data . "<br>";
  return $data;   // Returning the scraped data from the function
}

function getTimes($asa)
{
  $curlres =
    curl('https://cors-anywhere.herokuapp.com/https://www.swimmingresults.org/biogs/biogs_details.php?tiref=' . $asa);

  if ($curlres) {
    $start = '<table width="100%" style="page-break-before:always">';
    $end = '</table>';

    $output = curl_scrape_between($curlres, $start, $end);
    $output = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $output);
    $output = preg_replace('/(<[^>]+) width=".*?"/i', '$1', $output);

    $crawler = new Crawler($output);
    $crawler = $crawler->filter('tr > td');

    $array = ['Event', 'CY_SC', 'SCPB', 'CY_LC', 'LCPB'];
    $count = 0;

    foreach ($crawler as $domElement) {
      $col = $count % 5;
      if ($col == 0) {
        if ($domElement->textContent == "") {
          $array['Event'][] = null;
        } else {
          $array['Event'][] = trim($domElement->textContent);
        }
      } else if ($col == 1) {
        if ($domElement->textContent == "") {
          $array['CY_SC'][] = null;
        } else {
          $array['CY_SC'][] = trim($domElement->textContent);
        }
      } else if ($col == 2) {
        if ($domElement->textContent == "") {
          $array['SCPB'][] = null;
        } else {
          $array['SCPB'][] = trim($domElement->textContent);
        }
      } else if ($col == 3) {
        if ($domElement->textContent == "") {
          $array['CY_LC'][] = null;
        } else {
          $array['CY_LC'][] = trim($domElement->textContent);
        }
      } else if ($col == 4) {
        if ($domElement->textContent == "") {
          $array['LCPB'][] = null;
        } else {
          $array['LCPB'][] = trim($domElement->textContent);
        }
      }
      $count++;
    }

    return $array;
  }
  return false;
}

function user_needs_registration($user)
{
  $db = app()->db;

  // Check for legacy system
  try {
    $query = $db->prepare("SELECT RR FROM users WHERE UserID = ?");
    $query->execute([$user]);

    if ($query->fetchColumn()) {
      return true;
    } else {
      return false;
    }
  } catch (Exception $e) {
    return false;
  }

  // Check for pending onboarding sessions
  try {
    $query = $db->prepare("SELECT COUNT(*) FROM `onboardingSessions` WHERE `user` = :userId AND `type` = 'onboarding' AND (`status` = 'pending' OR `status` = 'in_progress') AND `start` <= :today AND `due_date` >= :today");
    $query->execute([
      'userId' => $user,
      'today' => (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d'),
    ]);

    return $query->fetchColumn() > 0;
  } catch (Exception $e) {
    return false;
  }

  return false;
}

function getPostContent($id)
{
  $db = app()->db;
  $sql = "SELECT `Content` FROM `posts` WHERE `ID` = ?";
  try {
    $query = $db->prepare($sql);
    $query->execute([$id]);
  } catch (PDOException $e) {
    halt(500);
  }

  $row = $query->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    return false;
  }

  $markdown = new ParsedownExtra();
  // Safe mode is disabled during the transition to markdown
  // $markdown->setSafeMode(true);

  return $markdown->text($row['Content']);
}

function isSubscribed($user, $email_type)
{
  $db = app()->db;
  $sql = "SELECT `Subscribed` FROM `users` LEFT JOIN `notifyOptions` ON `users`.`UserID` = `notifyOptions`.`UserID` WHERE (`users`.`UserID` = :user AND `EmailType` = :type) OR (`users`.`UserID` = :user AND `EmailType` IS NULL)";
  $array = [
    'user' => $user,
    'type' => $email_type
  ];
  try {
    $query = $db->prepare($sql);
    $query->execute($array);
  } catch (Exception $e) {
    halt(500);
  }
  $row = $query->fetchColumn();

  if ($row == null) {
    // Try user account info
    $sql = "SELECT `EmailComms` FROM `users` WHERE `UserID` = :user";
    $array = [
      'user' => $user
    ];
    try {
      $query = $db->prepare($sql);
      $query->execute($array);
    } catch (Exception $e) {
      halt(500);
    }
    $row = $query->fetchColumn();
    if ($row) {
      return true;
    }
  }

  if ($row) {
    return true;
  }

  return false;
}

function isAbsolutelySubscribed($user, $type)
{
  $db = app()->db;
  $tenant = app()->tenant;

  $get = $db->prepare("SELECT Subscribed FROM notifyOptions INNER JOIN users ON users.UserID = notifyOptions.UserID WHERE notifyOptions.UserID = ? AND Tenant = ? AND EmailType = ?");
  $get->execute([
    $user,
    $tenant->getId(),
    $type,
  ]);

  $result = $get->fetchColumn();

  if (!$result) return false;

  return bool($result);
}

function updateSubscription($post, $list, $user = null)
{
  $db = app()->db;
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
    $user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
  }
  $email = 0;
  $email_update = false;
  if ($post) {
    $email = 1;
  }

  if ($email != isSubscribed($user, $list)) {
    $email_update = true;
    $_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate'] = true;
  }

  $sql = "SELECT COUNT(*) FROM `notifyOptions` WHERE `UserID` = ? AND `EmailType` = ?";
  try {
    $query = $db->prepare($sql);
    $query->execute([$user, $list]);
  } catch (Exception $e) {
    halt(500);
  }
  if ($query->fetchColumn() == 0) {
    // INSERT
    $sql = "INSERT INTO `notifyOptions` (`UserID`, `EmailType`, `Subscribed`) VALUES (?, ?, ?)";
    try {
      $query = $db->prepare($sql);
      $query->execute([$user, $list, $email]);
    } catch (Exception $e) {
      halt(500);
    }
  } else {
    // UPDATE
    $sql = "UPDATE `notifyOptions` SET `Subscribed` = ? WHERE `UserID` = ? AND `EmailType` = ?";
    try {
      $query = $db->prepare($sql);
      $query->execute([$email, $user, $list]);
    } catch (Exception $e) {
      halt(500);
    }
  }
}

function getUserOption($userID, $option)
{
  $db = app()->db;
  $query = $db->prepare("SELECT `Value` FROM userOptions WHERE User = ? AND `Option` = ?");
  $query->execute([$userID, $option]);
  $result = $query->fetchColumn();

  if ($result == null || $result == "") {
    return null;
  }
  return $result;
}

function setUserOption($userID, $option, $value)
{
  if ($value == "") {
    $value = null;
  }
  try {
    $db = app()->db;
    $query = $db->prepare("SELECT COUNT(*) FROM userOptions WHERE User = ? AND `Option` = ?");
    $query->execute([$userID, $option]);
    $result = $query->fetchColumn();

    if ($result == 0) {
      $query = $db->prepare("INSERT INTO userOptions (`User`, `Option`, `Value`) VALUES (?, ?, ?)");
      $query->execute([$userID, $option, $value]);
    } else {
      $query = $db->prepare("UPDATE userOptions SET `Value` = ? WHERE User = ? AND `Option` = ?");
      $query->execute([$value, $userID, $option]);
    }
  } catch (Exception $e) {
    return false;
  }
  return true;
}

$count = 0;

/*
if ( (empty($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) || empty($_SESSION['TENANT-' . app()->tenant->getId()]['Username'])) && ($preventLoginRedirect != true)) {
  // Allow access to main page
  header("Location: " . autoUrl("login.php"));
}
elseif (((!empty($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) || (!empty($_SESSION['TENANT-' . app()->tenant->getId()]['Username']))) && ($preventLoginRedirect == true)) {
  // Don't show login etc if logged in
  header("Location: " . autoUrl(""));
}
*/

if (!function_exists('mb_ucfirst')) {
  function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false)
  {
    $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
    $str_end = "";
    if ($lower_str_end) {
      $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
    } else {
      $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
    }
    $str = $first_letter . $str_end;
    return $str;
  }
}

function helloGreeting()
{
  $date = new DateTime('now', new DateTimeZone('Europe/London'));
  $hour = (int) $date->format('H');
  if ($hour > 4 && $hour < 12) {
    return "Good Morning";
  } else if ($hour > 11 && $hour < 17) {
    return "Good Afternoon";
  } else if ($hour > 16 && $hour < 21) {
    return "Good Evening";
  } else {
    return "Good Night";
  }
}

function getCardFA($brand)
{
  if ($brand == 'visa') {
    return 'fa-cc-visa';
  } else if ($brand == 'mastercard') {
    return 'fa-cc-mastercard';
  } else if ($brand == 'amex') {
    return 'fa-cc-amex';
  } else if ($brand == 'diners') {
    return 'fa-cc-diners-club';
  } else if ($brand == 'discover') {
    return 'fa-cc-discover';
  } else if ($brand == 'jcb') {
    return 'fa-cc-jcb';
  } else if ($brand == 'unionpay') {
    return 'fa-cc-stripe';
  } else {
    return 'fa-cc-stripe';
  }
}

function getCardBrand($brand)
{
  if ($brand == 'visa') {
    return 'Visa';
  } else if ($brand == 'mastercard') {
    return 'Mastercard';
  } else if ($brand == 'amex') {
    return 'American Express';
  } else if ($brand == 'diners') {
    return 'Diners Club';
  } else if ($brand == 'discover') {
    return 'Discover';
  } else if ($brand == 'jcb') {
    return 'JCB';
  } else if ($brand == 'unionpay') {
    return 'UnionPay';
  } else {
    return 'Unknown Card';
  }
}

function createOrUpdatePayout($payout, $update = false)
{
  $db = app()->db;
  require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

  $client = SCDS\GoCardless\Client::get();

  $getCount = $db->prepare("SELECT COUNT(*) FROM paymentsPayouts WHERE ID = ?");
  $getCount->execute([
    $payout
  ]);
  $count = $getCount->fetchColumn() == 0;

  if ($count) {
    try {
      $payout = $client->payouts()->get($payout);

      $insert = $db->prepare("INSERT INTO paymentsPayouts (ID, Amount, Fees, Currency, ArrivalDate, Tenant) VALUES (?, ?, ?, ?, ?, ?)");
      $insert->execute([
        $payout->id,
        $payout->amount,
        $payout->deducted_fees,
        $payout->currency,
        $payout->arrival_date,
        app()->tenant->getId(),
      ]);
    } catch (Exception $e) {
      reportError($e);
      return false;
    }
  } else if ($count = 1 && $update) {
    try {
      $payout = $client->payouts()->get($payout);

      $insert = $db->prepare("UPDATE paymentsPayouts SET Amount = ?, Fees = ?, Currency = ?, ArrivalDate = ? WHERE ID = ?");
      $insert->execute([
        $payout->amount,
        $payout->deducted_fees,
        $payout->currency,
        $payout->arrival_date,
        $payout->id
      ]);
    } catch (Exception $e) {
      reportError($e);
      return false;
    }
  }
  return true;
}

function getSwimmerParent($member)
{
  $db = app()->db;
  $query = $db->prepare("SELECT UserID FROM members WHERE MemberID = ?");
  $query->execute([$member]);
  return $query->fetchColumn();
}

function stripeDirectDebit($absolute = false)
{
  if ($absolute) {
    return getenv('STRIPE') && app()->tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT');
  }
  return getenv('STRIPE') && app()->tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP') || app()->tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT');
}

function stripeSetUpDirectDebit()
{
  return getenv('STRIPE') && app()->tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP');
}

function getWalletName($name)
{
  if ($name == 'apple_pay') {
    return 'Apple Pay';
  } else if ($name == 'amex_express_checkout') {
    return 'Amex Express Checkout';
  } else if ($name == 'google_pay') {
    return 'Google Pay';
  } else if ($name == 'masterpass') {
    return 'Masterpass  ';
  } else if ($name == 'samsung_pay') {
    return 'Samsung Pay';
  } else if ($name == 'visa_checkout') {
    return 'Visa Checkout';
  } else {
    return 'Other wallet';
  }
}

function stripe_handleBalanceTransactionForFees($balanceTransaction)
{
  global $db;
  $update = $db->prepare("UPDATE stripePayments SET `Fees` = ? WHERE `Intent` = ?");

  try {
    $balanceTransaction = \Stripe\BalanceTransaction::retrieve([
      'id' => $balanceTransaction,
      'expand' => ['source'],
    ]);

    if (isset($balanceTransaction->source->payment_intent) && $balanceTransaction->source->payment_intent != null) {
      $update->execute([
        $balanceTransaction->fee,
        $balanceTransaction->source->payment_intent
      ]);
    }
  } catch (Exception $e) {
    reportError($e);
  }
}

function handleCompletedGalaPayments($paymentIntent, $onSession = false)
{
  \Stripe\Stripe::setApiKey(getenv('STRIPE'));
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
  $intent = \Stripe\PaymentIntent::retrieve(
    [
      'id' => $paymentIntent,
      'expand' => ['customer', 'payment_method']
    ],
    [
      'stripe_account' => app()->tenant->getStripeAccount()
    ]
  );

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
    $_SESSION['TENANT-' . app()->tenant->getId()]['CompletedEntryInfo'] = $databaseId;
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentIntent']);
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['PaidEntries']);
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentMethodID']);
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['AddNewCard']);

    $_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentSuccess'] = true;

    header("Location: " . autoUrl("galas/pay-for-entries/success"));
    return true;
  } else if ($onSession && $intent->status != 'succeeded') {
    header("Location: " . autoUrl("galas/pay-for-entries/checkout"));
    return false;
  }

  // Set fees if possible
  if (isset($intent->charges->data[0]->balance_transaction) && $intent->charges->data[0]->balance_transaction != null) {
    stripe_handleBalanceTransactionForFees($intent->charges->data[0]->balance_transaction);
  }

  // Get the user
  $getUser = $db->prepare("SELECT `User` FROM stripePayments WHERE Intent = ?");
  $getUser->execute([
    $intent->id
  ]);
  $userId = $getUser->fetchColumn();
  if ($userId == null) {
    $userId = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
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
        $intent->payment_method->id
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
          'payment_method' => $intent->payment_method->id,
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
      $pm = \Stripe\PaymentMethod::retrieve($method->id);

      $customerId = $customer->id;

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

      $getCardCount = $db->prepare("SELECT COUNT(*) FROM stripePayMethods WHERE MethodID = ?");
      $getCardCount->execute([
        $pm->id
      ]);

      $cardCount = $getCardCount->fetchColumn();

      if ($cardCount == 0) {
        // Work out if card fingerprint exists for user
        $getThisCardCount = $db->prepare("SELECT COUNT(*) FROM stripePayMethods WHERE Fingerprint = ? AND Customer = ? AND Reusable = '1'");
        $getThisCardCount->execute([
          $pm->card->fingerprint,
          $customerId
        ]);
        if ($getThisCardCount->fetchColumn() > 0) {
          $reuse = 0;
        }

        // Attach payment method to customer iff it's to be reused
        // Also only if we can't see it in the DB for this user
        // Otherwise we're saving loads of non reusable Apple Pay cards etc.
        if (bool($reuse) && (!isset($pm->customer) || $pm->customer == null)) {
          $pm->attach(['customer' => $customerId]);
        } else if (!isset($pm->customer) || $pm->customer == null) {
          $reuse = 0;
        }

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
        $_SESSION['TENANT-' . app()->tenant->getId()]['PayCardError'] = true;
        $_SESSION['TENANT-' . app()->tenant->getId()]['PayCardErrorMessage'] = $err;
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
    $addToStripePayments = $db->prepare("UPDATE stripePayments SET Method = ?, Amount = ?, Currency = ?, Paid = ?, AmountRefunded = ?, `DateTime` = ? WHERE Intent = ?");
    $addPaymentItems = $db->prepare("INSERT INTO stripePaymentItems (Payment, `Name`, `Description`, Amount, Currency, AmountRefunded) VALUES (?, ?, ?, ?, ?, ?)");
    $getEntries = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE StripePayment = ?");

    $getPaymentMethod = $db->prepare("SELECT ID FROM stripePayMethods WHERE MethodID = ?");
    $getPaymentMethod->execute([$intent->payment_method->id]);
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

    // Set the date to now
    $date = new DateTime('now', new DateTimeZone('UTC'));

    try {
      $addToStripePayments->execute([
        $paymentMethodId,
        $intent->amount,
        $intent->currency,
        true,
        0,
        $date->format('Y-m-d H:i:s'),
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
          \Brick\Math\BigDecimal::of((string) $entry['FeeToPay'])->withPointMovedRight(2)->toInt(),
          $intent->currency,
          0
        ]);
      }

      if ($pm == null) {
        $pm = \Stripe\PaymentMethod::retrieve($intent->payment_method->id);
      }

      if ($onSession) {
        $_SESSION['TENANT-' . app()->tenant->getId()]['CompletedEntryInfo'] = $databaseId;
      }

      $message = "<p>Here is your payment receipt for your gala entries.</p>";

      $message .= '<p>In accordance with card network rules, refunds for gala rejections will only be made to the payment card which was used.</p>';

      $message .= '<p>Should you wish to withdraw your swimmers you will need to contact the gala coordinator. Depending on the gala and host club, you may not be eligible for a refund in such circumstances unless you have a reason which can be evidenced, such as a doctors note..</p>';

      $getEntries->execute([$databaseId]);

      while ($entry = $getEntries->fetch(PDO::FETCH_ASSOC)) {
        $galaData = new GalaPrices($db, $entry['GalaID']);
        $count = 0;
        $message .= '<p>' . htmlspecialchars($entry['MForename'] . ' ' . $entry['MSurname']) . ' for ' . htmlspecialchars($entry['GalaName']) . '</p><ul>';
        foreach ($swimsArray as $colTitle => $text) {
          if ($entry[$colTitle]) {
            $count++;
            $pricingInfo = '';
            if ($galaData->getEvent($colTitle)->isEnabled()) {
              $pricingInfo = ', <em>&pound;' . $galaData->getEvent($colTitle)->getPriceAsString() . '</em>';
            }
            $message .= '<li>' . $text . $pricingInfo . '</li>';
          }
        }
        $message .= '</ul>';
      }

      $message .= '<p><strong>Total</strong> <br>&pound;' . htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $intent->amount))->withPointMovedLeft(2)->toScale(2)) . '</p><p><strong>Payment reference</strong> <br>SPM' . $databaseId . '</p>';

      if (isset($intent->charges->data[0]->payment_method_details->card) && $intent->charges->data[0]->payment_method_details->card != null) {
        $message .= '<p><strong>Card</strong> <br>' . getCardBrand($intent->charges->data[0]->payment_method_details->card->brand) . ' ' . $intent->charges->data[0]->payment_method_details->card->funding . ' card <br>&middot;&middot;&middot;&middot; &middot;&middot;&middot;&middot; &middot;&middot;&middot;&middot; ' . $intent->charges->data[0]->payment_method_details->card->last4 . '</p>';

        if (isset($intent->charges->data[0]->payment_method_details->card->wallet)) {
          $message .= '<p><strong>Mobile wallet</strong> <br>' . getWalletName($intent->charges->data[0]->payment_method_details->card->wallet->type) . '</p>';

          if (isset($intent->charges->data[0]->payment_method_details->card->wallet->dynamic_last4)) {
            $message .= '<p><strong>Device account number</strong> <br>&middot;&middot;&middot;&middot; &middot;&middot;&middot;&middot; &middot;&middot;&middot;&middot; ' . htmlspecialchars($intent->charges->data[0]->payment_method_details->card->wallet->dynamic_last4) . '</p>';
          }
        }
      }

      if (isset($intent->charges->data[0]->billing_details->address)) {
        $billingAddress = $intent->charges->data[0]->billing_details->address;

        $message .= '<p class="mb-0><strong>Billing address</strong></p>';

        $message .= '<address>';
        if (isset($intent->charges->data[0]->billing_details->name) && $intent->charges->data[0]->billing_details->name != null) {
          $message .= htmlspecialchars($intent->charges->data[0]->billing_details->name) . '<br>';
        }
        if (isset($billingAddress->line1) && $billingAddress->line1 != null) {
          $message .= htmlspecialchars($billingAddress->line1) . '<br>';
        }
        if (isset($billingAddress->line2) && $billingAddress->line2 != null) {
          $message .= htmlspecialchars($billingAddress->line2) . '<br>';
        }
        if (isset($billingAddress->postal_code) && $billingAddress->postal_code != null) {
          $message .= htmlspecialchars($billingAddress->postal_code) . '<br>';
        }
        if (isset($billingAddress->state) && $billingAddress->state != null) {
          $message .= htmlspecialchars($billingAddress->state) . '<br>';
        }
        if (isset($billingAddress->country) && $billingAddress->country != null) {
          $message .= htmlspecialchars($billingAddress->country);
        }
        $message .= '</address>';
      }

      $emailDb = $db->prepare("INSERT INTO notify (`UserID`, `Status`, `Subject`, `Message`, `ForceSend`, `EmailType`) VALUES (?, ?, ?, ?, 1, 'Payments')");

      $email = $name = '';
      if (isset($intent->charges->data[0]->billing_details->email)) {
        $email = $intent->charges->data[0]->billing_details->email;
        $name = $user['Forename'] . ' ' . $user['Surname'];
        if (isset($intent->charges->data[0]->billing_details->name)) {
          $name = $intent->charges->data[0]->billing_details->name;
        }
      } else {
        $email = $user['EmailAddress'];
        $name = $user['Forename'] . ' ' . $user['Surname'];
      }
      $sendingEmail = "noreply@" . getenv('EMAIL_DOMAIN');
      notifySend(null, 'Payment Receipt', $message, $name, $email, [
        "Email" => $sendingEmail,
        "Name" => app()->tenant->getKey('CLUB_NAME'),
        "Unsub" => [
          "Allowed" => false,
          "User" => $userId,
          "List" =>  "Payments"
        ]
      ]);
      $emailDb->execute([
        $userId,
        'Sent',
        'Payment Receipt',
        $message
      ]);

      $db->commit();

      if ($onSession) {
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentIntent']);
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['PaidEntries']);
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentMethodID']);
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['AddNewCard']);

        $_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentSuccess'] = true;

        header("Location: " . autoUrl("galas/pay-for-entries/success"));
      } else {
        return true;
      }
    } catch (Exception $e) {
      reportError($e);
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

function isOnS3($asset)
{
  if (substr($asset, 0, 5) === "X-S3:") return true;
  return false;
}

function getS3Key($asset)
{
  return ltrim(substr($asset, 5), '/');
}

function getUploadedAssetUrl($asset, $public = false)
{
  // return $asset;

  if (substr($asset, 0, 5) === "X-S3:") {
    $asset = ltrim(substr($asset, 5), '/');
    if (getenv('AWS_CLOUDFRONT_ROOT')) {
      return getenv('AWS_CLOUDFRONT_ROOT') . $asset;
    } else if (getenv('AWS_S3_REGION') && getenv('AWS_S3_BUCKET')) {
      return 'https://' . getenv('AWS_S3_BUCKET') . '.s3.' . getenv('AWS_S3_REGION') . '.amazonaws.com/' . $asset;
    }
  }

  if ($public) autoUrl('public/' . $asset);

  return autoUrl($asset);
}

function getCompiledAsset($filename)
{
  $dirName = dirname($filename); // if we have a file name like dir1/dir2/style.css
  $fileBaseName = pathinfo($filename, PATHINFO_FILENAME); // Just the file name
  $fileExt = pathinfo($filename, PATHINFO_EXTENSION); // Just the file extension

  // $assetType handles the asset folder inside the dist folder
  switch ($fileExt) {
    case 'js':
      $assetType = 'js'; // dir/scripts
      break;
    case 'png':
    case 'jpg':
    case 'gif':
    case 'ico':
      $assetType = 'img'; // dir/images
      break;
    default:
      $assetType = 'css'; // dir/styles
  }

  // if the file is inside another folder inside "dist" folder
  if ($dirName != '.') {
    $handler = "compiled/$assetType/$dirName/";
    $externalHandler = "compiled/$assetType/$dirName/"; // for viewing it. e.g: http://example.com/dist/styles/style1/main.css
  } else {
    $handler = "compiled/$assetType/";
    $externalHandler = "compiled/$assetType/"; // for viewing it. e.g: http://example.com/dist/styles/main.css
  }

  $openHandler = opendir($handler);
  while ($file = readdir($openHandler)) {
    if ($file !== '.' && $file !== '..') {
      if (preg_match("/^" . $fileBaseName . "(.\w+)?." . $fileExt . "/i", $file, $name)) {
        return $externalHandler . $name[0];
      }
    }
  }
  closedir($openHandler);
}

/**
 * Returns an IP address, checking for proxy/LB headers
 */
function getUserIp()
{
  if (isset($_SERVER['CF-Connecting-IP'])) {
    return $_SERVER['CF-Connecting-IP'];
  }

  if (isset($_SERVER['REMOTE_ADDR'])) {
    return $_SERVER['REMOTE_ADDR'];
  }

  return '';
}

include BASE_PATH . 'includes/ErrorReporting.php';
include BASE_PATH . 'includes/Colours.php';
include BASE_PATH . 'includes/direct-debit/BankLogos.php';
include BASE_PATH . 'includes/direct-debit/BankNames.php';
include BASE_PATH . 'includes/direct-debit/Balances.php';
// include BASE_PATH . 'includes/membership-fees/Loader.php';
include BASE_PATH . 'includes/pdf/StringReplacements.php';
include BASE_PATH . 'includes/GetCachedFile.php';
include BASE_PATH . 'includes/BankHolidays.php';
include BASE_PATH . 'includes/GetContrastColour.php';
include BASE_PATH . 'includes/CoachTypes.php';
// require BASE_PATH . 'helperclasses/Components/Footer.php';
// require BASE_PATH . 'helperclasses/Components/RootFooter.php';
// require BASE_PATH . 'helperclasses/Components/RenewalProgressListGroup.php';
