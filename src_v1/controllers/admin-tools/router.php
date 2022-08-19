<?php

/**
 * Admin tools routes
 */

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') {
  $this->get('/', function () {
    include 'home.php';
  });

  $this->group('/reports', function () {
    $this->get('/', function () {
      include 'reports/home.php';
    });

    $this->get('/membership-data-export.csv', function () {
      include 'reports/membership-data.php';
    });

    $this->get('/photography-permissions-export.csv', function () {
      include 'reports/photo-permissions.php';
    });

    $this->get('/no-email-subscription', function () {
      include 'reports/no-email-subscription.php';
    });

    $this->get('/adult-members', function () {
      include 'reports/adult-members-form.php';
    });

    $this->get('/adult-members-list', function () {
      include 'reports/adult-members-list.php';
    });

    $this->get('/pending-payments-data-export.csv', function () {
      include BASE_PATH . 'controllers/payments/admin/reports/pending-payments.csv.php';
    });
  });

  $this->group('/editors', function () {
    $this->get('/member-se-categories', function () {
      include 'editors/membership-cat/editor.php';
    });

    $this->get('/', function () {
      include 'editors/home.php';
    });

    $this->group('/squad-membership', function () {
      $this->get('/', function () {
        include 'editors/member-squad-editor/editor.php';
      });

      $this->post('/add-remove', function () {
        include 'editors/member-squad-editor/add-remove.php';
      });
    });
  });

  $this->group('/galas', function () {
    $this->get('/', function () {
      include 'galas/home.php';
    });

    $this->group('/sdif', function () {
      // $this->get('/', function () {
      //   include 'galas/sdif/home.php';
      // });
  
      $this->get('/upload', function () {
        include 'galas/sdif/upload.php';
      });

      $this->post('/upload', function () {
        include 'galas/sdif/uploadPost.php';
      });
    });
  });

  $this->group('/member-upload', function () {
    include 'swimmer-upload/router.php';
  });

  $this->group('/scds-payments', function () {
    include 'scds-payments/router.php';
  });

  $this->group('/billing', function () {
    include 'billing/router.php';
  });

  $this->group('/audit', function () {
    include 'audit/router.php';
  });

  $this->group('/member-lookup', function () {
    include 'member-lookup/router.php';
  });

  $this->group('/tier-3', function () {
    include 'tier-3/router.php';
  });

  $this->group('/swim-england-compliance', function () {
    $this->get('/', function () {
      include 'swim-england-compliance/home.php';
    });

    $this->post('/', function () {
      include 'swim-england-compliance/post.php';
    });
  });
}