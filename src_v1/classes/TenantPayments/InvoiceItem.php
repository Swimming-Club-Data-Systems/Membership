<?php

namespace TenantPayments;

class InvoiceItem
{

  private $id;
  private $invoice;
  private $description;
  private $amount;
  private $currency;
  private $type;
  private $quantity;
  private $pricePerUnit;
  private $vatAmount;
  private $vatRate;

  private function __construct($data)
  {
    $this->id = $data['ID'];
    $this->invoice = $data['Invoice'];
    $this->description = json_decode($data['Description']);
    $this->amount = $data['Amount'];
    $this->currency = $data['Currency'];
    $this->type = $data['Type'];
    $this->quantity = $data['Quantity'];
    $this->pricePerUnit = $data['PricePerUnit'];
    $this->vatAmount = $data['VATAmount'];
    $this->vatRate = $data['VATRate'];
  }

  public static function get($id)
  {
    $db = app()->db;
    $getItem = $db->prepare("SELECT * FROM tenantPaymentInvoiceItems WHERE ID = ?");
    $getItem->execute([
      $id,
    ]);

    $item = $getItem->fetch(\PDO::FETCH_ASSOC);

    if (!$item) throw new \Exception('Item not found');

    return new InvoiceItem($item);
  }

  public static function getFromInvoice($id)
  {
    $db = app()->db;
    $getItem = $db->prepare("SELECT * FROM tenantPaymentInvoiceItems WHERE Invoice = ?");
    $getItem->execute([
      $id,
    ]);

    $items = $getItem->fetchAll(\PDO::FETCH_ASSOC);

    if (sizeof($items) == 0) throw new \Exception('Items not found');

    $items = array_map(function ($data) {
      return new InvoiceItem($data);
    }, $items);

    return $items;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->description->product->name;
  }

  public function getDescription()
  {
    return $this->description->plan_description->name;
  }

  public function getAmount()
  {
    return $this->amount;
  }

  public function getFormattedAmount()
  {
    return \MoneyHelpers::formatCurrency(\MoneyHelpers::intToDecimal($this->getAmount()), $this->getCurrency());
  }

  public function getCurrency()
  {
    return $this->currency;
  }

  public function getType()
  {
    return $this->type;
  }

  public function getQuantity()
  {
    return $this->quantity;
  }

  public function getPricePerUnit()
  {
    return $this->pricePerUnit;
  }

  public function getFormattedPricePerUnit()
  {
    return \MoneyHelpers::formatCurrency(\MoneyHelpers::intToDecimal($this->getPricePerUnit()), $this->getCurrency());
  }

  public function getVatAmount()
  {
    return $this->vatAmount;
  }

  public function getFormattedVatAmount()
  {
    return \MoneyHelpers::formatCurrency(\MoneyHelpers::intToDecimal($this->getVatAmount()), $this->getCurrency());
  }

  public function getVatRate()
  {
    return $this->vatRate;
  }
}
