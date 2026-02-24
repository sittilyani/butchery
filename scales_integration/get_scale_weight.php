<?php
header('Content-Type: application/json');

// Read from scale (implementation depends on your scale)
$weight = 0;

// Example: Read from serial port
$serial = fopen('/dev/ttyUSB0', 'r');
if ($serial) {
    $data = fread($serial, 100);
    // Parse weight from scale output
    if (preg_match('/(\d+\.?\d*)/', $data, $matches)) {
        $weight = floatval($matches[1]);
    }
    fclose($serial);
}

echo json_encode(['weight' => $weight]);
?>