<?php

$db = app()->db;
$post = json_decode(file_get_contents('php://input'));

$find = $db->prepare("SELECT COUNT(*) FROM `userCredentials` WHERE `id` = ? AND `user_id` = ?");
$find->execute([
  $post->id,
  app()->user->getId(),
]);

$count = $find->fetchColumn();

if ($count) {
  $delete = $db->prepare("DELETE FROM `userCredentials` WHERE `id` = ? AND `user_id` = ?");
  $delete->execute([
    $post->id,
    app()->user->getId(),
  ]);

  echo json_encode([
    'success' => true,
  ]);
} else {
  echo json_encode([
    'success' => false,
    'message' => "No passkey found",
  ]);
}
