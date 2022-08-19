<?php

$_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'] = $_POST;

$db = app()->db;
$tenant = app()->tenant;

$sendingCategory = 'Notify';

$client = new Aws\S3\S3Client([
  'version'     => 'latest',
  'region'      => getenv('AWS_S3_REGION'),
  'visibility' => 'private',
]);
$adapter = new League\Flysystem\AwsS3V3\AwsS3V3Adapter(
  // S3Client
  $client,
  // Bucket name
  getenv('AWS_S3_BUCKET'),
  // Optional path prefix
  '',
  // Visibility converter (League\Flysystem\AwsS3V3\VisibilityConverter)
  new League\Flysystem\AwsS3V3\PortableVisibilityConverter(
    // Optional default for directories
    League\Flysystem\Visibility::PRIVATE // or ::PRIVATE
  )
);

// The FilesystemOperator
$filesystem = new League\Flysystem\Filesystem($adapter);

$db->beginTransaction();

try {
  if (sizeof($_POST) == 0) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError'] = true;
    throw new Exception('Filesize TooLargeError');
  }

  if (!SCDS\FormIdempotency::verify()) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['FormError'] = true;
    throw new Exception('Form idempotency error');
  }

  if (!SCDS\CSRF::verify()) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['FormError'] = true;
    throw new Exception('Form CSRF error');
  }

  $replyAddress = getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 'NotifyReplyAddress');

  $to_remove = [
    "<p>&nbsp;</p>",
    "<p></p>",
    "<p> </p>",
    "\r",
    "\n",
    '<div dir="auto">&nbsp;</div>',
    '&nbsp;'
  ];

  // Handle attachments early doors
  $attachments = [];
  $collectiveSize = 0;

  $attachmentsList = json_decode($_POST['email-attachments']);
  for ($i = 0; $i < sizeof($attachmentsList); $i++) {
    try {
      if ($filesystem->fileExists($attachmentsList[$i]->s3_key)) {
        $file = $filesystem->read($attachmentsList[$i]->s3_key);
        $filesize = $filesystem->filesize($attachmentsList[$i]->s3_key);
        $mimetype = $filesystem->mimeType($attachmentsList[$i]->s3_key);

        $collectiveSize += $_FILES['file-upload']['size'][$i];
        $attachments[] = [
          'encoded' => base64_encode($file),
          'mime' => $mimetype,
          'filename' => $attachmentsList[$i]->filename,
          'disposition' => 'attachment',
          'store_name' => $attachmentsList[$i]->s3_key,
          'directory' => null,
          'url' => $attachmentsList[$i]->url,
          'uploaded' => true,
        ];
      }
    } catch (League\Flysystem\FilesystemException | League\Flysystem\UnableToReadFile | \Exception $e) {
      // handle the error
      reportError($e);
    }
  }

  // Handle attachments early doors
  // $attachments = [];
  // $collectiveSize = 0;
  // for ($i = 0; $i < sizeof($_FILES['file-upload']['tmp_name']); $i++) {
  //   if (is_uploaded_file($_FILES['file-upload']['tmp_name'][$i])) {

  //     if (bool($_FILES['file-upload']['error'][$i])) {
  //       // Error
  //       // reportError($_FILES['file-upload']['error'][$i]);
  //       if ($_FILES['file-upload']['error'][$i] == 2) {
  //         // Too large
  //         $_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError'] = true;
  //       } else {
  //         $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
  //       }
  //       throw new Exception();
  //     } else if (false/*$_FILES['file-upload']['type'][$i] != 'text/plain' && $_FILES['file-upload']['type'][$i] != 'application/octet-stream'*/) {
  //       // Probably not a text file
  //       reportError($_FILES['file-upload']['type'][$i]);
  //       $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
  //       throw new Exception();
  //     } else if ($_FILES['file-upload']['size'][$i] > 10485760) {
  //       // Too large, stop
  //       // reportError($_FILES['file-upload']['size'][$i]);
  //       $_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError'] = true;
  //       throw new Exception();
  //     } else if ($_FILES['file-upload']['size'][$i] > 0) {
  //       // Store uploaded files in filestore, if exists
  //       if ($rootFilePath = $tenant->getFilePath()) {
  //         // Work out filename for upload
  //         $date = new DateTime('now', new DateTimeZone('Europe/London'));
  //         $urlPath = 'notify/attachments/' . $date->format("Y/m/d") . '/';
  //         $s3Path = $tenant->getId() . '/' . $urlPath;

  //         $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();
  //         $filename = $uuid . '-' . preg_replace('@[^0-9a-z\.]+@i', '-', basename($_FILES['file-upload']['name'][$i]));

  //         $filenamePath = $s3Path . $filename;
  //         $url = $urlPath . $filename;
  //       }

  //       $collectiveSize += $_FILES['file-upload']['size'][$i];
  //       $attachments[] = [
  //         'encoded' => base64_encode(file_get_contents($_FILES['file-upload']['tmp_name'][$i])),
  //         'mime' => mime_content_type($_FILES['file-upload']['tmp_name'][$i]),
  //         'filename' => $_FILES['file-upload']['name'][$i],
  //         'disposition' => 'attachment',
  //         'tmp_name' => $_FILES['file-upload']['tmp_name'][$i],
  //         'store_name' => $filenamePath,
  //         'directory' => $path,
  //         'url' => $url,
  //         'uploaded' => false,
  //       ];
  //     } else {
  //       // File upload error (no size)
  //       reportError($_FILES);
  //       $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
  //       throw new Exception();
  //     }
  //   }
  // }

  if ($collectiveSize > 10485760) {
    // Collectively too large attachments
    $_SESSION['TENANT-' . app()->tenant->getId()]['CollectiveSizeTooLargeError'] = true;
    throw new Exception();
  }

  if (false && getenv('AWS_S3_BUCKET')) {
    for ($i = 0; $i < sizeof($attachments); $i++) {

      try {
        $filesystem->write($attachments[$i]['store_name'], file_get_contents($attachments[$i]['tmp_name']), ['visibility' => 'private']);
        $attachments[$i]['uploaded'] = true;
      } catch (League\Flysystem\FilesystemException | League\Flysystem\UnableToWriteFile $exception) {
      }

      // if (!is_writeable($attachments[$i]['store_name'])) {
      // Try making folders
      // $dir = explode('/', $attachments[$i]['store_name']);
      // $path = "";
      // $tried = [];
      // for ($y = 0; $y < sizeof($dir) - 1; $y++) {
      //   $path .= $dir[$y];
      //   if (!is_dir($path)) {
      //     mkdir($path);
      //     $tried[] = $path;
      //   }
      //   $path .= '/';
      // }
      //   if (!is_dir($attachments[$i]['directory'])) {
      //     mkdir($attachments[$i]['directory'], 0755, true);
      //   }
      //   if (!is_writeable($path)) {
      //     // reportError([$tried, $path, $attachments[$i]['store_name']]);
      //   } else {
      //     // reportError([$tried, $path, $attachments[$i]['store_name']]);
      //   }
      // } else {
      //   // reportError("Filepath is writeable");
      // }
      // if (move_uploaded_file($attachments[$i]['tmp_name'], $attachments[$i]['store_name'])) {
      //   $attachments[$i]['uploaded'] = true;
      // } else {
      //   // reportError([$attachments[$i]['tmp_name'], $attachments[$i]['store_name'], $_FILES['file-upload']]);
      // }
    }
  }

  // reportError($attachments);

  if (isset($_POST['subscription-category']) && $_POST['subscription-category'] != 'DEFAULT') {
    // Check exists
    $checkCategory = $db->prepare("SELECT COUNT(*) FROM `notifyCategories` WHERE `ID` = ? AND `Tenant` = ? AND Active");
    $checkCategory->execute([
      $_POST['subscription-category'],
      $tenant->getId(),
    ]);

    if ($check->fetchColumn() != 1) throw new Exception('No subscription category');

    $sendingCategory = $_POST['subscription-category'];
  }

  $subject = trim(str_replace('!', '', str_replace('*', '', $_POST['subject'])));
  $message = str_replace($to_remove, "", $_POST['message']);
  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Admin" && !($replyAddress && isset($_POST['ReplyToMe']) && bool($_POST['ReplyToMe']))) {
    $name = getUserName($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']);
    $message .= '<p class="small text-muted">Sent by ' . $name . '. Reply to this email to contact our Enquiries Team who can pass your message on to ' . $name . '.</p>';
  }
  $force = 0;
  $sender = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
  if (isset($_POST['force']) && bool($_POST['force']) && ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Galas")) {
    $force = 1;
  }

  $coachSend = false;
  if (isset($_POST['coach-send']) && bool($_POST['coach-send'])) {
    $coachSend = true;
  }

  $getCoaches = $db->prepare("SELECT User FROM coaches WHERE Squad = ?");

  $squads = null;
  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') {
    $squads = $db->prepare("SELECT `SquadName`, `SquadID` FROM `squads` WHERE Tenant = ? ORDER BY `SquadFee` DESC, `SquadName` ASC;");
    $squads->execute([
      $tenant->getId()
    ]);
  } else {
    $squads = $db->prepare("SELECT `SquadName`, `SquadID` FROM `squads` INNER JOIN squadReps ON squadReps.Squad = squads.SquadID WHERE squadReps.User = ? ORDER BY `SquadFee` DESC, `SquadName` ASC;");
    $squads->execute([
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
    ]);
  }
  $row = $squads->fetchAll(PDO::FETCH_ASSOC);

  $lists = null;
  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') {
    $lists = $db->prepare("SELECT targetedLists.ID, targetedLists.Name FROM `targetedLists` WHERE Tenant = ? ORDER BY `Name` ASC;");
    $lists->execute([
      $tenant->getId()
    ]);
  } else {
    $lists = $db->prepare("SELECT targetedLists.ID, targetedLists.Name FROM `targetedLists` INNER JOIN listSenders ON listSenders.List = targetedLists.ID WHERE listSenders.User = ? ORDER BY `Name` ASC;");
    $lists->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  }
  $lists = $lists->fetchAll(PDO::FETCH_ASSOC);

  $galas = $db->prepare("SELECT GalaName, GalaID FROM `galas` WHERE GalaDate >= ? AND Tenant = ? ORDER BY `GalaName` ASC;");
  $date = new DateTime('-1 week', new DateTimeZone('Europe/London'));
  $galas->execute([
    $date->format('Y-m-d'),
    $tenant->getId()
  ]);

  $query = $squadsQuery = $listsQuery = $galaQuery = "";

  $squads = $listsArray = $galasArray = [];

  $toSendTo = [];

  for ($i = 0; $i < sizeof($row); $i++) {
    if ($squadsQuery != "" && $_POST[$row[$i]['SquadID']] == 1) {
      $squadsQuery .= "OR";
    }
    if ($_POST[$row[$i]['SquadID']] == 1) {
      $squadsQuery .= " `Squad` = '" . $row[$i]['SquadID'] . "' ";
      $squads[$row[$i]['SquadID']] = $row[$i]['SquadName'];

      if ($coachSend) {
        // Get coaches
        $getCoaches->execute([
          $row[$i]['SquadID']
        ]);

        while ($coach = $getCoaches->fetchColumn()) {
          $toSendTo[$coach] = $coach;
        }
      }
    }
  }

  for ($i = 0; $i < sizeof($lists); $i++) {
    if ($listsQuery != "" && $_POST["TL-" . $lists[$i]['ID']] == 1) {
      $listsQuery .= "OR";
    }
    if ($_POST["TL-" . $lists[$i]['ID']] == 1) {
      $id = "TL-" . $lists[$i]['ID'];
      $id = substr_replace($id, '', 0, 3);
      $listsQuery .= " `ListID` = '" . $lists[$i]['ID'] . "' ";
      $listsArray[$lists[$i]['ID']] = $lists[$i]['Name'];
    }
  }

  while ($gala = $galas->fetch(PDO::FETCH_ASSOC)) {
    if ($galaQuery != "" && $_POST["GALA-" . $gala['GalaID']]) {
      $galaQuery .= "OR";
    }
    if ($_POST["GALA-" . $gala['GalaID']]) {
      $id = "TL-" . $lists[$i]['ID'];
      $id = substr_replace($id, '', 0, 3);
      $galaQuery .= " `GalaID` = '" . $gala['GalaID'] . "' ";
      $galasArray[$gala['GalaID']] = $gala['GalaName'];
    }
  }

  $squadUsers = $listUsers = $galaUsers = null;

  if ($squadsQuery) {
    $squadUsers = $db->query("SELECT UserID FROM members INNER JOIN squadMembers ON members.MemberID = squadMembers.Member WHERE (" . $squadsQuery . ") AND UserID IS NOT NULL");
    while ($u = $squadUsers->fetch(PDO::FETCH_ASSOC)) {
      $toSendTo[$u['UserID']] = $u['UserID'];
    }
  }
  if ($listsQuery) {
    $listUsers = $db->query("SELECT members.UserID FROM members INNER JOIN `targetedListMembers` ON targetedListMembers.ReferenceID = members.MemberID WHERE (" . $listsQuery . ") AND ReferenceType = 'Member' AND UserID IS NOT NULL");
    while ($u = $listUsers->fetch(PDO::FETCH_ASSOC)) {
      $toSendTo[$u['UserID']] = $u['UserID'];
    }

    $listUsers = $db->query("SELECT users.UserID FROM users INNER JOIN `targetedListMembers` ON targetedListMembers.ReferenceID = users.UserID WHERE (" . $listsQuery . ") AND ReferenceType = 'User'");
    while ($u = $listUsers->fetch(PDO::FETCH_ASSOC)) {
      $toSendTo[$u['UserID']] = $u['UserID'];
    }
  }
  if ($galaQuery && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') {
    $galaUsers = $db->query("SELECT users.UserID FROM ((`users` INNER JOIN `members` ON members.UserID = users.UserID) INNER JOIN `galaEntries` ON galaEntries.MemberID = members.MemberID) WHERE " . $galaQuery);
    while ($u = $galaUsers->fetch(PDO::FETCH_ASSOC)) {
      $toSendTo[$u['UserID']] = $u['UserID'];
    }
  }
  $date = new DateTime('now', new DateTimeZone('Europe/London'));
  $renewals = $db->prepare("SELECT `ID` FROM `renewals` WHERE `StartDate` <= :today AND `EndDate` >= :today AND `Tenant` = :tenant");
  $renewals->execute([
    'tenant' => $tenant->getId(),
    'today' => $date->format("Y-m-d")
  ]);
  $renewal = $renewals->fetchColumn();
  if ($renewal) {
    if (isset($_POST['pending-renewal']) && bool($_POST['pending-renewal'])) {
      // Get those pending a renewal
      $sql = $db->prepare("SELECT `UserID` FROM `renewalMembers` INNER JOIN `members` ON members.MemberID = renewalMembers.MemberID WHERE members.Active AND renewalMembers.RenewalID = ? AND NOT renewalMembers.Renewed AND `UserID` IS NOT NULL;");
      $sql->execute([$renewal]);
      while ($u = $sql->fetchColumn()) {
        $toSendTo[$u] = $u;
      }
    }
    if (isset($_POST['completed-renewal']) && bool($_POST['completed-renewal'])) {
      // Get those who have completed renewal
      $sql = $db->prepare("SELECT `UserID` FROM `renewalMembers` INNER JOIN `members` ON members.MemberID = renewalMembers.MemberID WHERE members.Active AND renewalMembers.RenewalID = ? AND renewalMembers.Renewed AND `UserID` IS NOT NULL;");
      $sql->execute([$renewal]);
      while ($u = $sql->fetchColumn()) {
        $toSendTo[$u] = $u;
      }
    }
  }

  $userSending = getUserName($sender);

  $recipientGroups = [
    "Sender" => [
      "ID" => $sender,
      "Name" => $userSending
    ],
    "To" => [
      "Squads" => $squads,
      "Targeted_Lists" => $listsArray,
      "Galas" => $galasArray
    ],
    "Message" => [
      "Subject" => $subject,
      "Body" => $message
    ],
    "Metadata" => [
      "ForceSend" => $force
    ],
  ];

  if ($_POST['from'] == "current-user") {
    $senderNames = explode(' ', $userSending);
    $fromEmail = "";
    for ($i = 0; $i < sizeof($senderNames); $i++) {
      $fromEmail .= urlencode(strtolower($senderNames[$i]));
      if ($i < sizeof($senderNames) - 1) {
        $fromEmail .= '.';
      }
    }

    if (!app()->tenant->isCLS()) {
      $fromEmail .= '.' . urlencode(strtolower(str_replace(' ', '', app()->tenant->getKey("ASA_CLUB_CODE"))));
    }

    $fromEmail .= '@' . getenv('EMAIL_DOMAIN');

    $recipientGroups["NamedSender"] = [
      "Email" => $fromEmail,
      "Name" => $userSending
    ];
  }

  if ($replyAddress && isset($_POST['ReplyToMe']) && bool($_POST['ReplyToMe'])) {
    $recipientGroups["ReplyToMe"] = [
      "Email" => $replyAddress,
      "Name" => $_SESSION['TENANT-' . app()->tenant->getId()]['Forename'] . ' ' . $_SESSION['TENANT-' . app()->tenant->getId()]['Surname'],
    ];
  }

  // reportError($attachments);
  if (sizeof($attachments) > 0) {
    $recipientGroups["Attachments"] = [];
  }
  foreach ($attachments as $attachment) {
    if ($attachment['uploaded']) {
      $recipientGroups["Attachments"][] = [
        'Filename' => $attachment['filename'],
        'URI' => $attachment['url'],
        'MIME' => $attachment['mime'],
      ];
    }
  }

  $json = json_encode($recipientGroups);
  $date = new DateTime('now', new DateTimeZone('UTC'));
  $dbDate = $date->format('Y-m-d H:i:s');

  $sql = "INSERT INTO `notifyHistory` (`Sender`, `Subject`, `Message`,
  `ForceSend`, `Date`, `JSONData`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $pdo_query = $db->prepare($sql);
  $pdo_query->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
    $subject,
    $message,
    $force,
    $dbDate,
    $json,
    $tenant->getId()
  ]);

  $id = $db->lastInsertId();

  $count = sizeof($toSendTo);

  $insert = $db->prepare("INSERT INTO `notify` (`UserID`, `MessageID`, `Subject`, `Message`, `Status`, `Sender`, `ForceSend`, `EmailType`) VALUES (?, ?, ?, ?, 'Sent', ?, ?, 'Notify')");

  foreach ($toSendTo as $userid => $user) {
    try {
      $insert->execute([$userid, $id, null, null, $sender, $force]);
    } catch (PDOException $e) {
      reportError($e);
    }
  }

  // if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Admin" && $force == 1) {
  //   $sql = "SELECT `UserID` FROM `users` INNER JOIN `permissions` ON users.UserID = `permissions`.`User` WHERE `Permission` = 'Admin'";
  //   try {
  //     $pdo_query = $db->prepare($sql);
  //     $pdo_query->execute([$userid, $id, $subject, $message, $sender, $force]);
  //   } catch (PDOException $e) {
  //     reportError($e);
  //   }

  //   try {
  //     $sendToTeam = $db->prepare("INSERT INTO `notify` (`UserID`, `MessageID`, `Subject`, `Message`, `Status`, `Sender`, `ForceSend`, `EmailType`) VALUES (?, ?, ?, ?, 'Queued', ?, ?, 'Notify-Audit')");
  //   } catch (PDOException $e) {
  //     reportError($e);
  //   }

  //   $gdpr_question = '<p>You have force sent the below message. Please contact <a href="mailto:gdpr@chesterlestreetasc.co.uk">gdpr@chesterlestreetasc.co.uk</a> to explain the rationale for using <strong>Force Send</strong> for this email.</p><hr>' . $message . '<p class="small text-muted">Sent via Notify, our custom built email notification service.</p>';
  //   $sendToTeam->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], null, "GDPR Compliance: " . $subject, $gdpr_question, $sender, $force]);

  //   $intro = '
  //   <p>We\'re sending you this email because you\'re an administrator at ' . app()->tenant->getKey('CLUB_NAME') . '.</p>
  //   <p>' . getUserName($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) . ' has force sent the following email, overriding parent subscription options. We send these updates about emails which have been force sent in order to ensure compliance with GDPR rules.</p>
  //   <p>Emails should only be force sent when they are of high importance. An example would be to inform parents of a session cancellation.</p>
  //   <hr>';
  //   $message_admin = $intro . $message . '<p class="small text-muted">Sent via Notify, our custom built email notification service.</p>';

  //   $row = $pdo_query->fetchAll(PDO::FETCH_ASSOC);
  //   for ($i = 0; $i < sizeof($row); $i++) {
  //     try {
  //       $sendToTeam->execute([$row[$i]['UserID'], null, "GDPR Alert: " . $subject, $message_admin, $sender, $force]);
  //     } catch (PDOException $e) {
  //       reportError($e);
  //     }
  //   }
  // }

  $_SESSION['TENANT-' . app()->tenant->getId()]['NotifySuccess'] = [
    "Count" => $count,
    "Force" => $force
  ];

  $db = app()->db;
  $getExtraEmails = $db->prepare("SELECT Name, EmailAddress, ID FROM notifyAdditionalEmails WHERE UserID = ? AND Verified = '1'");

  $getPendingGroupMail = $db->prepare("SELECT ID, notifyHistory.Subject, notifyHistory.Message, notifyHistory.ForceSend, notifyHistory.JSONData FROM notifyHistory WHERE ID = ?");
  $getPendingGroupMail->execute([$id]);

  $getUsersForEmail = $db->prepare("SELECT Forename, Surname, EmailAddress, notify.UserID, EmailID FROM notify INNER JOIN users ON notify.UserID = users.UserID WHERE MessageID = ?");

  // Completed It PDO Object
  // $completed = $db->prepare("UPDATE `notify` SET `Status` = ? WHERE `EmailID` = ?");

  while ($currentMessage = $getPendingGroupMail->fetch(PDO::FETCH_ASSOC)) {
    $getUsersForEmail->execute([$currentMessage['ID']]);

    $jsonData = json_decode($currentMessage['JSONData']);

    $mailObject = new \CLSASC\SuperMailer\CreateMail();
    $mailObject->setHtmlContent($currentMessage['Message']);

    $mailObject->showName();
    if (!$currentMessage['ForceSend']) {
      $mailObject->setUnsubscribable();
    }

    $from = new \SendGrid\Mail\From("noreply@" . getenv('EMAIL_DOMAIN'), app()->tenant->getKey('CLUB_NAME'));
    if ($jsonData->NamedSender->Email != null && $jsonData->NamedSender->Name) {
      $from = new \SendGrid\Mail\From("noreply@" . getenv('EMAIL_DOMAIN'), $jsonData->NamedSender->Name);
    }
    $tos = [];
    while ($user = $getUsersForEmail->fetch(PDO::FETCH_ASSOC)) {
      if (($sendingCategory == 'Notify' && ($currentMessage['ForceSend'] || isSubscribed($user['UserID'], $sendingCategory))) || ($sendingCategory != 'Notify' && ($currentMessage['ForceSend'] || isAbsolutelySubscribed($user['UserID'], $sendingCategory)))) {
        $tos[] = new \SendGrid\Mail\To(
          $user['EmailAddress'],
          $user['Forename'] . ' ' . $user['Surname'],
          [
            '-name-' => $user['Forename'] . ' ' . $user['Surname'],
            '-unsub_link-' => autoUrl("notify/unsubscribe/" . dechex($user['UserID']) .  "/" . urlencode($user['EmailAddress']) . "/Notify")
          ]
        );
        if ($sendingCategory == 'Notify') {
          $getExtraEmails->execute([$user['UserID']]);
          while ($extraEmails = $getExtraEmails->fetch(PDO::FETCH_ASSOC)) {
            $tos[] = new \SendGrid\Mail\To(
              $extraEmails['EmailAddress'],
              $extraEmails['Name'],
              [
                '-name-' => $extraEmails['Name'],
                '-unsub_link-' => autoUrl("cc/" . dechex($extraEmails['ID']) .  "/" . hash('sha256', $extraEmails['ID']) . "/unsubscribe")
              ]
            );
            $ccEmails[$extraEmails['EmailAddress']] = $extraEmails['Name'];
          }
        }
        // $completed->execute(['Sent', $user['EmailID']]);
      } else {
        // $completed->execute(['No_Sub', $user['EmailID']]);
      }
    }
    $subject = $currentMessage['Subject'];
    $globalSubstitutions = [];
    $plain_text = $mailObject->getFormattedPlain();
    //$plain_text = str_replace(';', '', $plain_text);
    $plainTextContent = new \SendGrid\Mail\PlainTextContent($plain_text);
    $htmlContent = new \SendGrid\Mail\HtmlContent($mailObject->getFormattedHtml());

    $email = new \SendGrid\Mail\Mail(
      $from,
      $tos,
      $subject,
      $plainTextContent,
      $htmlContent,
      $globalSubstitutions
    );

    foreach ($attachments as $attachment) {
      $email->addAttachment(
        $attachment['encoded'],
        $attachment['mime'],
        $attachment['filename'],
        $attachment['disposition']
      );
    }

    if ($jsonData->ReplyToMe->Email != null && $jsonData->ReplyToMe->Name != null) {
      try {
        $email->setReplyTo($jsonData->ReplyToMe->Email, $jsonData->ReplyToMe->Name);
      } catch (Exception $e) {
        $email->setReplyTo(app()->tenant->getKey('CLUB_EMAIL'), app()->tenant->getKey('CLUB_NAME') . ' Enquiries');
      }
    } else {
      $email->setReplyTo(app()->tenant->getKey('CLUB_EMAIL'), app()->tenant->getKey('CLUB_NAME') . ' Enquiries');
    }

    $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
    $response = $sendgrid->send($email);

    AuditLog::new('Notify-SentGroup', 'Sent email ' . $id);
  }
  $db->commit();

  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'])) {
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData']);
  }

  header("Location: " . autoUrl("notify"));
} catch (Exception $e) {
  $db->rollback();
  header("Location: " . autoUrl("notify/new"));
}
