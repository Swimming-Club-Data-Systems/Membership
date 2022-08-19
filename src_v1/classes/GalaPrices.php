<?php

/**
 * GALA PRICING CLASS
 */

class GalaPrices {

  private $events;
  private $gala;
  private $db;

  public function __construct(PDO $db, int $gala) {
    $this->db = $db;
    $this->gala = $gala;

    // Verify the gala exists in the database
    $verifyGalaExists = $db->prepare("SELECT COUNT(*) FROM galas WHERE GalaID = ?");
    $verifyGalaExists->execute([$this->gala]);
    if ($verifyGalaExists->fetchColumn() == 0) {
      throw new Exception('Gala does not exist');
    }

    // Get the events and pricing
    $getEvents = $db->prepare("SELECT Prices, Events FROM galaData WHERE Gala = ?");
    $getEvents->execute([$this->gala]);
    $data = $getEvents->fetch(PDO::FETCH_ASSOC);

    $sysEvents = GalaEvents::getEvents();

    $this->events = [];
    foreach ($sysEvents as $key => $value) {
      $this->events[$key] = new GalaEvent($key);
    }

    if ($data != null) {
      // Data exists for this gala
      // Data is stored in the JSON format

      try {
        $events = json_decode($data['Events']);
        $prices = json_decode($data['Prices'], true);

        // Events then prices
        foreach ($sysEvents as $key => $value) {
          if (isset($events->$key) && $events->$key) {
            $this->events[$key]->enableEvent();
            $this->events[$key]->setPrice($prices[$key]);
          }
        }
      } catch (Exception $e) {
        // What to do?
      }
    } else {
      // There's no data for this gala so assume all events are allowed at the default price
    }
  }

  /**
   * Get the event object
   *
   * @param string $event
   * @return GalaEvent
   */
  public function getEvent(string $event) {
    if (isset($this->events[$event])) {
      return $this->events[$event];
    } else {
      return null;
    }
  }

  /**
   * Check if an event is allowed at this gala
   *
   * @param string $event the name of the event at the gala
   * @return boolean true if event can be swam
   */
  public function isEvent(string $event) {
    // Check if this event is allowed
    if (isset($events) && isset($events['Events'][$event]) && $events['Events'][$event]) {
      return true;
    }
    return false;
  }

  /**
   * Save the gala data to the database
   *
   * @return void
   */
  public function save() {
    // Turn data for events and prices into JSON data
    $outputEvents = GalaEvents::getEvents();
    $outputPrices = GalaEvents::getEvents();

    foreach ($this->events as $key => $event) {
      if ($event->isEnabled()) {
        $outputEvents[$key] = true;
        $outputPrices[$key] = $event->getPrice();
      } else {
        $outputEvents[$key] = false;
        $outputPrices[$key] = 0;
      }
    }

    // Check if data exists first
    $dataCount = $this->db->prepare("SELECT COUNT(*) FROM galaData WHERE Gala = ?");
    $dataCount->execute([$this->gala]);

    if ($dataCount->fetchColumn() == 0) {
      $insert = $this->db->prepare("INSERT INTO galaData (Gala, Events, Prices) VALUES (?, ?, ?)");
      $insert->execute([
        $this->gala,
        json_encode($outputEvents),
        json_encode($outputPrices)
      ]);
    } else {
      $update = $this->db->prepare("UPDATE galaData SET Events = ?, Prices = ? WHERE Gala = ?");
      $update->execute([
        json_encode($outputEvents),
        json_encode($outputPrices),
        $this->gala
      ]);
    }
  }

  public function setupDefault() {
    // Get the standard price
    $getPrice = $this->db->prepare("SELECT GalaFee FROM galas WHERE GalaID = ?");
    $getPrice->execute([$this->gala]);
    $priceFromDb = $getPrice->fetchColumn();

    $price = \Brick\Math\BigDecimal::of((string) $priceFromDb);
    $price = (string) $price->withPointMovedRight(2)->toBigInteger();

    foreach ($this->events as $key => $event) {
      $event->enableEvent();
      $event->setPrice($price);
    }

    $this->save();
  }

}