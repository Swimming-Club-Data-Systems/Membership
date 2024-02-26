<?php

function chartColours($counts) {
  $colours = [];

  for ($i=0; $i < $counts; $i++) {

    $match = $i%8;

    $colours[] = match ($match) {
        0 => '#dc3545',
        1 => '#fd7e14',
        2 => '#ffc107',
        3 => '#28a745',
        4 => '#20c997',
        5 => '#6610f2',
        6 => '#6f42c1',
        7 => '#e83e8c',
        default => '#dc3545',
    };
    
  }

  return $colours;
}