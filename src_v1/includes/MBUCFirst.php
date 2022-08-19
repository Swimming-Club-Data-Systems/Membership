<?php

if (!function_exists('mb_ucfirst'))
{
function mb_ucfirst ($str, $encoding = "UTF-8", $lower_str_end = false)
{
$first_letter = mb_strtoupper (mb_substr ($str, 0, 1, $encoding), $encoding);
$str_end = mb_substr ($str, 1, mb_strlen ($str, $encoding), $encoding);

return $first_letter.($lower_str_end ? mb_strtolower ($str_end) : $str_end);
}
}