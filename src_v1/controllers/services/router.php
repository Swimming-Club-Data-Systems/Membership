<?php

$this->get(['/request-a-trial', '/request-a-trial/{headless}'], function($headless = false) {
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Success']) && $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Success'] === true) {
    include 'RequestTrialSuccess.php';
  } else {
    include 'RequestTrialForm.php';
  }
});

$this->post(['/request-a-trial', '/request-a-trial/{headless}'], function($headless = false) {
  include 'RequestTrialPost.php';
});

$this->get('/request-a-trial/{hash}/status', function($hash) {
  $url_path = 'services/request-a-trial/';
  include 'RequestTrialStatus.php';
});

$this->get('/request-a-trial/{hash}/cancel/{trial}', function($hash, $trial) {
  $url_path = 'services/request-a-trial/';
  include 'DeleteTrialRequest.php';
});
