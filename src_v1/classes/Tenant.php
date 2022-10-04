<?php

class Tenant
{
  private int $id;
  private string $name;
  private $code;
  private $website;
  private $email;
  private $verified;
  private $keys;
  private string $uuid;
  private $goCardlessAccessToken;
  private $goCardlessOrganisationId;
  private $goCardlessLoaded = false;
  private $stripeAccountId;
  private $domain;

  public function __construct($details)
  {
    $this->id = $details['ID'];
    $this->name = $details['Name'];
    $this->code = $details['Code'];
    $this->website = $details['Website'];
    $this->email = $details['Email'];
    $this->verified = $details['Verified'];
    $this->uuid = $details['UniqueID'];
    $this->domain = $details['Domain'];

    $this->getKeys();
  }

  /**
   * Get a tenant by ID
   * 
   * @param int id
   * @return Tenant|null tenant object if exists
   */
  public static function fromId(int $id)
  {
    $db = app()->db;
    $getTenant = $db->prepare("SELECT `ID`, `Name`, `Code`, `Website`, `Email`, `Verified`, `UniqueID`, `Domain` FROM tenants WHERE ID = ?");
    $getTenant->execute([
      $id
    ]);
    $tentantDetails = $getTenant->fetch(PDO::FETCH_ASSOC);

    if ($tentantDetails) {
      return new Tenant($tentantDetails);
    }
    return null;
  }

  /**
   * Get a tenant by four letter code
   * 
   * @param string code
   * @return Tenant|null tenant object if exists
   */
  public static function fromCode(string $code)
  {
    $db = app()->db;
    $getTenant = $db->prepare("SELECT `ID`, `Name`, `Code`, `Website`, `Email`, `Verified`, `UniqueID`, `Domain` FROM tenants WHERE Code COLLATE utf8mb4_general_ci = ?");
    $getTenant->execute([
      $code
    ]);
    $tentantDetails = $getTenant->fetch(PDO::FETCH_ASSOC);

    if ($tentantDetails) {
      return new Tenant($tentantDetails);
    }
    return null;
  }

  /**
   * Get a tenant by domain name
   * 
   * @param string domain name
   * @return Tenant|null tenant object if exists
   */
  public static function fromDomain(string $domain)
  {
    $db = app()->db;
    $getTenant = $db->prepare("SELECT `ID`, `Name`, `Code`, `Website`, `Email`, `Verified`, `UniqueID`, `Domain` FROM tenants WHERE `Domain` COLLATE utf8mb4_general_ci = ?");
    $getTenant->execute([
      $domain
    ]);
    $tentantDetails = $getTenant->fetch(PDO::FETCH_ASSOC);

    if ($tentantDetails) {
      return new Tenant($tentantDetails);
    }
    return null;
  }

  /**
   * Get a tenant by UUID
   * 
   * @param string tenant UUID
   * @return Tenant|null tenant object if exists
   */
  public static function fromUUID(string $uuid)
  {
    $db = app()->db;
    $getTenant = $db->prepare("SELECT `ID`, `Name`, `Code`, `Website`, `Email`, `Verified`, `UniqueID`, `Domain` FROM tenants WHERE `UniqueID` COLLATE utf8mb4_general_ci = ?");
    $getTenant->execute([
      $uuid
    ]);
    $tentantDetails = $getTenant->fetch(PDO::FETCH_ASSOC);

    if ($tentantDetails) {
      return new Tenant($tentantDetails);
    }
    return null;
  }

  /**
   * Get tenant keys from db
   */
  private function getKeys()
  {
    $db = app()->db;
    $getKeys = $db->prepare("SELECT Option, Value FROM tenantOptions WHERE Tenant = ?");
    $getKeys->execute([
      $this->id
    ]);
    $keys = $getKeys->fetchAll(PDO::FETCH_KEY_PAIR);

    $defaultKeys = [
      'CLUB_NAME' => null,
      'CLUB_SHORT_NAME' => null,
      'ASA_CLUB_CODE' => null,
      'CLUB_EMAIL' => null,
      'CLUB_TRIAL_EMAIL' => null,
      'CLUB_WEBSITE' => null,
      'GOCARDLESS_USE_SANDBOX' => null,
      'GOCARDLESS_SANDBOX_ACCESS_TOKEN' => null,
      'GOCARDLESS_ACCESS_TOKEN' => null,
      'GOCARDLESS_WEBHOOK_KEY' => null,
      'CLUB_ADDRESS' => null,
      'SYSTEM_COLOUR' => '#007bff',
      'ASA_DISTRICT' => 'E',
      'ASA_COUNTY' => 'NDRE',
      'STRIPE' => null,
      'STRIPE_PUBLISHABLE' => null,
      'STRIPE_APPLE_PAY_DOMAIN' => null,
      'EMERGENCY_MESSAGE' => false,
      'EMERGENCY_MESSAGE_TYPE' => 'NONE',
      'GALA_CARD_PAYMENTS_ALLOWED' => true,
      'REQUIRE_FULL_REGISTRATION' => true,
      'REQUIRE_FULL_RENEWAL' => true,
      'USE_DIRECT_DEBIT' => true,
      'MEMBERSHIP_FEE_PM_CARD' => true,
      'MEMBERSHIP_FEE_PM_DD' => true,
      'ENABLE_BILLING_SYSTEM' => true,
      'NGB_NAME' => 'Swim England',
      'REQUIRE_SQUAD_REP_FOR_APPROVAL' => true,
      'DISPLAY_NAME_FORMAT' => 'FL',
      'DEFAULT_GALA_PROCESSING_FEE' => 0,
    ];

    foreach ($keys as $key => $value) {
      $defaultKeys[$key] = $value;
    }

    $defaultKeys['CLUB_NAME'] = $this->name;
    if ($this->code) {
      $defaultKeys['ASA_CLUB_CODE'] = mb_strtoupper($this->code);
    }

    $this->keys = $defaultKeys;
  }

  /**
   * Get the tenant id
   * 
   * @return int tenant id
   */
  public function getId()
  {
    return $this->id;
  }

  public function __toString()
  {
    if ($this->uuid) {
      return $this->uuid;
    }
    return spl_object_hash($this->uuid);
  }

  /**
   * Get the club code
   * 
   * @return string club code
   */
  public function getCode()
  {
    return $this->code;
  }

  /**
   * Get the club name
   * 
   * @return string club name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Get the tenant domain name
   * 
   * @return string domain
   */
  public function getDomain()
  {
    return $this->domain;
  }

  /**
   * Get the club website
   * 
   * @return string club website address
   */
  public function getWebsite()
  {
    if ($this->getKey('CLUB_WEBSITE')) {
      return $this->getKey('CLUB_WEBSITE');
    }
    return $this->website;
  }

  /**
   * Get club admin contact email
   * 
   * @return string email address
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * Is this club account verified?
   * 
   * @return boolean verification status
   */
  public function isVerified()
  {
    return $this->verified;
  }

  /**
   * Get tenant key
   * 
   * @param string key
   * @return string value
   */
  public function getKey(string $key)
  {
    if (isset($this->keys[$key])) {
      return $this->keys[$key];
    }
    return null;
  }

  /**
   * Get tenant key as a boolean
   * 
   * @param string key
   * @return boolean value
   */
  public function getBooleanKey(string $key)
  {
    return filter_var($this->getKey($key), FILTER_VALIDATE_BOOLEAN);
  }

  /**
   * Set tenant key
   * 
   * @param string key
   * @param mixed value
   * @return boolean status
   * @throws PDOException
   */
  public function setKey(string $key, $value)
  {
    $db = app()->db;

    if (!is_numeric($value) && $value == "") {
      $value = null;
    }

    // Update value in memory
    $this->keys[$key] = $value;

    // Any PDO exceptions will be propagated
    $query = $db->prepare("SELECT COUNT(*) FROM tenantOptions WHERE `Option` = ? AND `Tenant` = ?");
    $query->execute([$key, $this->id]);
    $result = $query->fetchColumn();

    $res = false;
    if ($result > 0 && $value === null) {
      $query = $db->prepare("DELETE FROM tenantOptions WHERE `Option` = ? AND `Tenant` = ?");
      $res = $query->execute([$key, $this->id]);
    } else if ($result == 0 && $value !== null) {
      $query = $db->prepare("INSERT INTO tenantOptions (`Option`, `Value`, `Tenant`) VALUES (?, ?, ?)");
      $res = $query->execute([$key, $value, $this->id]);
    } else if ($result > 0) {
      $query = $db->prepare("UPDATE tenantOptions SET `Value` = ? WHERE `Option` = ? AND `Tenant` = ?");
      $res = $query->execute([$value, $key, $this->id]);
    }

    return $res;
  }

  /**
   * Get the code or id
   * 
   * @return string|int code if exists, else id
   */
  public function getCodeId()
  {
    if ($this->code) {
      return mb_strtolower($this->code);
    }
    return $this->id;
  }

  /**
   * Get a tenant's UUID
   * 
   * @return string uuid
   */
  public function getUuid()
  {
    return $this->uuid;
  }

  /**
   * Fetch GoCardless details from the database
   * 
   * To be called only once when required
   */
  private function loadGoCardless()
  {
    $db = app()->db;
    $getKey = $db->prepare("SELECT OrganisationId, AccessToken FROM gcCredentials WHERE Tenant = ?");
    $getKey->execute([
      $this->id
    ]);

    $keys = $getKey->fetch(PDO::FETCH_ASSOC);

    if ($keys) {
      $this->goCardlessAccessToken = $keys['AccessToken'];
      $this->goCardlessOrganisationId = $keys['OrganisationId'];
    }
    $this->goCardlessLoaded = true;
  }

  /**
   * Get a tenant's GoCardless access token
   * 
   * @return string token
   */
  public function getGoCardlessAccessToken()
  {
    // if (!$this->goCardlessLoaded) {
    //   $this->loadGoCardless();
    // }
    // if ($this->goCardlessAccessToken) {
    //   return $this->goCardlessAccessToken;
    // }
    return $this->getKey('GOCARDLESS_ACCESS_TOKEN');
  }

  /**
   * Assign GoCardless details to a tenant
   * 
   * @param string the GC Org ID
   * @param string the GC Access Token
   */
  public function setGoCardlessAccessToken(string $orgId, string $accessToken)
  {
    $count = app()->db->prepare("SELECT COUNT(*) FROM gcCredentials WHERE OrganisationId = ? OR Tenant = ?");
    $count->execute([
      $orgId,
      $this->id
    ]);

    if ($count->fetchColumn() > 0) {
      throw new Exception('Exists already');
    }

    $insert = app()->db->prepare("INSERT INTO gcCredentials (OrganisationId, AccessToken, Tenant) VALUES (?, ?, ?)");
    $insert->execute([
      $orgId,
      $accessToken,
      $this->id
    ]);

    $this->goCardlessAccessToken = $accessToken;
    $this->goCardlessOrganisationId = $orgId;
  }

  /**
   * Get a tenant's GoCardless Org ID
   * 
   * @return string org id
   */
  public function getGoCardlessOrgId()
  {
    if (!$this->goCardlessLoaded) {
      $this->loadGoCardless();
    }
    if ($this->goCardlessOrganisationId) {
      return $this->goCardlessOrganisationId;
    }
    return null;
  }

  /**
   * Get a 
   * 
   * @return string stripe account id
   */
  public function getStripeAccount()
  {
    if ($this->stripeAccountId) {
      return $this->stripeAccountId;
    }

    $sid = $this->getKey('STRIPE_ACCOUNT_ID');

    if ($sid) {
      return $sid;
    }

    return null;
  }

  /**
   * Test if this club is Chester-le-Street ASC (special legacy code reasons)
   * 
   * @return boolean true if clse
   */
  public function isCLS()
  {
    return $this->code == 'CLSE';
  }

  /**
   * Get the path to the tenant's file store
   * String incloudes a trailing slash
   * 
   * @return string path
   * @throws Exception if not file store available
   */
  public function getFilePath()
  {
    if (!getenv('FILE_STORE_PATH')) {
      throw new Exception('No file store available');
    }

    return getenv('FILE_STORE_PATH') . $this->getId() . '/';
  }

  public function getS3FilePath()
  {
    if (!getenv('AWS_S3_REGION') || !getenv('AWS_S3_BUCKET')) {
      throw new Exception('No AWS S3 bucket available');
    }

    return '/' . $this->getId() . '/';
  }

  public function getStripeCustomer()
  {
    if (!getenv('STRIPE')) {
      return null;
    }

    \Stripe\Stripe::setApiKey(getenv('STRIPE'));

    $db = app()->db;
    $checkIfCustomer = $db->prepare("SELECT COUNT(*) FROM tenantStripeCustomers WHERE Tenant = ?");
    $checkIfCustomer->execute([$this->id]);

    $customer = null;
    if ($checkIfCustomer->fetchColumn() == 0) {
      // Create a Customer:
      $customer = \Stripe\Customer::create([
        "name" => $this->getName(),
        "description" => "Customer for Tenant " . $this->getCodeId(),
        'email' => $this->getEmail(),
        "invoice_settings" => [
          "custom_fields" => [
            [
              "name" => "Tenant ID",
              "value" => $this->getId(),
            ],
            [
              "name" => "Swim England Club Code",
              "value" => $this->getKey('ASA_CLUB_CODE'),
            ]
          ]
        ]
      ]);

      // YOUR CODE: Save the customer ID and other info in a database for later.
      $id = $customer->id;
      $addCustomer = $db->prepare("INSERT INTO tenantStripeCustomers (Tenant, CustomerID) VALUES (?, ?)");
      $addCustomer->execute([
        $this->id,
        $id
      ]);
    } else {
      $getCustID = $db->prepare("SELECT CustomerID FROM tenantStripeCustomers WHERE Tenant = ?");
      $getCustID->execute([$this->id]);
      $customer = \Stripe\Customer::retrieve(
        $getCustID->fetchColumn(),
      );

      // Check whether we should update user details
      if (false && ($customer->name != $this->getName() || $customer->email != $this->getEmail())) {
        // Some details are not the same so let's update the stripe customer
        $customer = \Stripe\Customer::update(
          $customer->id,
          [
            "name" => $this->getName(),
            'email' => $this->getEmail()
          ]
        );
      }
    }

    $customer = \Stripe\Customer::update(
      $customer->id,
      [
        "invoice_settings" => [
          "custom_fields" => [
            [
              "name" => "Tenant ID",
              "value" => $this->getId(),
            ],
            [
              "name" => "Swim England Club Code",
              "value" => $this->getKey('ASA_CLUB_CODE'),
            ]
          ]
        ]
      ]
    );

    return $customer;
  }

  public function getSwimEnglandComplianceValue($key)
  {
    $db = app()->db;
    $getKeys = $db->prepare("SELECT `Value` FROM `swimEnglandCompliance` WHERE `Key` = ? AND `Tenant` = ?");
    $getKeys->execute([
      $key,
      $this->id
    ]);
    return $getKeys->fetchColumn();
  }

  public function setSwimEnglandComplianceValue($key, $value)
  {
    $db = app()->db;

    if ($value == null) {
      $delete = $db->prepare("DELETE FROM `swimEnglandCompliance` WHERE `Key` = ? AND `Tenant` = ?;");
      $delete->execute([
        $key,
        $this->id
      ]);

      return;
    }

    $exists = $db->prepare("SELECT COUNT(*) FROM `swimEnglandCompliance` WHERE `Key` = ? AND `Tenant` = ?;");
    $exists->execute([
      $key,
      $this->id
    ]);

    if ($exists->fetchColumn() > 0) {
      // Update
      $update = $db->prepare("UPDATE `swimEnglandCompliance` SET `Value` = ? WHERE `Key` = ? AND `Tenant` = ?;");
      $update->execute([
        $value,
        $key,
        $this->id
      ]);
    } else {
      // Add
      $insert = $db->prepare("INSERT INTO `swimEnglandCompliance` (`Key`, `Value`, `Tenant`) VALUES (?, ?, ?);");
      $insert->execute([
        $key,
        $value,
        $this->id
      ]);
    }
  }
}
