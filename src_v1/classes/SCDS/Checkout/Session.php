<?php

namespace SCDS\Checkout;

use DateTimeZone;
use DateTime;
use Exception;

/**
 * Checkout Session Class
 * 
 * @author Chris Heppell
 */
class Session
{
  public $id;
  public $user;
  public $amount;
  public $currency;
  public $state;
  public $allowedTypes;
  public $created;
  public $succeeded;
  public $intent;
  public $method;
  public $version;
  public $creator;
  public $taxId;
  public $totalDetails;
  public $metadata;
  public $tenant;
  private $items;

  private function __construct()
  {
  }

  public static function new($data, $tenant = null)
  {
    $db = app()->db;
    if (!$tenant) $tenant = app()->tenant->getId();

    // Validate user at this tenant
    if ($data['user']) {
      $userExists = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `UserID` = ? AND `Tenant` = ?");
      $userExists->execute([
        $data['user'],
        $tenant,
      ]);

      if ($userExists->fetchColumn() == 0) throw new Exception('No user at tenant');
    }

    $id = \Ramsey\Uuid\Uuid::uuid4();

    // Parse data and insert into db
    $add = $db->prepare("INSERT INTO `checkoutSessions` (`id`, `user`, `amount`, `currency`, `state`, `allowed_types`, `created`, `version`, `total_details`, `metadata`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $add->execute([
      $id,
      $data['user'] ?? null,
      $data['amount'] ?? 0,
      $data['currency'] ?? 'gbp',
      $data['state'] ?? 'open',
      json_encode($data['allowed_types'] ?? ['card']),
      $data['created'] ?? (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
      'v1',
      json_encode($data['total_details'] ?? []),
      json_encode($data['metadata'] ?? []),
      $tenant,
    ]);

    return Session::retrieve($id);
  }

  public static function retrieve($id, $tenant = null)
  {
    $db = app()->db;
    if (!$tenant) $tenant = app()->tenant->getId();

    $get = $db->prepare("SELECT * FROM `checkoutSessions` WHERE `id` = ? AND `Tenant` = ?");
    $get->execute([
      $id,
      $tenant,
    ]);

    $sessionInfo = $get->fetch(\PDO::FETCH_OBJ);

    if (!$sessionInfo) throw new Exception('No checkout session');

    $session = new Session();

    $session->id = $id;
    $session->user = $sessionInfo->user;
    $session->amount = $sessionInfo->amount;
    $session->currency = $sessionInfo->currency;
    $session->state = $sessionInfo->state;
    $session->allowedTypes = json_decode($sessionInfo->allowed_types);
    $session->created = new \DateTime($sessionInfo->created, new \DateTimeZone(('UTC')));
    if ($sessionInfo->succeeded) {
      $session->succeeded = new \DateTime($sessionInfo->succeeded, new \DateTimeZone(('UTC')));
    } else {
      $session->succeeded = null;
    }
    $session->intent = $sessionInfo->intent;
    $session->method = $sessionInfo->method;
    $session->version = $sessionInfo->version;
    $session->taxId = $sessionInfo->tax_id;
    $session->totalDetails = json_decode($sessionInfo->total_details);
    $session->metadata = json_decode($sessionInfo->metadata);
    $session->tenant = $sessionInfo->Tenant;

    return $session;
  }

  public function addItem($data)
  {
    $id = \Ramsey\Uuid\Uuid::uuid4();

    $db = app()->db;

    $add = $db->prepare("INSERT INTO checkoutItems (`id`, `checkout_session`, `name`, `description`, `amount`, `currency`, `tax_amount`, `tax_data`, `sub_items`, `type`, `attributes`, `metadata`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $add->execute([
      $id,
      $this->id,
      $data['name'] ?? 'Item',
      $data['description'] ?? null,
      $data['amount'] ?? 0,
      $data['currency'] ?? 'gbp',
      $data['tax_amount'] ?? 0,
      json_encode($data['tax_data'] ?? []),
      json_encode($data['sub_items'] ?? []),
      $data['type'] ?? 'debit',
      json_encode($data['attributes'] ?? []),
      json_encode($data['metadata'] ?? []),
    ]);
  }

  public function removeItem($itemId)
  {
    $db = app()->db;

    $remove = $db->prepare("DELETE FROM checkoutItems WHERE `id` = ? AND `checkout_session` = ?");
    $remove->execute([
      $itemId,
      $this->id,
    ]);
  }

  public function getItems()
  {
    $this->loadItems();
    return $this->items;
  }

  public function autoCalculateTotal()
  {
    $items = $this->getItems();

    $total = 0;

    foreach ($items as $item) {
      if ($item->type == 'debit') {
        $total += $item->amount;
      } else {
        $total -= $item->amount;
      }
    }

    if ($total < 0) {
      throw new Exception('Invalid amount');
    }

    $this->amount = $total;
  }

  public function save()
  {
    $db = app()->db;

    $save = $db->prepare("UPDATE checkoutSessions SET `amount` = ?, `currency` = ?, `state` = ?, `allowed_types` = ?, `created` = ?, succeeded = ?, intent = ?, method = ?, `version` = ?, creator = ?, tax_id = ?, `total_details` = ?, `metadata` = ? WHERE `id` = ?");
    $save->execute([
      $this->amount,
      $this->currency,
      $this->state,
      json_encode($this->allowedTypes),
      $this->created->format('Y-m-d H:i:s'),
      $this->succeeded,
      $this->intent,
      $this->method,
      $this->version,
      $this->creator,
      $this->taxId,
      json_encode($this->totalDetails),
      json_encode($this->metadata),
      $this->id,
    ]);

    if ($this->intent) {
      \Stripe\Stripe::setApiKey(getenv('STRIPE'));

      $db = app()->db;

      $tenant = \Tenant::fromId($this->tenant);

      $customer = (new \User($this->user))->getStripeCustomerID();

      $intent = \Stripe\PaymentIntent::update(
        $this->intent,
        [
          'amount' => $this->amount,
          'customer' => $customer,
          'currency' => $this->currency,
          'payment_method_types' => (array) $this->allowedTypes,
          'metadata' => [
            'payment_category' => 'checkout_v1',
            'checkout_id' => $this->id,
          ]
        ],
        [
          'stripe_account' => $tenant->getStripeAccount()
        ]
      );
    }
  }

  public function getUrl()
  {
    return autoUrl('payments/checkout/' . $this->version . '/' . $this->id);
  }

  public function createPaymentIntent()
  {
    $db = app()->db;
    \Stripe\Stripe::setApiKey(getenv('STRIPE'));

    $tenant = \Tenant::fromId($this->tenant);

    $customer = (new \User($this->user))->getStripeCustomerID();

    $intent = \Stripe\PaymentIntent::create([
      'amount' => $this->amount,
      'customer' => $customer,
      'currency' => $this->currency,
      'payment_method_types' => (array) $this->allowedTypes,
      'confirm' => false,
      'setup_future_usage' => 'off_session',
      'metadata' => [
        'payment_category' => 'checkout_v1',
        'checkout_id' => $this->id,
      ]
    ], [
      'stripe_account' => $tenant->getStripeAccount()
    ]);

    $this->intent = $intent->id;

    $this->save();

    return $intent;
  }

  public function getPaymentIntent()
  {

    if (!$this->intent) {
      $intent = $this->createPaymentIntent();

      $this->save();

      return $intent;
    }

    \Stripe\Stripe::setApiKey(getenv('STRIPE'));

    $tenant = \Tenant::fromId($this->tenant);

    $intent = \Stripe\PaymentIntent::retrieve(
      [
        'id' => $this->intent,
        'expand' => ['customer', 'payment_method', 'charges.data.balance_transaction'],
      ],
      [
        'stripe_account' => $tenant->getStripeAccount()
      ]
    );

    return $intent;
  }

  private function loadItems()
  {
    $db = app()->db;
    $this->items = [];

    // Load the checkout session items
    $getItemIds = $db->prepare("SELECT `id` FROM `checkoutItems` WHERE `checkout_session` = ?");
    $getItemIds->execute([
      $this->id,
    ]);

    while ($item = $getItemIds->fetchColumn()) {
      $this->items[] = Item::retrieve($item);
    }
  }
}
