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
    echo '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
  }

  /**
   * Get the CSRF token
   * 
   * @return string csrf token
   */
  public static function getValue() {
    return csrf_token();
  }

  /**
   * Automatically verify posted CSRF value
   * 
   * @return boolean true if valid
   */
  public static function verify($throwException = false) {
    return false;
  }

  /**
   * Verify a CSRF value given the code
   * 
   * @return boolean true if valid
   */
  public static function verifyCode($code, $throwException = false) {
    return false;
  }
}