<?php

$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['StripeDDError'] = true;
if (isset($renewal_trap) && $renewal_trap) {
  header("location: " . autoUrl("renewal/payments/direct-debit/set-up"));
} else {
  header("location: " . autoUrl("payments/direct-debit/set-up"));
}