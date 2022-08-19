<?php

$this->get('/', function () {
  require BASE_PATH . 'controllers/global-admin/tenants/home.php';
});

$this->get('/{id}:uuid', function ($id) {
  require BASE_PATH . 'controllers/global-admin/tenants/tenant.php';
});

$this->get('/{id}:uuid/stripe', function ($id) {
  require BASE_PATH . 'controllers/global-admin/tenants/stripe.php';
});

$this->get('/{id}:uuid/stripe/delete-apple-pay-domain', function ($id) {
  require BASE_PATH . 'controllers/global-admin/tenants/stripe-delete-apple-pay-domain.php';
});

$this->post('/{id}:uuid/stripe/add-apple-pay-domain', function ($id) {
  require BASE_PATH . 'controllers/global-admin/tenants/stripe-add-apple-pay-domain.php';
});

$this->post('/{id}:uuid/set-domain', function ($id) {
  require BASE_PATH . 'controllers/global-admin/tenants/set-domain.php';
});