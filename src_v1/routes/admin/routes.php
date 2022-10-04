<?php

if (!isset($_SESSION['SCDS-SuperUser'])) {
  $this->group('/login', function () {
    $this->get('/', function () {
      require BASE_PATH . 'controllers/global-admin/login/oauth.php';
    });

    $this->get('/oauth', function () {
      require BASE_PATH . 'controllers/global-admin/login/oauth.php';
    });

    // $this->post('/', function () {
    //   require BASE_PATH . 'controllers/global-admin/login/post.php';
    // });

    // $this->post('/2fa', function () {
    //   require BASE_PATH . 'controllers/global-admin/login/post-2fa.php';
    // });

    $this->group('/reset-password', function () {
      $this->get('/', function () {
        require BASE_PATH . 'controllers/global-admin/login/reset-password/view.php';
      });

      // $this->post('/', function () {
      //   require BASE_PATH . 'controllers/global-admin/login/reset-password/post.php';
      // });
    });
  });

  $this->any(['/', '/*'], function () {
    http_response_code(303);
    header("Location: " . autoUrl('admin/login?target=' . urlencode(app('request')->path)));
  });
} else {
  $this->group('/login', function () {
    $this->any(['/', '/*'], function () {
      http_response_code(303);
      header("Location: " . autoUrl('admin'));
    });
  });
}

$this->get('/', function () {
  require BASE_PATH . 'controllers/global-admin/dashboard.php';
});

$this->group('/register', function () {
  include 'register/routes.php';
});

$this->group('/notify', function () {
  include 'notify/routes.php';
});

$this->group('/audit', function () {
  include 'audit/routes.php';
});

$this->group('/users', function () {
  include 'users/routes.php';
});

$this->group('/payments', function () {
  include 'payments/routes.php';
});

$this->group('/tenants', function () {
  include 'tenants/routes.php';
});

$this->get('/files/{tenant}:int/*', function () {
  $array = $this->getArrayCopy();
  $tenant = (int) $array['tenant'];
  $filename = $array[0];
  require BASE_PATH . 'controllers/FileLoaderSudo.php';
});
