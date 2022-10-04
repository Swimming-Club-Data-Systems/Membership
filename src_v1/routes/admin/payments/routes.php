<?php

/**
 * Code for payment routes
 */

$this->get('/', function () {
  include BASE_PATH . 'controllers/global-admin/payments/home.php';
});


$this->group('/products', function () {
  $this->get('/', function () {
    include BASE_PATH . 'controllers/global-admin/payments/products/home.php';
  });

  $this->group('/new', function () {
    $this->get('/', function () {
      include BASE_PATH . 'controllers/global-admin/payments/products/new.php';
    });

    $this->post('/', function () {
      include BASE_PATH . 'controllers/global-admin/payments/products/new-post.php';
    });
  });

  $this->group('/{id}:uuid', function ($id) {
    $this->get('/', function ($id) {
      include BASE_PATH . 'controllers/global-admin/payments/products/view.php';
    });

    $this->post('/', function ($id) {
      include BASE_PATH . 'controllers/global-admin/payments/products/view-post.php';
    });
  });
});

$this->group('/plans', function () {
  $this->group('/new', function () {
    $this->post('/', function () {
      include BASE_PATH . 'controllers/global-admin/payments/plans/new-post.php';
    });
  });
});

$this->group('/tax-rates', function () {
  $this->get('/', function () {
    include BASE_PATH . 'controllers/global-admin/payments/tax-rates/home.php';
  });

  $this->group('/new', function () {
    $this->get('/', function () {
      include BASE_PATH . 'controllers/global-admin/payments/tax-rates/new.php';
    });

    $this->post('/', function () {
      include BASE_PATH . 'controllers/global-admin/payments/tax-rates/new-post.php';
    });
  });

  $this->group('/{id}:uuid', function ($id) {
    $this->get('/', function ($id) {
      include BASE_PATH . 'controllers/global-admin/payments/tax-rates/view.php';
    });

    $this->post('/', function ($id) {
      include BASE_PATH . 'controllers/global-admin/payments/tax-rates/view-post.php';
    });
  });
});

$this->group('/subscriptions', function () {
  $this->get('/', function () {
    include BASE_PATH . 'controllers/global-admin/payments/subscriptions/home.php';
  });

  $this->group('/new', function () {
    $this->get('/', function () {
      include BASE_PATH . 'controllers/global-admin/payments/subscriptions/new.php';
    });

    $this->post('/', function () {
      include BASE_PATH . 'controllers/global-admin/payments/subscriptions/new-post.php';
    });

    $this->post('/get-tenant-payment-methods', function() {
      include BASE_PATH . 'controllers/global-admin/payments/subscriptions/get-tenant-payment-methods.php';
    });

    $this->post('/get-product-plans', function() {
      include BASE_PATH . 'controllers/global-admin/payments/subscriptions/get-product-plans.php';
    });
  });

  $this->group('/{id}:uuid', function ($id) {
    $this->get('/', function ($id) {
      include BASE_PATH . 'controllers/global-admin/payments/subscriptions/view.php';
    });

    $this->post('/', function ($id) {
      include BASE_PATH . 'controllers/global-admin/payments/subscriptions/edit-post.php';
    });
  });
});

$this->group('/invoices', function () {
  $this->get('/', function () {
    include BASE_PATH . 'controllers/global-admin/payments/invoices/home.php';
  });

  $this->group('/new', function () {
    $this->get('/', function () {
      include BASE_PATH . 'controllers/global-admin/payments/invoices/new.php';
    });

    $this->post('/', function () {
      include BASE_PATH . 'controllers/global-admin/payments/invoices/new-post.php';
    });

    $this->post('/get-tenant-payment-methods', function() {
      include BASE_PATH . 'controllers/global-admin/payments/subscriptions/get-tenant-payment-methods.php';
    });

    $this->post('/get-product-plans', function() {
      include BASE_PATH . 'controllers/global-admin/payments/subscriptions/get-product-plans.php';
    });
  });

  $this->group('/{id}:uuid', function ($id) {
    $this->get('/', function ($id) {
      include BASE_PATH . 'controllers/global-admin/payments/invoices/view.php';
    });

    $this->post('/', function ($id) {
      include BASE_PATH . 'controllers/global-admin/payments/invoices/edit-post.php';
    });
  });
});