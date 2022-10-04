<?php

$this->get('/{id}:uuid', function($id) {
  include 'view.php';
});