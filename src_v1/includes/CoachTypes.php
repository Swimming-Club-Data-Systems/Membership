<?php

/**
 * Get a coach role description (string)
 *
 * @param string $coach role code
 * @return string coach role description
 */
function coachTypeDescription($type) {
  switch ($type) {
    case 'LEAD_COACH':
      return 'Lead Coach';
      break;

    case 'COACH':
      return 'Coach';
      break;

    case 'ASSISTANT_COACH':
      return 'Assistant Coach';
      break;

    case 'TEACHER':
      return 'Teacher';
      break;

    case 'HELPER':
      return 'Helper';
      break;

    case 'ADMINISTRATOR':
      return 'Squad Administrator';
      break;
    
    default:
      return 'Unknown Coach Type';
      break;
  }
}