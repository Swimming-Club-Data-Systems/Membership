<?php

try {
  $db = app()->db;
  $tenant = app()->tenant;

  // Check gala
  $getGala = $db->prepare("SELECT COUNT(*) FROM galas WHERE GalaID = ? AND Tenant = ?");
  $getGala->execute([
    $id,
    $tenant->getId()
  ]);
  if ($getGala->fetchColumn() == 0) {
    throw new Exception();
  }

  $delete = $db->prepare("DELETE FROM galaSessions WHERE Gala = ? AND ID = ?");
  $delete->execute([$id, $session]);
  header("Location: " . autoUrl("galas/" . $id . "/sessions"));
} catch (Exception $e) {
  halt(404);
}