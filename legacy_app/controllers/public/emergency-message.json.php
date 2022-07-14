<?php

$data = [
  'showMessage' => false,
  'message' => null
];

if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') != 'NONE' && app()->tenant->getKey('EMERGENCY_MESSAGE')) {
  $markdown = new ParsedownExtra();
  $message = "";

  $message .= '<div class="py-3 ';

  if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'SUCCESS') {
    $message .= 'bg-success text-white';
  }

  if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'DANGER') {
    $message .= 'bg-danger text-white';
  }

  if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'WARN') {
    $message .= 'bg-warning text-body';
  }

  $message .= '"><div class="container emergency-message">';
  try {
    $message .= $markdown->text(app()->tenant->getKey('EMERGENCY_MESSAGE'));
  } catch (Exception $e) {
    $message .= '<p>An emergency message has been set but cannot be rendered.</p>';
  }
  $message .= '</div> </div>';

  $data = [
    'showMessage' => true,
    'message' => $message
  ];
}

header("cache-control: max-age=3600");
header("access-control-allow-origin: " . $_SERVER['HTTP_ORIGIN']);
header("content-type: application/json");
echo json_encode($data);