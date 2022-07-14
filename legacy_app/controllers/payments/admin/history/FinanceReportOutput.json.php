<?php

require 'FinanceReport.json.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_PRETTY_PRINT);