<?php

define('CRON_PROCESS', true);

require '../common.php';

$db = null;
try {
  $db = new PDO("mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4", getenv('DB_USER'), getenv('DB_PASS'));
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
  echo "\r\nUnable to connect to SQL database.";
  echo "\r\nQuitting...\r\n\r\n";
  exit();
}

/**
 * halt to stop execution
 *
 * @param integer $statusCode http response code
 * @param boolean $throwException throw an exception to be handled later
 * @return void
 */
function halt(int $statusCode, bool $throwException = true) {
  if ($statusCode == 500) {
    // Report the error
    global $e;
    reportError($e);
  }
  
  if ($throwException) {
    throw new \SCDS\HaltException('Status ' . $statusCode);
  }
}

include BASE_PATH . 'database.php';