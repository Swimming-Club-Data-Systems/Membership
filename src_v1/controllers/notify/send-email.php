<?php

header("content-type: application/json");

$data = json_decode(file_get_contents('php://input'));

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
  // if (!SCDS\FormIdempotency::verify()) {
  //   $_SESSION['TENANT-' . app()->tenant->getId()]['FormError'] = true;
  //   throw new Exception('Form idempotency error');
  // }

  // if (!SCDS\CSRF::verify()) {
  //   $_SESSION['TENANT-' . app()->tenant->getId()]['FormError'] = true;
  //   throw new Exception('Form CSRF error');
  // }

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

  $attachmentsList = $data->state->attachments;
  for ($i = 0; $i < sizeof($attachmentsList); $i++) {
    try {
      if ($filesystem->fileExists($attachmentsList[$i]->s3_key)) {
        // $file = $filesystem->read($attachmentsList[$i]->s3_key);
        $filesize = $filesystem->filesize($attachmentsList[$i]->s3_key);
        $mimetype = $filesystem->mimeType($attachmentsList[$i]->s3_key);

        $collectiveSize += $filesize;
        $attachments[] = [
          // 'encoded' => base64_encode($file),
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

  if ($collectiveSize > 10485760) {
    // Collectively too large attachments
    $_SESSION['TENANT-' . app()->tenant->getId()]['CollectiveSizeTooLargeError'] = true;
    throw new Exception();
  }

  // if (false && getenv('AWS_S3_BUCKET')) {
  //   for ($i = 0; $i < sizeof($attachments); $i++) {

  //     try {
  //       $filesystem->write($attachments[$i]['store_name'], file_get_contents($attachments[$i]['tmp_name']), ['visibility' => 'private']);
  //       $attachments[$i]['uploaded'] = true;
  //     } catch (League\Flysystem\FilesystemException | League\Flysystem\UnableToWriteFile $exception) {
  //     }
  //   }
  // }

  // reportError($attachments);

  if ($data->state->category && $data->state->category != "DEFAULT") {
    // Check exists
    $checkCategory = $db->prepare("SELECT COUNT(*) FROM `notifyCategories` WHERE `ID` = ? AND `Tenant` = ? AND Active");
    $checkCategory->execute([
      $data->state->category,
      $tenant->getId(),
    ]);

    if ($checkCategory->fetchColumn() != 1) throw new Exception('No subscription category');

    $sendingCategory = $data->state->category;
  }

  $subject = trim(str_replace('!', '', str_replace('*', '', $data->state->subject)));
  $message = str_replace($to_remove, "", $data->state->editorValue);
  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Admin" && !($replyAddress && $data->state->replyTo == "toMe")) {
    $name = getUserName($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']);
    $message .= '<p class="small text-muted">Sent by ' . $name . '. Reply to this email to contact our Enquiries Team who can pass your message on to ' . $name . '.</p>';
  }
  $force = 0;
  $sender = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
  if ($data->state->forceSend && ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Galas")) {
    $force = 1;
  }

  $coachSend = $data->state->sendToCoaches;

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
    $field = 'squad-' . $row[$i]['SquadID'];
    $selected = isset($data->recipients->$field);
    if ($squadsQuery != "" && $selected) {
      $squadsQuery .= "OR";
    }
    if ($selected) {
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
    $field = 'list-' . $lists[$i]['ID'];
    $selected = isset($data->recipients->$field);
    if ($listsQuery != "" && $selected) {
      $listsQuery .= "OR";
    }
    if ($selected) {
      $id = "TL-" . $lists[$i]['ID'];
      $id = substr_replace($id, '', 0, 3);
      $listsQuery .= " `ListID` = '" . $lists[$i]['ID'] . "' ";
      $listsArray[$lists[$i]['ID']] = $lists[$i]['Name'];
    }
  }

  while ($gala = $galas->fetch(PDO::FETCH_ASSOC)) {
    $field = 'gala-' . $gala['GalaID'];
    $selected = isset($data->recipients->$field);
    if ($galaQuery != "" && $selected) {
      $galaQuery .= "OR";
    }
    if ($selected) {
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

  if ($data->state->from == "fromMe") {
    $senderNames = explode(' ', $userSending);

    $recipientGroups["NamedSender"] = [
      "Email" => "noreply@" . getenv('EMAIL_DOMAIN'),
      "Name" => $userSending
    ];
  }

  if ($replyAddress && $data->state->replyTo == "toMe") {
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

  $_SESSION['TENANT-' . app()->tenant->getId()]['NotifySuccess'] = [
    "Count" => $count,
    "Force" => $force
  ];

  $db = app()->db;
  $getExtraEmails = $db->prepare("SELECT `Name`, `EmailAddress`, `ID` FROM `notifyAdditionalEmails` WHERE `UserID` = ? AND `Verified` = '1'");

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

    $from = [
      "email" => "noreply@" . getenv('EMAIL_DOMAIN'),
      "name" => app()->tenant->getKey('CLUB_NAME')
    ];
    if (isset($jsonData->NamedSender->Email) && isset($jsonData->NamedSender->Name)) {
      $from = [
        "email" => "noreply@" . getenv('EMAIL_DOMAIN'),
        "name" => $jsonData->NamedSender->Name
      ];
    }
    $tos = [];
    while ($user = $getUsersForEmail->fetch(PDO::FETCH_ASSOC)) {
      if (($sendingCategory == 'Notify' && ($currentMessage['ForceSend'] || isSubscribed($user['UserID'], $sendingCategory))) || ($sendingCategory != 'Notify' && ($currentMessage['ForceSend'] || isAbsolutelySubscribed($user['UserID'], $sendingCategory)))) {
        $tos[$user['EmailAddress']] = [
          'email' => $user['EmailAddress'],
          'name' => $user['Forename'] . ' ' . $user['Surname'],
          'replacements' => [
            [
              "search" => '-name-',
              "replacement" => $user['Forename'] . ' ' . $user['Surname']
            ],
            [
              "search" => '-unsub_link-',
              "replacement" => autoUrl("notify/unsubscribe/" . dechex($user['UserID']) .  "/" . urlencode($user['EmailAddress']) . "/Notify")
            ]
          ]
        ];
        if ($sendingCategory == 'Notify') {
          $getExtraEmails->execute([$user['UserID']]);
          while ($extraEmails = $getExtraEmails->fetch(PDO::FETCH_ASSOC)) {
            $tos[$extraEmails['EmailAddress']] = [
              'email' => $extraEmails['EmailAddress'],
              'name' => $extraEmails['Name'],
              'replacements' => [
                [
                  "search" => '-name-',
                  "replacement" => $extraEmails['Name']
                ],
                [
                  "search" => '-unsub_link-',
                  "replacement" => autoUrl("cc/" . dechex($extraEmails['ID']) .  "/" . hash('sha256', $extraEmails['ID']) . "/unsubscribe")
                ]
              ]
            ];
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
    $plainTextContent = $plain_text;
    $htmlContent = $mailObject->getFormattedHtml();

    // Ensure we only send once
    $recipients = [];
    foreach ($tos as $value) {
      $recipients[] = $value;
    }

    $apiRequest = [
      "from" => $from,
      "recipients" => $recipients,
      "subject" => $subject,
      "plain" => $plainTextContent,
      "html" => $htmlContent,
      "global_substitutions" => $globalSubstitutions
    ];

    $apiAttachments = [];
    foreach ($attachments as $attachment) {
      $apiAttachments[] = [
        "bucket" => getenv('AWS_S3_BUCKET'),
        "key" => $attachment['store_name'],
        "filename" => $attachment['filename'],
      ];
    }
    $apiRequest['attachments'] = $apiAttachments;

    if (isset($jsonData->ReplyToMe->Email) && isset($jsonData->ReplyToMe->Name)) {
      try {
        $apiRequest['replyTo'] = [
          "email" => $jsonData->ReplyToMe->Email,
          "name" => $jsonData->ReplyToMe->Name
        ];
      } catch (Exception $e) {
        $apiRequest['replyTo'] = [
          "email" => app()->tenant->getKey('CLUB_EMAIL'),
          "name" => app()->tenant->getKey('CLUB_NAME') . ' Enquiries'
        ];
      }
    } else {
      $apiRequest['replyTo'] = [
        "email" => app()->tenant->getKey('CLUB_EMAIL'),
        "name" => app()->tenant->getKey('CLUB_NAME') . ' Enquiries'
      ];
    }

    $lambda = new Aws\Lambda\LambdaClient([
      'region' => 'eu-west-2',
      'version' => '2015-03-31',
    ]);

    $res = $lambda->invoke([
      'FunctionName' => 'arn:aws:lambda:eu-west-2:684636513987:function:Email-Queue',
      'InvocationType' => 'Event',
      'Payload' => json_encode($apiRequest),
    ]);

    AuditLog::new('Notify-SentGroup', 'Sent email ' . $id);
  }
  $db->commit();

  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'])) {
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData']);
  }

  echo json_encode([
    'success' => true,
  ]);
} catch (Exception $e) {
  $db->rollback();

  reportError($e);

  echo json_encode([
    'success' => false,
    'exception' => $e,
  ]);
}
