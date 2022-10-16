<?php

\SCDS\Can::view('TeamManager', $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $id);