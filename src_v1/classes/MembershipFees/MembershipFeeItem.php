<?php

namespace MembershipFees;

class MembershipFeeItem
{

  public function __construct(private $description, private $amount, private $member)
  {
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function getAmount()
  {
    return $this->amount;
  }

  public function getFormattedAmount()
  {
    return (string) (\Brick\Math\BigDecimal::of((string) $this->getAmount()))->withPointMovedLeft(2)->toScale(2);
  }

  public function getMember()
  {
    return $this->member;
  }

  public function setAmount($amount): void {
    $this->amount = $amount;
  }
}
