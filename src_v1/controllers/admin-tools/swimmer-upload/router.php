<?php

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') {
  $this->get('/', function() {
    require 'upload.php';
  });

  $this->post('/', function() {
    require 'upload-post.php';
  });
}