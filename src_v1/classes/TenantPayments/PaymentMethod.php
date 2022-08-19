<?php

namespace TenantPayments;

abstract class PaymentMethod
{
  private $id;
  private $dbId;
  private $customer;
  private $tenant;
  private $billing;
  private $type;
  private $typeData;
  private $fingerprint;
  private $usable;
  private $created;

  public function getId()
  {
    return $this->id;
  }

  public function getDbId()
  {
    return $this->dbId;
  }

  public function getCustomer()
  {
    return $this->customer;
  }

  public function getTenant()
  {
    return \Tenant::fromId($this->tenant);
  }

  public function getBillingAddress()
  {
    return $this->billing->address;
  }

  public function getBillingName()
  {
    return $this->billing->name;
  }

  public function getBillingEmail()
  {
    return $this->billing->email;
  }

  public function getBillingPhone()
  {
    return $this->billing->phone;
  }

  public function getType() {
    return $this->type;
  }

  public function getTypeDataJSON() {
    return $this->typeData;
  }

  abstract public function getBrandName();
  abstract public function getExpiry();
  abstract public function getLast4();

  public function getFingerprint()
  {
    return $this->fingerprint;
  }

  public function isUsable()
  {
    return bool($this->usable);
  }

  public function getCreationTime()
  {
    return $this->created;
  }
}
