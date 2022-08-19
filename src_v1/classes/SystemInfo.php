<?php

/**
 * System Class
 * Cannot store this in the session
 *
 * @copyright Chester-le-Street ASC https://github.com/Chester-le-Street-ASC
 * @author Chris Heppell https://github.com/clheppell
 */
class SystemInfo {
  private $db;
  private $systemOptions;
  private $systemOptionsRetrieved;
  private $serverEnvVar;

  public function __construct($db) {
    $this->db = $db;
    $this->systemOptionsRetrieved = false;
    $this->serverEnvVar = [];
  }

  public function revalidate() {
    // Get all options
    $this->getSystemOptions();
  }

  public function setExistingEnvVar($key) {
    $this->serverEnvVar[$key] = true;
  }

  public function unsetExistingEnvVar($key) {
    $this->serverEnvVar[$key] = false;
  }

  public function isExistingEnvVar($key) {
    if (isset($this->serverEnvVar[$key]) && $this->serverEnvVar[$key]) {
      return true;
    }
    return false;
  }

  private function getSystemOptions() {
    try {
      $getOptions = $this->db->query("SELECT `Option`, `Value` FROM systemOptions");
      $this->systemOptions = $getOptions->fetchAll(PDO::FETCH_KEY_PAIR);
      $this->systemOptionsRetrieved = true;
    } catch (Exception $e) {
      // Couldn't get options
    }
  }

  public function getSystemOption($name) {
    if (!$this->systemOptionsRetrieved) {
      $this->getSystemOptions();
    }
    // Get the options
    if (isset($this->systemOptions[$name])) {
      return $this->systemOptions[$name];
    } else {
      return null;
    }
  }

  public function getSystemBooleanOption($name) {
    return filter_var($this->getSystemOption($name), FILTER_VALIDATE_BOOLEAN);
  }

  public function setSystemOption($option, $value) {
    if (!is_numeric($value) && $value == "") {
      $value = null;
    }

    // Update value in memory
    $this->systemOptions[$option] = $value;

    // Any PDO exceptions will be propagated
    $query = $this->db->prepare("SELECT COUNT(*) FROM tenantOptions WHERE `Option` = ?");
    $query->execute([$option]);
    $result = $query->fetchColumn();

    if ($result == 0 && $value == null) {
      $query = $this->db->prepare("DELETE FROM tenantOptions WHERE `Option` = ?");
      $query->execute([$option]);
    } else if ($result == 0) {
      $query = $this->db->prepare("INSERT INTO tenantOptions (`Option`, `Value`) VALUES (?, ?)");
      $query->execute([$option, $value]);
    } else {
      $query = $this->db->prepare("UPDATE tenantOptions SET `Value` = ? WHERE `Option` = ?");
      $query->execute([$value, $option]);
    }
  }
}
