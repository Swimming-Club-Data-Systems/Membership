<?php

function chartColours($counts) {
  $colours = [];

  for ($i=0; $i < $counts; $i++) {

    $match = $i%8;

    switch ($match) {
      case 0:
        $colours[] = '#dc3545';
        break;
      case 1:
        $colours[] = '#fd7e14';
        break;
      case 2:
        $colours[] = '#ffc107';
        break;
      case 3:
        $colours[] = '#28a745';
        break;
      case 4:
        $colours[] = '#20c997';
        break;
      case 5:
        $colours[] = '#6610f2';
        break;
      case 6:
        $colours[] = '#6f42c1';
        break;
      case 7:
        $colours[] = '#e83e8c';
        break;
      default:
        $colours[] = '#dc3545';
        break;
    }
    
  }

  return $colours;
}