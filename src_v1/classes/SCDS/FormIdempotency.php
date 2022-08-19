<?php

namespace SCDS;

/**
 * FormIdempotency class which provides namespaced static functions for CSRF requests
 */
class FormIdempotency {
  public static function write() {
    // If the token is not set, define it
    if (!isset($_SESSION['FORM-IDEMPOTENCY']) || $_SESSION['FORM-IDEMPOTENCY'] == null) {
      $_SESSION['FORM-IDEMPOTENCY'] = hash('sha256', random_bytes(100));
    }

    echo '<input id="SCDS-FORM-IDEMPOTENCY" name="SCDS-FORM-IDEMPOTENCY" type="hidden" value="' . htmlspecialchars($_SESSION['FORM-IDEMPOTENCY']) . '">';
  }

  public static function verify() {
    $status = null;

    if (isset($_SESSION['FORM-IDEMPOTENCY']) && isset($_POST['SCDS-FORM-IDEMPOTENCY']) && $_SESSION['FORM-IDEMPOTENCY'] == $_POST['SCDS-FORM-IDEMPOTENCY']) {
      // Verifies idempotency, proceed normally
      $status = true;
    } else {
      $status = false;
    }

    $_SESSION['SCDS-FORM-IDEMPOTENCY'] = null;
    unset($_SESSION['SCDS-FORM-IDEMPOTENCY']);

    return $status;
  }
}