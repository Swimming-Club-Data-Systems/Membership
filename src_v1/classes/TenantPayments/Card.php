<?php

namespace TenantPayments;

class Card extends PaymentMethod
{
  private $last4;

  public function getBrandName()
  {
    return getCardBrand($this->typeData->brand);
  }

  public function getExpiry()
  {
    return null;
  }

  public function getLast4()
  {
    return $this->last4;
  }
}
