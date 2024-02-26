<?php

// Checks if username has WebAuthn creds

$db = app()->db;
$tenant = app()->tenant;

$email = $_GET['email'] ?? "";

$getCount = $db->prepare("SELECT COUNT(*) FROM users INNER JOIN userCredentials ON users.UserID = userCredentials.user_id WHERE users.EmailAddress = ? AND users.Tenant = ?");
$getCount->execute([
  $email,
  $tenant->getId(),
]);

$ssoUrl = null;

echo json_encode([
  "has_webauthn" => $getCount->fetchColumn() > 0,
  "is_sso" => $tenant->getBooleanKey("TENANT_ENABLE_STAFF_OAUTH") && str_ends_with((string) $email, (string) $tenant->getKey("TENANT_OAUTH_EMAIL_DOMAIN")),
  "sso_url" => autoUrl("login/oauth?email=" . urlencode((string) $email)),
]);