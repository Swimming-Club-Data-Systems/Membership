<?php

use function GuzzleHttp\json_encode;

try {

  if (!\SCDS\CSRF::verify()) {
    throw new Exception('Invalid CSRF token');
  }

  $db = app()->db;
  $tenant = app()->tenant;

  $getSquads = $db->prepare("SELECT SquadName, SquadID, SquadFee FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC;");
  $getSquads->execute([
    $tenant->getId(),
  ]);

  $date = new DateTime($_POST['date-eighteen']);
  $squads = [];

  while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)) {
    if (isset($_POST['discount-amount-' . $squad['SquadID']])) {
      if ($_POST['discount-amount-' . $squad['SquadID']] > $squad['SquadFee'] || $_POST['discount-amount-' . $squad['SquadID']] < 0) {
        throw new Exception('Invalid value');
      }

      $fee = \Brick\Math\BigDecimal::of((string) $_POST['discount-amount-' . $squad['SquadID']])->withPointMovedRight(2)->toInt();
      $squads[(string) $squad['SquadID']] = $fee;
    }
  }

  $json = [
    'eighteen_by' => $date->format('Y-m-d'),
    'squads' => $squads,
  ];

  $json = json_encode($json);

  $tenant->setKey('TIER3_SQUAD_FEES', $json);

  $_SESSION['TENANT-' . app()->tenant->getId()]['FormSuccess'] = true;
} catch (PDOException $e) {

  throw new Exception('A database error occurred');
} catch (Exception $e) {

  $_SESSION['TENANT-' . app()->tenant->getId()]['FormError'] = $e->getMessage();
}

http_response_code(302);
header('location: ' . autoUrl('admin/tier-3'));
