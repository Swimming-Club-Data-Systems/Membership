<?php

// Setup GoCardless Client

// $at = tenant()->getLegacyTenant()->getGoCardlessAccessToken();
$client = null;
// try {
  $client = SCDS\GoCardless\Client::get();
// } catch (Exception $e) {
//   halt(404);
// }
