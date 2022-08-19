<?php

$tenant = Tenant::fromUUID($id);

if (!$tenant) halt(404);

if (!\SCDS\CSRF::verify()) halt(403);

$db = app()->db;

$set = $db->prepare("UPDATE tenants SET Domain = ? WHERE UniqueID = ?");
$set->execute([
  trim($_POST['domain-name']),
  $id
]);

http_response_code(302);
header('location: ' . autoUrl("admin/tenants/$id"));