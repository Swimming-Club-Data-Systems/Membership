<?php

use Webauthn\PublicKeyCredentialSource;

$db = app()->db;

$sources = [];

$find = $db->prepare("SELECT `id`, `credential_id`, `credential`, `credential_name`, `created_at`, `updated_at` FROM `userCredentials` WHERE `user_id` = ?");
$find->execute([
  app()->user->getId(),
]);

while ($data = $find->fetch(PDO::FETCH_ASSOC)) {
  $source = PublicKeyCredentialSource::createFromArray(json_decode($data['credential'], true));
  $sources[] = [
    'id' => $data['id'],
    'credential_id' => $data['credential_id'],
    'created_at' => $data['created_at'],
    'updated_at' => $data['updated_at'],
    'name' => $data['credential_name'],
    'type' => $source->getType(),
  ];
}

echo json_encode([
  'username' => app()->user->getEmail(),
  'authenticators' => $sources,
]);