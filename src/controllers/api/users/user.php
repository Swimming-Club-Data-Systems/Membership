<?php

$db = app()->db;
$tenant = app()->tenant;

$output = [];

try {

  $user = new User($id);

  

  $superPermissions = app()->user->hasPermissions(["Admin", "Coach", "Galas"]);
  $isUser = $user->getId() == app()->user->getId();

} catch (Exception $e) {

  $output = [
    "success" => false,
    "message" => $e->getMessage(),
  ];
}

echo json_encode($output);