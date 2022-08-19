<?php

namespace SCDS;

/**
 * CSRF class which provides namespaced static functions for CSRF requests
 */
class CSRF {
  /**
   * Automatically format the CSRF token for use in an HTML form
   */
  public static function write() {
    // If the token is not set, define it
    $csrfName = 'CSRF';
    if (isset(app()->tenant)) {
      $csrfName = 'CSRF-T' . app()->tenant->getId();
    }

    self::getValue();

    echo '<input id="SCDS-GLOBAL-CSRF" name="SCDS-GLOBAL-CSRF" type="hidden" value="' . htmlspecialchars($_SESSION[$csrfName]) . '">';
  }

  /**
   * Get the CSRF token
   * 
   * @return string csrf token
   */
  public static function getValue() {
    $csrfName = 'CSRF';
    if (isset(app()->tenant)) {
      $csrfName = 'CSRF-T' . app()->tenant->getId();
    }

    if (!isset($_SESSION[$csrfName]) || $_SESSION[$csrfName] == null) {
      $_SESSION[$csrfName] = hash('sha256', random_bytes(100));
    }

    return $_SESSION[$csrfName];
  }

  /**
   * Automatically verify posted CSRF value
   * 
   * @return boolean true if valid
   */
  public static function verify($throwException = false) {
    $csrfName = 'CSRF';
    if (isset(app()->tenant)) {
      $csrfName = 'CSRF-T' . app()->tenant->getId();
    }

    if (isset($_SESSION[$csrfName]) && isset($_POST['SCDS-GLOBAL-CSRF']) && $_SESSION[$csrfName] == $_POST['SCDS-GLOBAL-CSRF']) {
      // Verifies CSRF, proceed normally
      return true;
    } else if ($throwException) {
      throw new CSRFValidityException('Invalid Cross-Site Request Forgery Token');
    } else {
      return false;
    }
  }

  /**
   * Verify a CSRF value given the code
   * 
   * @return boolean true if valid
   */
  public static function verifyCode($code, $throwException = false) {
    $csrfName = 'CSRF';
    if (isset(app()->tenant)) {
      $csrfName = 'CSRF-T' . app()->tenant->getId();
    }
    
    if (isset($_SESSION[$csrfName]) && $_SESSION[$csrfName] == $code) {
      // Verifies CSRF, proceed normally
      return true;
    } else if ($throwException) {
      throw new CSRFValidityException('Invalid Cross-Site Request Forgery Token');
    } else {
      return false;
    }
  }
}