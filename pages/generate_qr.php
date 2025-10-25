<?php
// Step 1: Include QR code library
require_once '../phpqrcode/qrlib.php'; // Make sure qrlib.php is inside phpqrcode folder

// Step 2: Set content type to PNG so browser knows it's an image
header('Content-Type: image/png');

// Step 3: Define the content to encode in QR
$visitorPassId = "VP12345"; // You can make this dynamic using $_GET or database
$qrContent = "Visitor Pass ID: " . $visitorPassId;

// Step 4: Generate and output QR code directly to browser
QRcode::png($qrContent);
?>
