<?php

use Respect\Validation\Validator as v;

$data = json_decode(file_get_contents('php://input'));

if (v::email()->validate($data->replyEmailAddress)) {
  setUserOption(Auth::id(), 'NotifyReplyAddress', $data->replyEmailAddress);
}

if ($data->defaultSendAs) {
  setUserOption(Auth::id(), 'NotifyDefaultSendAs', $data->defaultSendAs);
}

$replyAddress = Auth::User()->getLegacyUser()->getUserOption('NotifyReplyAddress');
if ($replyAddress && $data->defaultReplyTo) {
  setUserOption(Auth::id(), 'NotifyDefaultReplyTo', $data->defaultReplyTo);
}

$defaultSendAs = Auth::User()->getLegacyUser()->getUserOption('NotifyDefaultSendAs');
$defaultReplyTo = Auth::User()->getLegacyUser()->getUserOption('NotifyDefaultReplyTo');

$clubName = config('CLUB_NAME');
$clubEmail = config('CLUB_EMAIL');
$userName = Auth::User()->getLegacyUser()->getForename() . ' ' . Auth::User()->getLegacyUser()->getSurname();

$possibleReplyTos = [
  [
    'name' => 'Main club email address <' . $clubEmail . '>',
    'value' => 'toClub'
  ],
];

if ($replyAddress) {
  $possibleReplyTos[] = [
    'name' => $userName . ' <' . $replyAddress . '>',
    'value' => 'toMe'
  ];
}

header("content-type: application/json");
echo json_encode([
  'possibleReplyTos' => $possibleReplyTos,
  'settings' => [
    'replyEmailAddress' => (string) Auth::User()->getLegacyUser()->getUserOption('NotifyReplyAddress'),
    'defaultReplyTo' => ($defaultReplyTo && $replyAddress) ? $defaultReplyTo : 'toClub',
    'defaultSendAs' => ($defaultSendAs) ? $defaultSendAs : 'asClub',
  ],
]);
