<?php

/**
 * User Class
 * Cannot store this in the session
 *
 * @copyright Chester-le-Street ASC https://github.com/Chester-le-Street-ASC
 * @author Chris Heppell https://github.com/clheppell
 */
class User extends Person
{
  private $emailAddress;
  private $mobile;
  private $accessLevel;
  private $userOptions;
  private $userOptionsRetrieved;
  private $setSession;
  private $permissions;

  public function __construct($id, $setSession = false)
  {
    $this->id = (int) $id;
    $this->userOptionsRetrieved = false;
    $this->setSession = $setSession;
    $this->revalidate();
  }

  public function revalidate()
  {
    $db = app()->db;
    // Get the user
    $query = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile FROM users WHERE UserID = ?");
    $query->execute([$this->id]);
    $row = $query->fetch(PDO::FETCH_ASSOC);

    $this->permissions = [];
    try {
      // Get access permissions
      $getPermissions = $db->prepare("SELECT `Permission` FROM `permissions` WHERE `User` = ?");
      $getPermissions->execute([
        $this->id
      ]);
      $this->permissions = $getPermissions->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
      // Table does not exist
    }

    if (sizeof($this->permissions) == 0) {
      $this->permissions = ['Parent'];
    }

    if ($row) {
      $defaultAccessLevel = $this->getUserOption('DefaultAccessLevel');

      if (isset(app()->tenant)) {
        if ($defaultAccessLevel && in_array($defaultAccessLevel, $this->permissions)) {
          $this->accessLevel = $defaultAccessLevel;
        } else if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) && in_array($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'], $this->permissions)) {
          $this->accessLevel = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
        } else if (in_array('Admin', $this->permissions) && !$defaultAccessLevel) {
          $this->accessLevel = 'Admin';
        } else {
          $this->accessLevel = $this->permissions[0];
        }
      }


      $this->forename = $row['Forename'];
      $this->surname = $row['Surname'];
      $this->emailAddress = $row['EmailAddress'];
      $this->mobile = $row['Mobile'];

      if ($this->setSession) {
        // Set legacy user details
        $_SESSION['TENANT-' . app()->tenant->getId()]['Forename'] = $this->forename;
        $_SESSION['TENANT-' . app()->tenant->getId()]['Surname'] = $this->surname;
        $_SESSION['TENANT-' . app()->tenant->getId()]['EmailAddress'] = $this->emailAddress;
        $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] = $this->accessLevel;
      }
    } else {
      throw new Exception();
    }
  }

  public function getDirtyFirstName()
  {
    return $this->forename;
  }

  public function getFirstName()
  {
    return $this->forename;
  }

  public function getDirtyLastName()
  {
    return $this->surname;
  }

  public function getLastName()
  {
    return $this->surname;
  }

  public function getDirtyName()
  {
    return $this->forename . ' ' . $this->surname;
  }

  public function getName()
  {
    return $this->forename . ' ' . $this->surname;
  }

  public function getDirtyEmail()
  {
    return $this->emailAddress;
  }

  public function getEmail()
  {
    return $this->getDirtyEmail();
  }

  public function getMobile()
  {
    return $this->mobile;
  }

  private function getUserOptions()
  {
    $db = app()->db;

    try {
      $getOptions = $db->prepare("SELECT `Option`, `Value` FROM userOptions WHERE User = ? LIMIT 100");
      $getOptions->execute([$this->id]);
      $this->userOptions = $getOptions->fetchAll(PDO::FETCH_KEY_PAIR);
      $this->userOptionsRetrieved = true;
    } catch (Exception $e) {
      // Couldn't get options
    }
  }

  public function getUserOption($name)
  {
    if (!$this->userOptionsRetrieved) {
      $this->getUserOptions();
    }
    if (!isset($this->userOptions[$name])) {
      return null;
    }
    // Get the options
    if ($this->userOptions[$name] != null) {
      return $this->userOptions[$name];
    } else {
      return null;
    }
  }

  public function getUserBooleanOption($name)
  {
    return bool($this->getUserOption($name));
  }

  public function setUserOption($option, $value)
  {
    $db = app()->db;

    if ($value == "") {
      $value = null;
    }

    // Update value in memory
    $this->userOptions[$option] = $value;

    // Any PDO exceptions will be propagated
    $query = $db->prepare("SELECT COUNT(*) FROM userOptions WHERE User = ? AND `Option` = ?");
    $query->execute([$this->id, $option]);
    $result = $query->fetchColumn();

    if ($result == 0) {
      $query = $db->prepare("INSERT INTO userOptions (User, `Option`, `Value`) VALUES (?, ?, ?)");
      $query->execute([$this->id, $option, $value]);
    } else {
      $query = $db->prepare("UPDATE userOptions SET `Value` = ? WHERE User = ? AND `Option` = ?");
      $query->execute([$value, $this->id, $option]);
    }
  }

  public function getPermissions()
  {
    return $this->permissions;
  }

  public function getPrintPermissions()
  {
    $permissions = [];
    foreach ($this->permissions as $perm) {
      if ($perm == 'Admin') {
        $permissions[$perm] = 'Admin';
      } else if ($perm == 'Parent') {
        $permissions[$perm] = 'Parent/Member';
      } else if ($perm == 'Coach') {
        $permissions[$perm] = 'Coach';
      } else if ($perm == 'Galas') {
        $permissions[$perm] = 'Gala Staff';
      }
    }
    return $permissions;
  }

  public function hasPermission($permission)
  {
    return in_array($permission, $this->permissions);
  }

  public function hasPermissions(array $permissions = [])
  {
    foreach ($permissions as $type) {
      if ($this->hasPermission($type)) {
        return true;
      }
    }
  }

  public function grantPermission($permission)
  {
    $db = app()->db;
    try {
      $setPerm = $db->prepare("INSERT INTO `permissions` (`Permission`, `User`) VALUES (?, ?)");
      $setPerm->execute([
        $permission,
        $this->id
      ]);
      return true;
    } catch (PDOException $e) {
      return false;
    }
  }

  public function revokePermission($permission)
  {
    $db = app()->db;
    try {
      $deletePerm = $db->prepare("DELETE FROM `permissions` WHERE `Permission` = ? AND `User` = ?");
      $deletePerm->execute([
        $permission,
        $this->id
      ]);
      return true;
    } catch (PDOException $e) {
      return false;
    }
  }

  /**
   * Get the user's emergency contacts
   * 
   * @return EmergencyContact[] an array of emergency contacts
   */
  public function getEmergencyContacts()
  {
    $ec = new EmergencyContacts(app()->db);
    $ec->byParent($this->id);
    return $ec->getContacts();
  }

  public function getStripeCustomerID()
  {
    if (!app()->tenant->getStripeAccount() || !getenv('STRIPE')) {
      return null;
    }

    \Stripe\Stripe::setApiKey(getenv('STRIPE'));

    $db = app()->db;
    $getCustomer = $db->prepare("SELECT `CustomerID` FROM `stripeCustomers` WHERE `User` = ?");
    $getCustomer->execute([$this->id]);

    $customerId = $getCustomer->fetchColumn();

    if (!$customerId) {
      $customerId = $this->getStripeCustomer()->id;
    }

    return $customerId;
  }

  public function getStripeCustomer()
  {
    if (!app()->tenant->getStripeAccount() || !getenv('STRIPE')) {
      return null;
    }

    \Stripe\Stripe::setApiKey(getenv('STRIPE'));

    $db = app()->db;
    $checkIfCustomer = $db->prepare("SELECT COUNT(*) FROM stripeCustomers WHERE User = ?");
    $checkIfCustomer->execute([$this->id]);

    $customer = null;
    if ($checkIfCustomer->fetchColumn() == 0) {
      // Create a Customer:
      $customer = \Stripe\Customer::create([
        "name" => $this->getName(),
        "description" => "Customer for " . $this->id . ' (' . $this->getEmail() . ')',
        'email' => $this->getEmail(),
        'phone' => $this->getMobile()
      ], [
        'stripe_account' => app()->tenant->getStripeAccount()
      ]);

      // YOUR CODE: Save the customer ID and other info in a database for later.
      $id = $customer->id;
      $addCustomer = $db->prepare("INSERT INTO stripeCustomers (User, CustomerID) VALUES (?, ?)");
      $addCustomer->execute([
        $this->id,
        $id
      ]);
    } else {
      $getCustID = $db->prepare("SELECT CustomerID FROM stripeCustomers WHERE User = ?");
      $getCustID->execute([$this->id]);
      $customer = \Stripe\Customer::retrieve(
        $getCustID->fetchColumn(),
        [
          'stripe_account' => app()->tenant->getStripeAccount()
        ]
      );

      // Check whether we should update user details
      if ($customer->name != $this->getName() || $customer->email != $this->getEmail() || $customer->phone != $this->getMobile()) {
        // Some details are not the same so let's update the stripe customer
        $customer = \Stripe\Customer::update(
          $customer->id,
          [
            "name" => $this->getName(),
            'email' => $this->getEmail(),
            'phone' => $this->getMobile()
          ],
          [
            'stripe_account' => app()->tenant->getStripeAccount()
          ]
        );
      }
    }

    return $customer;
  }

  public function getKey($key) {
    return $this->getUserOption($key);
  }

  public function getBooleanKey($key) {
    return $this->getUserBooleanOption($key);
  }

}
