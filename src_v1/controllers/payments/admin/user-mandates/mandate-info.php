<?php

// GET MANDATE INFORMATION

require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$db = app()->db;
$tenant = app()->tenant;

$getMandate = $db->prepare("SELECT COUNT(*) FROM paymentMandates INNER JOIN users ON paymentMandates.UserID = users.UserID WHERE Mandate = ? AND users.Tenant = ?");
$getMandate->execute([
  $mandate,
  $tenant->getId()
]);
if ($getMandate->fetchColumn() == 0) {
  halt(404);
}

header("content-type: application/json");

$data = [];

try {
  $mandate = $client->mandates()->get($mandate);

  $creditor = $customer = $customerBankAccount = $newMandate = null;
  if (isset($mandate->links->creditor)) {
    $creditor = $mandate->links->creditor;
  }
  if (isset($mandate->links->customer)) {
    $customer = $mandate->links->customer;
  }
  if (isset($mandate->links->customer_bank_account)) {
    $customerBankAccount = $mandate->links->customer_bank_account;
  }
  if (isset($mandate->links->new_mandate)) {
    $newMandate = $mandate->links->new_mandate;
  }

  $data = [
    'status' => 200,
    'message' => null,
    'mandate' => [
      'id' => $mandate->id,
      'created_at' => $mandate->created_at,
      'metadata' => $mandate->metadata,
      'next_possible_charge_date' => $mandate->next_possible_charge_date,
      'payments_require_approval' => $mandate->payments_require_approval,
      'reference' => $mandate->reference,
      'scheme' => $mandate->scheme,
      'status' => $mandate->status,
      'links' => [
        'creditor' => $creditor,
        'customer' => $customer,
        'customer_bank_account' => $customerBankAccount,
        'new_mandate' => $newMandate,
      ],
    ]
  ];
} catch (\GoCardlessPro\Core\Exception\ApiException $e) {
  // Api request failed / record couldn't be created.
  $code = $e->getCode();
  http_response_code($code);
  $data = [
    'status' => $code,
    'message' => $e->getMessage(),
    'mandate' => null
  ];
} catch (\GoCardlessPro\Core\Exception\MalformedResponseException $e) {
  // Unexpected non-JSON response
  $code = $e->getCode();
  http_response_code($code);
  $data = [
    'status' => $code,
    'message' => $e->getMessage(),
    'mandate' => null
  ];
} catch (\GoCardlessPro\Core\Exception\ApiConnectionException $e) {
  // Network error
  $code = $e->getCode();
  http_response_code($code);
  $data = [
    'status' => $code,
    'message' => $e->getMessage(),
    'mandate' => null
  ];
} catch (Exception $e) {
  http_response_code(500);
  $data = [
    'status' => 500,
    'message' => $e->getMessage(),
    'mandate' => null
  ];
}

echo json_encode($data, JSON_PRETTY_PRINT);