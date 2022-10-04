<?php

function registerSessionSelectGenerator($date, $sessionId = null)
{
  $sessions = TrainingSession::list([
    'date' => $date->format("Y-m-d"),
  ]);

?>

  <option value="none">Select a session</option>
  <?php foreach ($sessions as $session) {
    $squads = $session->getSquads();
  ?>
    <option <?php if ($session->getId() == $sessionId) { ?>selected<?php } ?> value="<?= htmlspecialchars($session->getId()) ?>"><?= htmlspecialchars($session->getName()) ?> (<?= htmlspecialchars($session->getStartTime()->format('H:i')) ?> - <?= htmlspecialchars($session->getEndTime()->format('H:i')) ?>), <?php for ($i = 0; $i < sizeof($squads); $i++) { ?><?php if ($i > 0) { ?>, <?php } ?><?= htmlspecialchars($squads[$i]->getName()) ?><?php } ?></option>
  <?php } ?>

<?php

}
