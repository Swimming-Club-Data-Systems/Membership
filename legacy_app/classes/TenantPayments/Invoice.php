<?php

namespace TenantPayments;

class Invoice
{

  private $id;
  private $reference;
  private $customer;
  private $paymentIntent;
  private $date;
  private $supplyDate;
  private $company;
  private $currency;
  private $paymentTerms;
  private $howToPay;
  private $purchaseOrderNumber;
  private $amountPaidCash;
  private $paidDate;
  private $paid;
  private $created;
  private $updated;
  private $items;

  private $amount;
  private $vat;
  private $total;

  private function __construct($data)
  {
    $this->id = $data['ID'];
    $this->reference = $data['Reference'];
    $this->customer = $data['Customer'];
    $this->paymentIntent = $data['PaymentIntent'];
    $this->date = new \DateTime($data['Date'], new \DateTimeZone('Europe/London'));
    $this->supplyDate = new \DateTime($data['SupplyDate'], new \DateTimeZone('Europe/London'));
    $this->company = json_decode($data['Company']);
    $this->currency = $data['Currency'];
    $this->paymentTerms = $data['PaymentTerms'];
    $this->howToPay = $data['HowToPay'];
    $this->purchaseOrderNumber = $data['PurchaseOrderNumber'];
    $this->amountPaidCash = $data['AmountPaidCash'];
    if ($data['PaidDate']) {
      $this->paidDate = new \DateTime($data['PaidDate'], new \DateTimeZone('Europe/London'));
    } else {
      $this->paidDate = null;
    }
    $this->paid = $data['Paid'];
    $this->items = InvoiceItem::getFromInvoice($this->id);
  }

  public static function get($id)
  {
    $db = app()->db;
    $getInvoice = $db->prepare("SELECT tenantPaymentInvoices.ID, `Reference`, tenantPaymentInvoices.Customer, `PaymentIntent`, `Date`, `SupplyDate`, `Company`, tenantPaymentInvoices.Currency, `PaymentTerms`, `HowToPay`, `PurchaseOrderNumber`, `AmountPaidCash`, `PaidDate`, `Paid`, `Amount`, `Status`, `Shipping`, `PaymentMethod`, `BillingDetails`, `Type`, `TypeData`, tenantPaymentInvoices.Created, tenantPaymentInvoices.Updated FROM `tenantPaymentInvoices` LEFT JOIN `tenantPaymentIntents` ON tenantPaymentInvoices.PaymentIntent = tenantPaymentIntents.IntentID LEFT JOIN `tenantPaymentMethods` ON tenantPaymentMethods.MethodID = tenantPaymentIntents.PaymentMethod WHERE tenantPaymentInvoices.ID = ?");
    $getInvoice->execute([
      $id
    ]);

    $invoice = $getInvoice->fetch(\PDO::FETCH_ASSOC);

    if (!$invoice) throw new \Exception('No invoice');

    return new Invoice($invoice);
  }

  public function getId()
  {
    return $this->id;
  }

  public function getReference()
  {
    return $this->reference;
  }

  public function getDate()
  {
    return $this->date;
  }

  public function getSupplyDate()
  {
    return $this->supplyDate;
  }

  private function totalise()
  {
    if (!$this->amount) {
      $amount = 0;
      $vat = 0;
      foreach ($this->items as $item) {
        $amount += $item->getAmount();
        $vat += $item->getVatAmount();
      }

      $this->amount = $amount;
      $this->vat = $vat;
      $this->total = $amount + $vat;
    }
  }

  public function getCurrency()
  {
    return $this->currency;
  }

  public function getTotal()
  {
    $this->totalise();
    return $this->amount;
  }

  public function getFormattedTotal()
  {
    return \MoneyHelpers::formatCurrency(\MoneyHelpers::intToDecimal($this->getTotal()), $this->getCurrency());
  }

  public function getTotalWithVat()
  {
    $this->totalise();
    return $this->total;
  }

  public function getFormattedTotalWithVat()
  {
    return \MoneyHelpers::formatCurrency(\MoneyHelpers::intToDecimal($this->getTotalWithVat()), $this->getCurrency());
  }

  public function getVatTotal()
  {
    $this->totalise();
    return $this->vat;
  }

  public function getFormattedVatTotal()
  {
    return \MoneyHelpers::formatCurrency(\MoneyHelpers::intToDecimal($this->getVatTotal()), $this->getCurrency());
  }

  public function getItems()
  {
    return $this->items;
  }

  public function getPaymentTerms() {
    return $this->paymentTerms;
  }

  public function getHowToPay() {
    return $this->howToPay;
  }
}
