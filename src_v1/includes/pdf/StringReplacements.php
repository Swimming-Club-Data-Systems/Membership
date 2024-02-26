<?php

function pdfStringReplace($content) {
  // /<div class="page-break"></div>
  $search = ['<p>-page-break-</p>'];
  $replace = ['<div class="page-break"></div>'];

  $content = str_replace($search, $replace, (string) $content);
  return $content;
}