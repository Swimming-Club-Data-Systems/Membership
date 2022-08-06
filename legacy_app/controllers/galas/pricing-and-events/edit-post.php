<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

// Check gala
$getGalas = $db->prepare("SELECT COUNT(*) FROM galas WHERE GalaID = ? AND Tenant = ?");
$getGalas->execute([
  $id,
  $tenant->getId()
]);
if ($getGalas->fetchColumn() == 0) {
  halt(404);
}

try {
  // Get price and event information
  $galaData = new GalaPrices($db, $id);

  $swimsArray = GalaEvents::getEvents();

  foreach ($swimsArray as $key => $value) {
    $event = $galaData->getEvent($key);

    if (bool($_POST[$key . '-check'])) {
      $event->enableEvent();
      $event->setPriceFromString((string) $_POST[$key . '-price']);
    } else {
      $event->disableEvent();
    }
  }

  $galaData->save();

  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['PricesSaved'] = true;
} catch (Exception $e) {
  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['PricesNotSaved'] = true;
}

header("Location: " . autoUrl("galas/" . $id . "/pricing-and-events"));