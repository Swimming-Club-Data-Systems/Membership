<?php

/**
 * Code to provide informative content to users on bank holidays.
 */

/**
 * Returns details about today if it is a bank holiday
 *
 * @param string $date optional date in YYYY-MM-DD format
 * @return array
 */
function isBankHoliday($date = 'now') {
  $date = (new DateTime($date, new DateTimeZone('Europe/London')))->format("Y-m-d");
  $dataFile = getCachedFile(CACHE_DIR . 'bank-holidays.json', 'https://www.gov.uk/bank-holidays.json', 2419200);
  $data = json_decode($dataFile, true);

  foreach ($data['england-and-wales']['events'] as $event) {
    if ($event['date'] == $date) {
      return $event;
    }
  }

  return false;
}