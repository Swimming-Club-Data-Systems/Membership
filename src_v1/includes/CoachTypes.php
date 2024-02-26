<?php

/**
 * Get a coach role description (string)
 *
 * @param string $coach role code
 * @return string coach role description
 */
function coachTypeDescription($type) {
  return match ($type) {
      'LEAD_COACH' => 'Lead Coach',
      'COACH' => 'Coach',
      'ASSISTANT_COACH' => 'Assistant Coach',
      'TEACHER' => 'Teacher',
      'HELPER' => 'Helper',
      'ADMINISTRATOR' => 'Squad Administrator',
      default => 'Unknown Coach Type',
  };
}