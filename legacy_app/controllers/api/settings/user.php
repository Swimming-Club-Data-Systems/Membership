<?php

/**
 * API for user settings
 */

$db = DB::connection()->getPdo();
$user = null;

if (app()->user) {
  $user = app()->user;
}

$output = [];

try {

  if ($user) {

    $userKeyData = [];
    $keys = [
      'DISPLAY_NAME_FORMAT' => 'string',
    ];

    foreach ($keys as $key => $type) {
      $var = $user->getkey($key);
      settype($var, $type);
      $userKeyData[mb_strtolower($key)] = $var;
    }

    $output = [
      'success' => true,
      'keys' => $userKeyData,
      'user' => [
        'id' => $user->getId(),
        'first_name' => $user->getFirstName(),
        'last_name' => $user->getLastName(),
        'email' => $user->getEmail(),
        'mobile' => $user->getMobile(),
        'permissions' => $user->getPermissions(),
        'gravitar' => "https://www.gravatar.com/avatar/" . md5(strtolower(trim($user->getEmail()))) . "?d=mp",
      ],
    ];
  } else {
    throw new Exception('No user defined');
  }
} catch (Exception $e) {
  $output = [
    'success' => false,
    'message' => $e->getMessage()
  ];
}

echo json_encode($output);
