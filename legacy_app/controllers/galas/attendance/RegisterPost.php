<?php

\SCDS\Can::view('TeamManager', $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], $id);