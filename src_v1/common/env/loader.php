<?php

/**
 * READ IN SUPPLIED JSON FILE AND SET ENV VARS
 */

try {
  $json = json_decode(file_get_contents(getenv('ENV_JSON_FILE')), true);

  foreach ($json as $key => $value) {
    putenv("$key=$value");
  }

} catch (Exception $e) {
  echo "Cannot read env var file or put env vars";
  throw $e;
}