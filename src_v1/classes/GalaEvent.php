<?php

/**
 * GALA EVENT CLASS
 */

class GalaEvent {

  private $price;
  private $enabled;

  public function __construct(private $name) {
    $this->price = 0;
    $this->enabled = false;
  }

  /**
   * Set the event name
   *
   * @return void
   */
  public function setName(string $name): void {
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
  public function enableEvent(): void {
    $this->enabled = true;
  }

  /**
   * Disable the event
   *
   * @return void
   */
  public function disableEvent(): void {
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
   * @return void
   */
  public function setPrice(int $price): void {
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
   * @return void
   */
  public function setPriceFromString(string $price): void {
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