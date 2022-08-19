<?php

$this->get('/', function () {
  if (!isset($_SESSION['OnboardingSessionId'])) {
    include 'init.php';
  } else {
    include 'start.php';
  }
});

$this->get('/error', function () {
  include 'error.php';
});

$this->get('/wrong-account', function () {
  include 'logged-in.php';
});


if ($_SESSION['OnboardingSessionId']) {
  $this->get('/start-task', function () {
    include 'task-handler.php';
  });

  $this->post('/start-task', function () {
    include 'post-task-handler.php';
  });

  $this->group('/emergency-contacts', function () {
    $this->get('/list', function () {
      include 'tasks/emergency_contacts/list.php';
    });

    $this->post('/new', function () {
      include 'tasks/emergency_contacts/new.php';
    });

    $this->post('/edit', function () {
      include 'tasks/emergency_contacts/edit.php';
    });

    $this->post('/delete', function () {
      include 'tasks/emergency_contacts/delete.php';
    });
  });

  $this->group('/direct-debit', function () {
    $this->group('/stripe', function () {
      $this->get('/set-up', function () {
        include 'tasks/direct_debit_mandate/stripe/set-up.php';
      });

      $this->get('/success', function () {
        include 'tasks/direct_debit_mandate/stripe/success.php';
      });
    });

    $this->group('/go-cardless', function () {
      $this->get('/set-up', function () {
        include 'tasks/direct_debit_mandate/go-cardless/set-up.php';
      });

      $this->get('/success', function () {
        include 'tasks/direct_debit_mandate/go-cardless/success.php';
      });
    });
  });

  $this->group('/member-forms', function () {
    $this->group('/{id}:uuid', function ($id) {
      $this->get('/start-task', function ($id) {
        include 'tasks/member_forms/task-handler.php';
      });

      $this->post('/start-task', function ($id) {
        include 'tasks/member_forms/post-task-handler.php';
      });
    });
  });

  $this->group('/fees', function () {
    $this->get('/success', function () {
      include 'tasks/fees/success.php';
    });
  });

  $this->get('/sign-in', function () {
    include 'sign-in.php';
  });
} else {
  $this->any('/*', function () {
    include 'init.php';
  });
}
