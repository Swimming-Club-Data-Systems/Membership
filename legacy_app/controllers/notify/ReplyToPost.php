<?php

use Respect\Validation\Validator as v;

if (v::email()->validate($_POST['reply'])) {
  setUserOption($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], 'NotifyReplyAddress', $_POST['reply']);
  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['SetReplySuccess'] = true;
} else {
  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['SetReplyFalse'] = true;
}

header("Location: " . autoUrl("notify/reply-to"));