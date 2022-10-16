<?php

// Setup GoCardless Client

// $at = app()->tenant->getGoCardlessAccessToken();
$client = null;
try {
  $client = SCDS\GoCardless\Client::get();
} catch (Exception $e) {
  halt(902);
}
