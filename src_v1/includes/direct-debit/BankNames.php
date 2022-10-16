<?php

/**
 * Gets a bank's logo if exists
 *
 * @param string $bank name of bank
 * @return string url of bank logo if exists
 */
function getBankName($bank) {

  $bank = mb_strtoupper($bank);

  if ($bank == "TSB BANK PLC") {
    return 'TSB';
  } else if ($bank == "STARLING BANK LIMITED") {
    return 'Starling Bank';
  } else if ($bank == "LLOYDS BANK PLC") {
    return 'Lloyds Bank';
  } else if ($bank == "HALIFAX (A TRADING NAME OF BANK OF SCOTLAND PLC)") {
    return 'Halifax';
  } else if ($bank == "SANTANDER UK PLC") {
    return 'Santander';
  } else if ($bank == "BARCLAYS BANK UK PLC") {
    return 'Barclays';
  } else if ($bank == "NATIONAL WESTMINSTER BANK PLC") {
    return 'NatWest';
  } else if ($bank == "HSBC BANK  PLC (RFB)" || $bank == "HSBC UK BANK PLC") {
    return 'HSBC';
  } else if ($bank == "THE CO-OPERATIVE BANK PLC") {
    return 'The Co-operative Bank';
  } else if ($bank == "NATIONWIDE BUILDING SOCIETY") {
    return 'Nationwide';
  } else if ($bank == "THE ROYAL BANK OF SCOTLAND PLC") {
    return 'Royal Bank of Scotland';
  } else if ($bank == "VIRGIN MONEY PLC") {
    return 'Virgin Money';
  } else if ($bank == "YORKSHIRE BANK (A TRADING NAME OF CLYDESDALE BANK PLC)" || $bank == "CLYDESDALE BANK PLC") {
    return 'Virgin Money (CYBG)';
  } else if ($bank == "MONZO BANK LIMITED") {
    return 'Monzo';
  } else if ($bank == "AIB GROUP (UK) PLC (TRADING NAME FIRST TRUST BANK)") {
    return 'AIB';
  } else if ($bank == "HANDELSBANKEN PLC") {
    return 'Handelsbanken';
  } else if ($bank == "BANK OF SCOTLAND PLC") {
    return 'Bank of Scotland';
  } else if ($bank == "RBS ONE ACCOUNT") {
    return 'The One account';
  } else if ($bank == "BANK OF AMERICA, NA") {
    return 'Bank of America';
  } else if ($bank == "THE ROYAL BANK OF SCOTLAND INTERNATIONAL LTD") {
    return 'RBS International';
  } else if ($bank == "MODULR FS LTD") {
    return 'Modulr';
  } else if ($bank == "TRANSFERWISE LIMITED") {
    return 'TransferWise';
  }

  return $bank;

}