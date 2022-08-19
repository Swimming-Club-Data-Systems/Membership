<?php

$this->get(['/', '/*'], function() {
  include 'controllers/web.php';
});
