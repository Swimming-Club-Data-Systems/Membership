<?php

$data = [
  'showMessage' => false,
  'message' => null
];

if (config('EMERGENCY_MESSAGE_TYPE') != 'NONE' && config('EMERGENCY_MESSAGE')) {
  $markdown = new ParsedownExtra();
  $message = "";

  $message .= '<div class="py-3 ';

  if (config('EMERGENCY_MESSAGE_TYPE') == 'SUCCESS') {
    $message .= 'bg-success text-white';
  }

  if (config('EMERGENCY_MESSAGE_TYPE') == 'DANGER') {
    $message .= 'bg-danger text-white';
  }

  if (config('EMERGENCY_MESSAGE_TYPE') == 'WARN') {
    $message .= 'bg-warning text-body';
  }

  $message .= '"><div class="container emergency-message">';
  try {
    $message .= $markdown->text(config('EMERGENCY_MESSAGE'));
  } catch (Exception $e) {
    $message .= '<p>An emergency message has been set but cannot be rendered.</p>';
  }
  $message .= '</div> </div>';

  $data = [
    'showMessage' => true,
    'message' => $message
  ];
}

return response()->json($data)->withHeaders([
  'cache-control' => 'max-age=3600',
  'access-control-allow-origin' => $request->header('origin'),
]);