<?php

$db = DB::connection()->getPdo();
$post = json_decode(file_get_contents('php://input'));

$find = $db->prepare("SELECT COUNT(*) FROM `userCredentials` WHERE `id` = ? AND `user_id` = ?");
$find->execute([
  $post->id,
  Auth::id(),
]);

$count = $find->fetchColumn();

if ($count) {
  $delete = $db->prepare("DELETE FROM `userCredentials` WHERE `id` = ? AND `user_id` = ?");
  $delete->execute([
    $post->id,
    Auth::id(),
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
