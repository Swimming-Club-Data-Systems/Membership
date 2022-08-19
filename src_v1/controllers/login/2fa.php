<?php

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

$db = app()->db;

$response = [
  'next' => 'error',
  'error' => [
    'message' => 'HI'
  ]
];

use GeoIp2\Database\Reader;
use PragmaRX\Google2FA\Google2FA;
$ga2fa = new Google2FA();

try {

  // Parse request
  $body = json_decode(file_get_contents('php://input'));

  if (!SCDS\CSRF::verifyCode($body->csrfToken)) {
    throw new Exception('csrf');
  }

  // Get the organisation
  $club = null;
  if (isset($body->organisation) && mb_strlen((string) $body->organisation) > 0) {
    $club = Tenant::fromCode((string) $body->organisation);
    if (!$club) {
      $club = Tenant::fromId((int) $body->organisation);
    }
  }

  if (!$club) {
    throw new Error('clubNotFound');
  }

  app()->tenant = $club;

  if ($body->action == 'resend') {

    if ($_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_GOOGLE']) {
      $_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_CODE'] = random_int(100000, 999999);
      $_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_GOOGLE'] = false;
    }

    $query = $db->prepare("SELECT EmailAddress, Forename, Surname FROM users WHERE UserID = ? AND Tenant = ?");
    $query->execute([
      $_SESSION['TENANT-' . $club->getId()]['2FAUserID'],
      $club->getId()
    ]);
    $row = $query->fetch(PDO::FETCH_ASSOC);

    $date = new DateTime('now', new DateTimeZone('Europe/London'));

    $message = '
    <p>Hello. Confirm your login by entering the following code in your web browser.</p>
    <p><strong>' . $_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_CODE'] . '</strong></p>
    <p>If you did not just try to log in, you can ignore this email. You may want to reset your password.</p>
    <p>This email was resent to this address at the request of the user.</p>
    <p>Kind Regards, <br>The ' . $club->getKey('CLUB_NAME') . ' Team</p>';

    if (notifySend(null, "Verification Code - Requested at " . $date->format("H:i:s \o\\n d/m/Y"), $message, $row['Forename'] . " " . $row['Surname'], $row['EmailAddress'])) {
      $_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR'] = true;
      $_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_RESEND'] = true;
      $response = [
        'next' => 'resent',
      ];
    } else {
      throw new Exception('notSent');
    }
  } else if ($body->action == 'verify') {
    $auth_via_google_authenticator;
    try {
      $auth_via_google_authenticator = (isset($_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_GOOGLE']) && $_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_GOOGLE']) && $ga2fa->verifyKey(getUserOption($_SESSION['TENANT-' . $club->getId()]['2FAUserID'], "GoogleAuth2FASecret"), $body->code);
    } catch (Exception $e) {
      $auth_via_google_authenticator = false;
    }

    if ((isset($_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_CODE']) && $body->code == $_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_CODE']) || $auth_via_google_authenticator) {
      unset($_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR']);
      unset($_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_CODE']);
      unset($_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_GOOGLE']);

      if (isset($_SESSION['TENANT-' . $club->getId()]['2FAUserID']) && bool(getUserOption($_SESSION['TENANT-' . $club->getId()]['2FAUserID'], "IsSpotCheck2FA"))) {
        setUserOption($_SESSION['TENANT-' . $club->getId()]['2FAUserID'], "IsSpotCheck2FA", false);
      }

      // Verified - Go to pass-through
      $_SESSION['PassAuth-TENANT-' . $club->getId()]['User'] = $_SESSION['TENANT-' . $club->getId()]['2FAUserID'];

      $response = [
        'next' => 'pass',
        'pass' => [
          'redirect' => autoUrl("login/pass", false)
        ]
      ];

    } else {
      // Invalid
      $response = [
        'next' => 'incorrect',
        'incorrect' => [
          
        ]
      ];
    }
  }
} catch (Exception $e) {

  $error = $e->getMessage();

  if ($error == 'csrf') {
    $response = [
      'next' => 'csrf',
      'csrf' => [
        'message' => 'HI'
      ]
    ];
  } else if ($error == 'notSent') {
    $response = [
      'next' => 'notsent',
      'notSent' => [
        'message' => 'HI'
      ]
    ];
  } else {
    $response = [
      'next' => 'error',
      'error' => [
        'message' => 'HI'
      ]
    ];
  }
} finally {
  echo json_encode($response);
}
