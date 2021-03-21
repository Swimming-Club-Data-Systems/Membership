<?php

/**
 * Gets a bank's logo if exists
 *
 * @param string $bank name of bank
 * @return string url of bank logo if exists
 */
function getBankLogo($bank) {

  $bank = mb_strtoupper($bank);
  $logo_path = null;

  if ($bank == "TSB BANK PLC") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/tsbbankplc");
  } else if ($bank == "STARLING BANK LIMITED") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/starlingbanklimited");
  } else if ($bank == "LLOYDS BANK PLC") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/lloydsbankplc");
  } else if ($bank == "HALIFAX (A TRADING NAME OF BANK OF SCOTLAND PLC)") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/halifax");
  } else if ($bank == "SANTANDER UK PLC") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/santanderukplc");
  } else if ($bank == "BARCLAYS BANK UK PLC") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/barclaysbankukplc");
  } else if ($bank == "NATIONAL WESTMINSTER BANK PLC") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/nationalwestminsterbankplc");
  } else if ($bank == "HSBC BANK  PLC (RFB)" || $bank == "HSBC UK BANK PLC") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/hsbc");
  } else if ($bank == "THE CO-OPERATIVE BANK PLC") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/coop");
  } else if ($bank == "NATIONWIDE BUILDING SOCIETY") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/nationwide");
  } else if ($bank == "THE ROYAL BANK OF SCOTLAND PLC" || $bank == "THE ROYAL BANK OF SCOTLAND INTERNATIONAL LTD") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/rbs");
  } else if ($bank == "VIRGIN MONEY PLC" || $bank == "YORKSHIRE BANK (A TRADING NAME OF CLYDESDALE BANK PLC)" || $bank == "CLYDESDALE BANK PLC") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/virginmoney");
  } else if ($bank == "MONZO BANK LIMITED") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/monzo");
  } else if ($bank == "AIB GROUP (UK) PLC (TRADING NAME FIRST TRUST BANK)") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/aib");
  } else if ($bank == "BANK OF SCOTLAND PLC") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/bankofscotland");
  } else if ($bank == "RBS ONE ACCOUNT") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/oneaccount");
  } else if ($bank == "HANDELSBANKEN PLC") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/handlesbanken");
  } else if ($bank == "BANK OF AMERICA, NA") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/bankofamerica");
  } else if ($bank == "MODULR FS LTD") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/modulr");
  } else if ($bank == "TRANSFERWISE LIMITED") {
    $logo_path = autoUrl("public/img/directdebit/bank-logos/transferwise");
  }

  return $logo_path;

}