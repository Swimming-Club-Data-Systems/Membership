<?php
$pagetitle = "Notify Composer";
$use_white_background = true;

$db = app()->db;
$tenant = app()->tenant;

$squads = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') {
  $squads = $db->prepare("SELECT `SquadName`, `SquadID` FROM `squads` WHERE `Tenant` = ? ORDER BY `SquadFee` DESC, `SquadName` ASC;");
  $squads->execute([
    $tenant->getId()
  ]);
} else {
  $squads = $db->prepare("SELECT `SquadName`, `SquadID` FROM `squads` INNER JOIN squadReps ON squadReps.Squad = squads.SquadID WHERE squadReps.User = ? AND `Tenant` = ? ORDER BY `SquadFee` DESC, `SquadName` ASC;");
  $squads->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $tenant->getId()]);
}

$lists = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') {
  $lists = $db->prepare("SELECT targetedLists.ID, targetedLists.Name FROM `targetedLists` WHERE `Tenant` = ? ORDER BY `Name` ASC;");
  $lists->execute([
    $tenant->getId()
  ]);
} else {
  $lists = $db->prepare("SELECT targetedLists.ID, targetedLists.Name FROM `targetedLists` INNER JOIN listSenders ON listSenders.List = targetedLists.ID WHERE listSenders.User = ? AND `Tenant` = ? ORDER BY `Name` ASC;");
  $lists->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $tenant->getId()]);
}

$galas = $db->prepare("SELECT GalaName, GalaID FROM `galas` WHERE GalaDate >= ? AND `Tenant` = ? ORDER BY `GalaName` ASC;");
$date = new DateTime('-1 week', new DateTimeZone('Europe/London'));
$galas->execute([$date->format('Y-m-d'), $tenant->getId()]);

$query = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE
UserID = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$curUserInfo = $query->fetch(PDO::FETCH_ASSOC);

$senderNames = explode(' ', $curUserInfo['Forename'] . ' ' . $curUserInfo['Surname']);
$fromEmail = "";
for ($i = 0; $i < sizeof($senderNames); $i++) {
  $fromEmail .= urlencode(strtolower($senderNames[$i]));
  if ($i < sizeof($senderNames) - 1) {
    $fromEmail .= '.';
  }
}

$pendingRenewal = false;
$date = new DateTime('now', new DateTimeZone('Europe/London'));
$renewals = $db->prepare("SELECT * FROM `renewals` WHERE `StartDate` <= :today AND `EndDate` >= :today AND Tenant = :tenant");
$renewals->execute([
  'tenant' => $tenant->getId(),
  'today' => $date->format("Y-m-d")
]);
$renewal = $renewals->fetch(PDO::FETCH_ASSOC);
if ($renewal) {
  $pendingRenewal = true;
}

if (!app()->tenant->isCLS()) {
  $fromEmail .= '.' . urlencode(mb_strtolower(str_replace(' ', '', getenv('CLUB_CODE'))));
}

$fromEmail .= '@' . getenv('EMAIL_DOMAIN');

function fieldChecked($name)
{
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'][$name]) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'][$name])) {
    return ' checked ';
  }
}

function fieldValue($name)
{
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'][$name])) {
    return 'value="' . htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'][$name]) . '"';
  }
}

$uuid = \Ramsey\Uuid\Uuid::uuid4();
$date = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y/m/d');
$attachments = [];

$getCategories = $db->prepare("SELECT `ID` `id`, `Name` `name`, `Description` `description` FROM `notifyCategories` WHERE `Tenant` = ? AND `Active` ORDER BY `Name` ASC;");
$getCategories->execute([
  $tenant->getId()
]);

$possibleTos = [];

$possibleTosList = [];
while ($list = $lists->fetch(PDO::FETCH_ASSOC)) {
  $possibleTosList[] = [
    'name' => $list['Name'],
    'id' => $list['ID'],
    'key' => 'list-' . $list['ID'],
    'state' => false,
    'type' => 'lists',
  ];
};
$group = [
  'type' => 'lists',
  'groups' => $possibleTosList,
];
$possibleTos['lists'] = $group;

$possibleTosList = [];
while ($squad = $squads->fetch(PDO::FETCH_ASSOC)) {
  $possibleTosList[] = [
    'name' => $squad['SquadName'],
    'id' => $squad['SquadID'],
    'key' => 'squad-' . $squad['SquadID'],
    'state' => false,
    'type' => 'squads',
  ];
};
$group = [
  'type' => 'squads',
  'groups' => $possibleTosList,
];
$possibleTos['squads'] = $group;

$possibleTosList = [];
while ($gala = $galas->fetch(PDO::FETCH_ASSOC)) {
  $possibleTosList[] = [
    'name' => $gala['GalaName'],
    'id' => $gala['GalaID'],
    'key' => 'gala-' . $gala['GalaID'],
    'state' => false,
    'type' => 'galas',
  ];
};
$group = [
  'type' => 'galas',
  'groups' => $possibleTosList,
];
$possibleTos['galas'] = $group;

$clubName = app()->tenant->getKey('CLUB_NAME');
$clubEmail = app()->tenant->getKey('CLUB_EMAIL');
$userName = app()->user->getForename() . ' ' . app()->user->getSurname();

$replyAddress = app()->user->getUserOption('NotifyReplyAddress');

$defaultSendAs = app()->user->getUserOption('NotifyDefaultSendAs');
$defaultReplyTo = app()->user->getUserOption('NotifyDefaultReplyTo');

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

$sendAs = [
  [
    'name' => $clubName,
    'value' => 'fromClub'
  ],
  [
    'name' => $userName,
    'value' => 'fromMe'
  ]
];

// data-images-upload-url="<?= htmlspecialchars(autoUrl("notify/new/image-upload"))

$categories = [
  [
    'name' => 'Notify',
    'value' => 'DEFAULT',
  ]
];

while ($category = $getCategories->fetch(PDO::FETCH_OBJ)) {
  $categories[] = [
    'name' => $category->name,
    'value' => $category->id
  ];
}

$canForceSend = false;
if (app()->user->hasPermissions(['Admin', 'Galas'])) {
  $canForceSend = true;
}

$uuid = \Ramsey\Uuid\Uuid::uuid4();
$date = new DateTime('now', new DateTimeZone('Europe/London'));

header("content-type: application/json");
echo json_encode([
  'possibleTos' => $possibleTos,
  'possibleReplyTos' => $possibleReplyTos,
  'possibleFroms' => $sendAs,
  'possibleCategories' => $categories,
  'canForceSend' => $canForceSend,
  'documentBaseUrl' => autoUrl('notify/new/'),
  'imagesUploadUrl' => autoUrl('notify/new/image-upload'),
  'tinyMceCssLocation' => autoUrl('public/css/tinymce.css'),
  'uuid' => $uuid,
  'date' => $date->format('Y/m/d'),
  'settings' => [
    'replyEmailAddress' => (string) app()->user->getUserOption('NotifyReplyAddress'),
    'defaultReplyTo' => ($defaultReplyTo && $replyAddress) ? $defaultReplyTo : 'toClub',
    'defaultSendAs' => ($defaultSendAs) ? $defaultSendAs : 'asClub',
  ],
]);
