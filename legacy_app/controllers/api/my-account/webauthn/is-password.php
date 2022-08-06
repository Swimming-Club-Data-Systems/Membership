<?php

// Checks if username has WebAuthn creds

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

$email = isset($_GET['email']) ? $_GET['email'] : "";

$getUserCount = $db->prepare("SELECT COUNT(*) FROM users WHERE users.EmailAddress = ? AND users.Tenant = ?");
$getUserCount->execute([
  $email,
  $tenant->getId(),
]);

$getCount = $db->prepare("SELECT COUNT(*) FROM users INNER JOIN userCredentials ON users.UserID = userCredentials.user_id WHERE users.EmailAddress = ? AND users.Tenant = ?");
$getCount->execute([
  $email,
  $tenant->getId(),
]);

$ssoUrl = null;

echo json_encode([
  "user_exists" => $getUserCount->fetchColumn() > 0,
  "has_webauthn" => $getCount->fetchColumn() > 0,
  "is_sso" => config("TENANT_ENABLE_STAFF_OAUTH") && str_ends_with($email, config("TENANT_OAUTH_EMAIL_DOMAIN")),
  "sso_url" => autoUrl("login/oauth?email=" . urlencode($email)),
]);