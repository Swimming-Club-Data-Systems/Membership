<?php

try {
  $date = new DateTime($_POST['go-to-week-date'], new DateTimeZone('Europe/London'));

  http_response_code(302);

  $squadString = '';
  if (isset($_POST['squad'])) {
    $squadString = '&squad=' . urlencode($_POST['squad']);
  }

  header("location: " . autoUrl('timetable?&year=' . urlencode($date->format('o')) . $squadString . '&week=' . urlencode($date->format('W')) . '#day-' . $date->format('w')));
} catch (Exception $e) {
  http_response_code(302);
  header("location: " . autoUrl('timetable'));
}
