<?php

echo "Running";

$file = fopen(BASE_PATH . "controllers/dev/NER.SD3","r");

pre($file);

while (!feof($file)) {
  $line = fgets($file);
  // pre($line);

  if (mb_strlen($line) > 1 && substr($line, 0, 2) == 'B1') {
    try {
      $b1 = new \CLSASC\SDIF\B1();
      $b1->createFromLine($line);
      pre([
        $b1->getMeetName(),
        $b1->getMeetCity(),
        $b1->getMeetStartDate(),
        $b1->getMeetEndDate()
      ]);
    } catch (Exception | Error $e) {
      pre($e);
    }
  }

  if (mb_strlen($line) > 1 && substr($line, 0, 2) == 'D0') {
    try {
      $d0 = new \CLSASC\SDIF\D0();
      $d0->createFromLine($line);

      $prelims = [];
      if ($d0->hasPrelimTime()) {
        $prelims = [
          'time' => $d0->getPrelim(),
          'intTime' => \CLSASC\SDIF\D0::timeAsInt($d0->getPrelim()),
          'course' => $d0->getPrelimCourse(),
          'chronOrder' => 0,
        ];
      }

      $swimOffs = [];
      if ($d0->hasSwimOffTime()) {
        $swimOffs = [
          'time' => $d0->getSwimOff(),
          'intTime' => \CLSASC\SDIF\D0::timeAsInt($d0->getSwimOff()),
          'course' => $d0->getSwimOffCourse(),
          'chronOrder' => 1,
        ];
      }

      $finals = [];
      if ($d0->hasFinalsTime()) {
        $finals = [
          'time' => $d0->getFinals(),
          'intTime' => \CLSASC\SDIF\D0::timeAsInt($d0->getFinals()),
          'course' => $d0->getFinalsCourse(),
          'chronOrder' => 2,
        ];
      }

      pre([
        $d0->getSwimmerNumber(),
        $d0->getDateOfBirthDB(),
        $d0->getDateOfSwimDB(),
        'prelims' => $prelims,
        'swimOffs' => $swimOffs,
        'finals' => $finals,
      ]);
    } catch (Exception | Error $e) {
      pre($e);
    }
  }
}

fclose($file);

echo "Done";
