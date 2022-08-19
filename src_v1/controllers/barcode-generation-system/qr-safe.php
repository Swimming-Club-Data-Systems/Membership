<?php

$qrReturn = null;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

function setNumberIfUnset($test) {
	if ($test) {
		global $number;
		return $number;
	} else {
		return 0;
	}
}

$number = setNumberIfUnset(isset($number));

try {
	$text = "0";
	if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['qr'][$number]["text"])) {
		$text = $_SESSION['TENANT-' . app()->tenant->getId()]['qr'][$number]["text"];
	} 

	$size = "300";
	if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['qr'][$number]["size"])) {
		$size = $_SESSION['TENANT-' . app()->tenant->getId()]['qr'][$number]["size"];
	}
	if (isset($size_url) && $size_url > 0) {
		$size = $size_url;
	}

	$margin = 10;
	if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['qr'][$number]["text"])) {
		$margin = $_SESSION['TENANT-' . app()->tenant->getId()]['qr'][$number]["text"];
	}

	$ec = false;
	if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['qr'][$number]["error"]) && $_SESSION['TENANT-' . app()->tenant->getId()]['qr'][$number]["error"] == "true") {
		$ec = true;
	}

	// Create a basic QR code
	$qrCode = new QrCode($text);
	$qrCode->setSize($size);

	// Set advanced options
	$qrCode->setWriterByName('png');
	$qrCode->setMargin((int) $margin);
	$qrCode->setEncoding('UTF-8');
	if ($ec) {
		$qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));
	}
	$qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
	$qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 127]);

	$qrCode->setRoundBlockSize(false);
	$qrCode->setValidateResult(false);

	$qrCode->setWriterOptions(['exclude_xml_declaration' => false]);

	// Directly output the QR code
	//header('Content-Disposition: attachment; filename="qr.png"');
	if (isset($qrFile) && $qrFile) {
		$qrReturn = $qrCode->writeString();
	} else {
		header('Content-Type: '.$qrCode->getContentType());
		echo $qrCode->writeString();
	}

} catch (Exception $e) {
	halt(500);
}