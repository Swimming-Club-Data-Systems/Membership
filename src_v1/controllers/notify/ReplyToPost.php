<?php

use Respect\Validation\Validator as v;

if (v::email()->validate($_POST['reply'])) {
  setUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 'NotifyReplyAddress', $_POST['reply']);
  $_SESSION['TENANT-' . app()->tenant->getId()]['SetReplySuccess'] = true;
} else {
  $_SESSION['TENANT-' . app()->tenant->getId()]['SetReplyFalse'] = true;
}

header("Location: " . autoUrl("notify/reply-to"));