<?php

if ($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['AccessLevel'] == 'Admin') {
  $this->get('/', function() {
    require 'upload.php';
  });

  $this->post('/', function() {
    require 'upload-post.php';
  });
}