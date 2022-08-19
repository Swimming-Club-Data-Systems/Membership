<?php

$this->get('/', function() {
  include 'home.php';
});

// $this->group('/direct-debit', function() {
//   include 'gocardless/router.php';
// });

$this->group('/stripe', function() {
  include 'stripe/router.php';
});

$this->group('/variables', function() {
    $this->get('/', function() {
      include 'variables.php';
    });

    $this->post('/', function() {
      include 'variables-post.php';
    });
});

$this->group('/logo', function() {
  $this->get('/', function() {
    include 'logo.php';
  });

  $this->post('/', function() {
    include 'logo-post.php';
  });
});

// $this->group('/leavers-squad', function() {
//   $this->get('/', function() {
//     include 'leavers-squad.php';
//   });

//   $this->post('/', function() {
//     include 'leavers-squad-post.php';
//   });
// });

$this->group('/terms-and-conditions', function() {
  $this->get('/', function() {
    include 'terms.php';
  });

  $this->post('/', function() {
    include 'terms-post.php';
  });
});

$this->group('/notify-categories', function() {
  $this->get('/', function() {
    include 'notify-categories.php';
  });

  $this->post('/list', function() {
    include 'notify-categories-list.php';
  });

  $this->post('/new', function() {
    include 'notify-categories-new.php';
  });

  $this->post('/delete', function() {
    include 'notify-categories-delete.php';
  });

  $this->post('/update', function() {
    include 'notify-categories-update.php';
  });
});

$this->group('/codes-of-conduct', function() {
  $this->get('/', function() {
    header("Location: " . autoUrl("settings/codes-of-conduct/parent"));
  });

  $this->group('/parent', function() {
    $this->get('/', function() {
      include 'ParentCodeOfConduct.php';
    });

    $this->post('/', function() {
      include 'ParentCodeOfConductPost.php';
    });
  });
});

$this->group('/fees', function() {
  $this->get('/', function() {
    include 'fees/home.php';
  });

  $this->group('/membership-fee-payment-methods', function() {
    $this->get('/', function() {
      include 'fees/membership-payment-methods.php';
    });

    $this->post('/', function() {
      include 'fees/membership-payment-methods-post.php';
    });
  });

  $this->group('/membership-fees', function() {
    include 'fees/club-membership/router.php';
  });

  $this->group('/multiple-squads', function() {
    $this->get('/', function() {
      include 'fees/multiple-squads.php';
    });

    $this->post('/', function() {
      include 'fees/multiple-squads-post.php';
    });
  });

  $this->group('/charge-months', function() {
    $this->get('/', function() {
      include 'fees/charge-months.php';
    });

    $this->post('/', function() {
      include 'fees/charge-months-post.php';
    });
  });

  $this->group('/membership-discounts', function() {
    $this->get('/', function() {
      include 'fees/fee-discounts.php';
    });

    $this->post('/', function() {
      include 'fees/fee-discounts-post.php';
    });
  });
});

$this->get('/welcome-pack', function() {
  include 'WelcomePackSettings.php';
});

$this->get('/welcome-pack/preview', function() {
  include BASE_PATH . 'controllers/registration/welcome-pack/PDF.php';
});

$this->post('/welcome-pack', function() {
  include 'WelcomePackSettings.php';
});