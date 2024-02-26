<?php

function getContrastColor($hexColor) {

  // hexColor RGB
  $R1 = hexdec(substr((string) $hexColor, 1, 2));
  $G1 = hexdec(substr((string) $hexColor, 3, 2));
  $B1 = hexdec(substr((string) $hexColor, 5, 2));

  // Black RGB
  $blackColor = "#000000";
  $R2BlackColor = hexdec(substr($blackColor, 1, 2));
  $G2BlackColor = hexdec(substr($blackColor, 3, 2));
  $B2BlackColor = hexdec(substr($blackColor, 5, 2));

   // Calc contrast ratio
   $L1 = 0.2126 * ($R1 / 255) ** 2.2 +
         0.7152 * ($G1 / 255) ** 2.2 +
         0.0722 * ($B1 / 255) ** 2.2;

  $L2 = 0.2126 * ($R2BlackColor / 255) ** 2.2 +
        0.7152 * ($G2BlackColor / 255) ** 2.2 +
        0.0722 * ($B2BlackColor / 255) ** 2.2;

  $contrastRatio = 0;
  if ($L1 > $L2) {
    $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
  } else {
    $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
  }

  // If contrast is more than 5, return black color
  if ($contrastRatio > 5) {
    return true;
  } else { 
    // if not, return white color.
    return false;
  }
}