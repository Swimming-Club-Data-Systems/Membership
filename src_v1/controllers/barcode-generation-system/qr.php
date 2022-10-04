<?php

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

try {
	$text = "0";
	if (isset($_GET["text"])) {
		$text = $_GET["text"];
	} 

	$size = "300";
	if (isset($_GET["size"])) {
		$size = $_GET["size"];
	}

	$margin = 10;
	if (isset($_GET["text"])) {
		$margin = $_GET["text"];
	}

	$ec = false;
	if (isset($_GET["error"]) && $_GET["error"] == "true") {
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
	header('Content-Type: '.$qrCode->getContentType());
	echo $qrCode->writeString();

} catch (Exception $e) {
	halt(500);
}