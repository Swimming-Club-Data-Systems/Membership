<?php

unset($_SESSION['SCDS-Payments-Admin']);

http_response_code(302);
header("location: " . autoUrl(app()->adminCurrentTenant->getCodeId() . '/admin'));