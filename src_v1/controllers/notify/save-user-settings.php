<?php

use Respect\Validation\Validator as v;

$data = json_decode(file_get_contents('php://input'));

if (v::email()->validate($data->replyEmailAddress)) {
  setUserOption(app()->user->getId(), 'NotifyReplyAddress', $data->replyEmailAddress);
}

if ($data->defaultSendAs) {
  setUserOption(app()->user->getId(), 'NotifyDefaultSendAs', $data->defaultSendAs);
}

$replyAddress = app()->user->getUserOption('NotifyReplyAddress');
if ($replyAddress && $data->defaultReplyTo) {
  setUserOption(app()->user->getId(), 'NotifyDefaultReplyTo', $data->defaultReplyTo);
}

$defaultSendAs = app()->user->getUserOption('NotifyDefaultSendAs');
$defaultReplyTo = app()->user->getUserOption('NotifyDefaultReplyTo');

$clubName = app()->tenant->getKey('CLUB_NAME');
$clubEmail = app()->tenant->getKey('CLUB_EMAIL');
$userName = app()->user->getForename() . ' ' . app()->user->getSurname();

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
    'replyEmailAddress' => (string) app()->user->getUserOption('NotifyReplyAddress'),
    'defaultReplyTo' => ($defaultReplyTo && $replyAddress) ? $defaultReplyTo : 'toClub',
    'defaultSendAs' => ($defaultSendAs) ? $defaultSendAs : 'asClub',
  ],
]);
