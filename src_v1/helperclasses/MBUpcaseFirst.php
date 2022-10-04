<?php

/**
 * Takes a string an upcases the first letter of each word. Other letters are 
 * not touched. This function should be used for upcasing names, for example 
 * when names are of the form AbAbcde.
 *
 * @param string $string
 * @return void
 */
function mb_upcase_words($string) {
  $words = mb_split(' ', $string);
  $returnString = "";

  for ($i = 0; $i < sizeof($words); $i++) {
    if ($i > 0) {
      $returnString .= ' ';
    }
    if (!(mb_strlen($words[$i]) > 1 && $words[$i][1] == "'")) {
      $words[$i][0] = mb_strtoupper($string);
    }
    $returnString .= $words[$i];
  }

  return $returnString;
}