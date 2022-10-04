<?php

/**
 * GALA EVENT CLASS
 */

class GalaEvent {

  private $name;
  private $price;
  private $enabled;

  public function __construct($name) {
    $this->name = $name;
    $this->price = 0;
    $this->enabled = false;
  }

  /**
   * Set the event name
   *
   * @param string $name
   * @return void
   */
  public function setName(string $name) {
    $this->name = $name;
  }

  /**
   * Get the event name
   *
   * @return string name
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Enable the event
   *
   * @return void
   */
  public function enableEvent() {
    $this->enabled = true;
  }

  /**
   * Disable the event
   *
   * @return void
   */
  public function disableEvent() {
    $this->enabled = false;
  }

  /**
   * Test if the event is enabled
   *
   * @return boolean is enabled
   */
  public function isEnabled() {
    return $this->enabled;
  }

  /**
   * Set the price of the event
   *
   * @param integer $price
   * @return void
   */
  public function setPrice(int $price) {
    $this->price = $price;
  }

  /**
   * Get the event price as an integer
   *
   * @return int price
   */
  public function getPrice() {
    return $this->price;
  }

  /**
   * Set the price from a decimal string
   *
   * @param string $price
   * @return void
   */
  public function setPriceFromString(string $price) {
    $this->price = \Brick\Math\BigDecimal::of((string) $price)->withPointMovedRight(2)->toBigInteger();
  }

  /**
   * Get the price formatted as a string
   *
   * @return string price
   */
  public function getPriceAsString() {
    return (string) (\Brick\Math\BigInteger::of((string) $this->getPrice()))->toBigDecimal()->withPointMovedLeft(2)->toScale(2);
  }

}