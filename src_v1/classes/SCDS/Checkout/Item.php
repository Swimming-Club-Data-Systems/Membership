<?php

namespace SCDS\Checkout;

use Exception;

class Item
{
  public $id;
  public $session;
  public $name;
  public $description;
  public $amount;
  public $currency;
  public $taxAmount;
  public $taxData;
  public $subItems;
  public $type;
  public $attributes;
  public $metadata;

  private function __construct()
  {
  }

  // public static function new($data)
  // {
  //   $id = \Ramsey\Uuid\Uuid::uuid4();

  //   $item = new Item();

  //   $item->name = $data['name'] ?? 'Item';
  //   $item->description = $data['description'] ?? null;
  //   $item->amount = $data['amount'] ?? 0;
  //   $item->currency = $data['currency'] ?? 'gbp';
  //   $item->taxAmount = $data['tax_amount'] ?? 0;
  //   $item->taxData = json_encode($data['tax_data'] ?? []);
  //   $item->subItems = json_encode($data['sub_items'] ?? []);
  //   $item->type = $data['type'] ?? 'debit';
  //   $item->attributes = json_encode($data['attributes'] ?? []);
  //   $item->metadata = json_encode($data['metadata'] ?? []);

  //   return $item;
  // }

  public static function retrieve($id)
  {
    $db = app()->db;

    $get = $db->prepare("SELECT * FROM `checkoutItems` WHERE `id` = ?");
    $get->execute([
      $id,
    ]);

    $itemInfo = $get->fetch(\PDO::FETCH_OBJ);

    if (!$itemInfo) throw new Exception('No checkout item');

    $item = new Item();

    $item->id = $id;
    $item->session = $itemInfo->checkout_session;
    $item->name = $itemInfo->name;
    $item->description = $itemInfo->description;
    $item->amount = $itemInfo->amount;
    $item->currency = $itemInfo->currency;
    $item->taxAmount = $itemInfo->tax_amount;
    $item->taxData = json_decode($itemInfo->tax_data);
    $item->subItems = json_decode($itemInfo->sub_items);
    $item->type = $itemInfo->type;
    $item->attributes = json_decode($itemInfo->attributes);
    $item->metadata = json_decode($itemInfo->metadata);

    return $item;
  }

  public function save()
  {
    $db = app()->db;

    $save = $db->prepare("UPDATE `checkoutItems` SET `name` = ?, `description` = ?, `amount` = ?, `currency` = ?, `tax_amount` = ?, `tax_data` = ?, `sub_items` = ?, `type` = ?, `attributes` = ?, `metadata` = ? WHERE `id` = ?");
    $save->execute([
      $this->name,
      $this->description,
      $this->amount,
      $this->currency,
      $this->taxAmount,
      json_encode($this->taxData),
      json_encode($this->subItems),
      $this->type,
      json_encode($this->attributes),
      json_encode($this->metadata),
    ]);
  }
}
