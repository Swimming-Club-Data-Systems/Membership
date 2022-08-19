<?php

namespace TenantPayments;

class BacsDebit extends PaymentMethod
{
  private $last4;
  private $reference;
  private $sortCode;

  public function getBrandName()
  {
    return 'BACS Direct Debit';
  }

  public function getExpiry()
  {
    return null;
  }

  public function getLast4()
  {
    return $this->last4;
  }

  public function getReference()
  {
    return $this->reference;
  }

  public function getSortCode()
  {
    return $this->sortCode;
  }

  public function getFormattedLast4()
  {
    return $this->getLast4();
  }

  public function getFormattedSortCode()
  {
    return $this->getSortCode();
  }
}
