<?php

$_SESSION['SCDS-Notify']['NotifyPostData'] = $_POST;

$db = app()->db;

$db->beginTransaction();

try {
  if (sizeof($_POST) == 0) {
    $_SESSION['SCDS-Notify']['TooLargeError'] = true;
    throw new Exception('Filesize TooLargeError');
  }

  if (!SCDS\FormIdempotency::verify()) {
    $_SESSION['SCDS-Notify']['FormError'] = true;
    throw new Exception('Form idempotency error');
  }

  if (!SCDS\CSRF::verify()) {
    $_SESSION['SCDS-Notify']['FormError'] = true;
    throw new Exception('Form CSRF error');
  }

  $replyAddress = getUserOption($_SESSION['SCDS-Notify']['UserID'], 'NotifyReplyAddress');

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
  for ($i = 0; $i < sizeof($_FILES['file-upload']['tmp_name']); $i++) {
    if (is_uploaded_file($_FILES['file-upload']['tmp_name'][$i])) {

      if (bool($_FILES['file-upload']['error'][$i])) {
        // Error
        // reportError($_FILES['file-upload']['error'][$i]);
        if ($_FILES['file-upload']['error'][$i] == 2) {
          // Too large
          $_SESSION['SCDS-Notify']['TooLargeError'] = true;
        } else {
          $_SESSION['SCDS-Notify']['UploadError'] = true;
        }
        throw new Exception();
      } else if (false/*$_FILES['file-upload']['type'][$i] != 'text/plain' && $_FILES['file-upload']['type'][$i] != 'application/octet-stream'*/) {
        // Probably not a text file
        reportError($_FILES['file-upload']['type'][$i]);
        $_SESSION['SCDS-Notify']['UploadError'] = true;
        throw new Exception();
      } else if ($_FILES['file-upload']['size'][$i] > 10485760) {
        // Too large, stop
        // reportError($_FILES['file-upload']['size'][$i]);
        $_SESSION['SCDS-Notify']['TooLargeError'] = true;
        throw new Exception();
      } else if ($_FILES['file-upload']['size'][$i] > 0) {
        // Store uploaded files in filestore, if exists

        $collectiveSize += $_FILES['file-upload']['size'][$i];
        $attachments[] = [
          'encoded' => base64_encode(file_get_contents($_FILES['file-upload']['tmp_name'][$i])),
          'mime' => mime_content_type($_FILES['file-upload']['tmp_name'][$i]),
          'filename' => $_FILES['file-upload']['name'][$i],
          'disposition' => 'attachment',
          'tmp_name' => $_FILES['file-upload']['tmp_name'][$i],
          'store_name' => $filenamePath,
          'directory' => $path,
          'url' => $url,
          'uploaded' => false,
        ];
      } else {
        // File upload error (no size)
        reportError($_FILES);
        $_SESSION['SCDS-Notify']['UploadError'] = true;
        throw new Exception();
      }
    }
  }

  if ($collectiveSize > 10485760) {
    // Collectively too large attachments
    $_SESSION['SCDS-Notify']['CollectiveSizeTooLargeError'] = true;
    throw new Exception();
  }

  // if (getenv('FILE_STORE_PATH')) {
  //   for ($i = 0; $i < sizeof($attachments); $i++) {
  //     if (!is_writeable($attachments[$i]['store_name'])) {
  //       // Try making folders
  //       // $dir = explode('/', $attachments[$i]['store_name']);
  //       // $path = "";
  //       // $tried = [];
  //       // for ($y = 0; $y < sizeof($dir) - 1; $y++) {
  //       //   $path .= $dir[$y];
  //       //   if (!is_dir($path)) {
  //       //     mkdir($path);
  //       //     $tried[] = $path;
  //       //   }
  //       //   $path .= '/';
  //       // }
  //       if (!is_dir($attachments[$i]['directory'])) {
  //         mkdir($attachments[$i]['directory'], 0755, true);
  //       }
  //       if (!is_writeable($path)) {
  //         // reportError([$tried, $path, $attachments[$i]['store_name']]);
  //       } else {
  //         // reportError([$tried, $path, $attachments[$i]['store_name']]);
  //       }
  //     } else {
  //       // reportError("Filepath is writeable");
  //     }
  //     if (move_uploaded_file($attachments[$i]['tmp_name'], $attachments[$i]['store_name'])) {
  //       $attachments[$i]['uploaded'] = true;
  //     } else {
  //       // reportError([$attachments[$i]['tmp_name'], $attachments[$i]['store_name'], $_FILES['file-upload']]);
  //     }
  //   }
  // }

  // reportError($attachments);

  $subject = $_POST['subject'];
  $message = str_replace($to_remove, "", $_POST['message']);
  $force = 0;
  $sender = $_SESSION['SCDS-Notify']['UserID'];
  if (isset($_POST['force'])) {
    $force = 1;
  }

  $lists = $db->query("SELECT `ID`, `Name` FROM `tenants` ORDER BY `Name` ASC;");

  $query = $squadsQuery = $listsQuery = $galaQuery = "";

  $squads = $listsArray = $galasArray = [];

  $toSendTo = [];

  $emailUsers = [];
  $getUsersForEmail = $db->prepare("SELECT Forename, Surname, EmailAddress, UserID FROM users WHERE users.Active AND users.Tenant = ?");
  while ($list = $lists->fetch(PDO::FETCH_ASSOC)) {
    if (isset($_POST['TL-' . $list['ID']])) {
      $getUsersForEmail->execute([
        $list['ID'],
      ]);
      $users = $getUsersForEmail->fetchAll(PDO::FETCH_ASSOC);

      $emailUsers = array_merge($emailUsers, $users);
    }
  }

  // Completed It PDO Object
  // $completed = $db->prepare("UPDATE `notify` SET `Status` = ? WHERE `EmailID` = ?");


  $mailObject = new \CLSASC\SuperMailer\CreateMail();
  $mailObject->setHtmlContent($message);

  $mailObject->showName();
  $mailObject->setUnsubscribable();

  $from = new \SendGrid\Mail\From("noreply@" . getenv('EMAIL_DOMAIN'), 'Swimming Club Data Systems');
  $tos = [];
  foreach ($emailUsers as $user) {
    if (bool($force) || isSubscribed($user['UserID'], 'Notify')) {
      $tos[] = new \SendGrid\Mail\To(
        $user['EmailAddress'],
        $user['Forename'] . ' ' . $user['Surname'],
        [
          '-name-' => $user['Forename'] . ' ' . $user['Surname'],
        ]
      );
      // $getExtraEmails->execute([$user['UserID']]);
      // while ($extraEmails = $getExtraEmails->fetch(PDO::FETCH_ASSOC)) {
      //   $tos[] = new \SendGrid\Mail\To(
      //     $extraEmails['EmailAddress'],
      //     $extraEmails['Name'],
      //     [
      //       '-name-' => $extraEmails['Name'],
      //     ]
      //   );
      //   $ccEmails[$extraEmails['EmailAddress']] = $extraEmails['Name'];
      // }
      // $completed->execute(['Sent', $user['EmailID']]);
    } else {
      // $completed->execute(['No_Sub', $user['EmailID']]);
    }

  }
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

  $email->setReplyTo('support@myswimmingclub.uk', 'SCDS Support');

  $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
  $response = $sendgrid->send($email);

  if (isset($_SESSION['SCDS-Notify']['NotifyPostData'])) {
    unset($_SESSION['SCDS-Notify']['NotifyPostData']);
  }

  $_SESSION['SCDS-Notify']['Success'] = true;

  header("Location: " . autoUrl("admin/notify"));
} catch (Exception $e) {
  reportError($e);
  header("Location: " . autoUrl("admin/notify/compose"));
}
