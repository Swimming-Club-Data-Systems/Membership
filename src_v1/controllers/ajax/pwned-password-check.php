<?php

// https://api.pwnedpasswords.com/range/5chars

use Respect\Validation\Rules\TrueVal;

use function GuzzleHttp\json_encode;

if (!isset($_POST['password']) || !isset($_POST['csrf'])) halt(401);

// Ensure is a system user
if (!\SCDS\CSRF::verifyCode($_POST['csrf'])) halt(401);

$json = [
  'success' => false,
  'pwned' => false,
  'count' => 0,
];

try {

  $http = new GuzzleHttp\Client([
    'timeout'  => 1.0,
  ]);

  $responseData = [];

  $hash = hash('sha1', $_POST['password']);
  $hashStart = mb_strtoupper(mb_substr($hash, 0, 5));
  $hashEnd = mb_strtoupper(mb_substr($hash, 5));

  $response = $http->request('GET', 'https://api.pwnedpasswords.com/range/' . $hashStart);

  // Check 200 OK
  if ($response->getStatusCode() != 200) {
    throw new Exception('Could not get hash list');
  }

  // Get body
  $body = $response->getBody();
  $hashes = explode("\r\n", $body);

  $pwned = false;
  $count = 0;

  foreach ($hashes as $line) {
    $lineData = explode(":", $line);

    if ($lineData[0] == $hashEnd) {
      $pwned = true;
      $count = (int) $lineData[1];
      break;
    }
  }

  $json = [
    'success' => true,
    'pwned' => $pwned,
    'count' => $count,
  ];
} catch (GuzzleHttp\Exception\RequestException $e) {

  $json = [
    'success' => false,
    'pwned' => false,
    'count' => 0,
  ];
} catch (Exception $e) {

  $json = [
    'success' => false,
    'pwned' => false,
    'count' => 0,
  ];
}

http_response_code(200);
header('content-type: application/json');
echo json_encode($json);
