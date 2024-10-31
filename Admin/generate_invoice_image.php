<?php
include '../config.php';

if (isset($_GET['history_id'])) {
    $history_id = $_GET['history_id'];

    // Fetch rental history details
    $query = "SELECT h.history_id, h.rental_id, u.name AS customer_name, u.email AS customer_email, 
              u.phone AS customer_phone, h.date_from, h.date_to, h.total_fees, h.car_model, 
              h.car_brand, h.seat_count, h.max_speed, h.km_per_liter, h.fine_details, h.additional_options 
              FROM rental_history h
              JOIN users u ON h.customer_id = u.user_id
              WHERE h.history_id = $history_id";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();

    // Calculate rental days
    $daysRented = (strtotime($data['date_to']) - strtotime($data['date_from'])) / 86400 + 1;
    $perDayCost = $data['total_fees'] / $daysRented;

    // Fine details (parse fine details to array if stored as JSON)
    $fines = json_decode($data['fine_details'], true) ?: [];

    // Create a blank image
    $width = 800;
    $height = 1600;
    $image = imagecreatetruecolor($width, $height);
    $background = imagecolorallocate($image, 255, 255, 255);
    imagefilledrectangle($image, 0, 0, $width, $height, $background);

    // Define colors
    $textColor = imagecolorallocate($image, 0, 0, 0);
    $blue = imagecolorallocate($image, 41, 128, 185);
    $borderColor = imagecolorallocate($image, 200, 200, 200);

    // Set up font path
    $fontPath = './arial.ttf';

    // Header
    imagettftext($image, 20, 0, 200, 50, $textColor, $fontPath, 'DriveXpert Rental Company');
    imagettftext($image, 12, 0, 20, 120, $textColor, $fontPath, '1234 Elm Street, Suite 567, City Name');
    imagettftext($image, 12, 0, 20, 145, $textColor, $fontPath, 'Phone: +123 456 7890 | Email: info@drivexpert.com');
    imagettftext($image, 18, 0, 300, 200, $blue, $fontPath, 'Rental Invoice');

    $startY = 250;
    $lineHeight = 40;

    // Draw Customer Information Table
    imagettftext($image, 14, 0, 20, $startY, $blue, $fontPath, 'Customer Information');
    $startY += 30;
    $customerData = [
        ['Customer Name', $data['customer_name']],
        ['Email', $data['customer_email']],
        ['Phone', $data['customer_phone']]
    ];
    drawTable($image, $customerData, $startY, $width, $lineHeight, $borderColor, $textColor, $fontPath);
    $startY += count($customerData) * $lineHeight + 10;

    // Draw Car Details Table
    imagettftext($image, 14, 0, 20, $startY, $blue, $fontPath, 'Car Information');
    $startY += 30;
    $carData = [
        ['Brand', $data['car_brand']],
        ['Model', $data['car_model']],
        ['Seats', $data['seat_count']],
        ['Max Speed', $data['max_speed'] . ' km/h'],
        ['Efficiency', $data['km_per_liter'] . ' km/l']
    ];
    drawTable($image, $carData, $startY, $width, $lineHeight, $borderColor, $textColor, $fontPath);
    $startY += count($carData) * $lineHeight + 10;

    // Draw Pricing Table
    imagettftext($image, 14, 0, 20, $startY, $blue, $fontPath, 'Pricing Details');
    $startY += 30;
    $priceData = [
        ['Per Day Cost', '$' . number_format($perDayCost, 2)],
        ['Rented Cost', '$' . number_format($data['total_fees'], 2)]
    ];

    // Add each fine
    foreach ($fines as $reason => $fine) {
        $priceData[] = ["Fine for $reason", '$' . number_format($fine, 2)];
    }

    // Add total row
    $totalFine = array_sum($fines);
    $totalCost = $data['total_fees'] + $totalFine;
    $priceData[] = ['Total Fee', '$' . number_format($totalCost, 2)];
    drawTable($image, $priceData, $startY, $width, $lineHeight, $borderColor, $textColor, $fontPath);

    // Signature section with lines
    $signY = $height - 100;
    imagettftext($image, 12, 0, 40, $signY, $textColor, $fontPath, 'Director Signature: ____________________');
    imagettftext($image, 12, 0, 300, $signY, $textColor, $fontPath, 'Manager Signature: ____________________');
    imagettftext($image, 12, 0, 600, $signY, $textColor, $fontPath, 'Customer Signature: ____________________');

    // Footer
    imagettftext($image, 10, 0, 20, $height - 30, $textColor, $fontPath, '© ' . date('Y') . ' DriveXpert Rentals | All Rights Reserved');

    // Output as JPG
    header('Content-Type: image/jpeg');
    header('Content-Disposition: attachment; filename="rental_invoice_' . $data['rental_id'] . '.jpg"');
    imagejpeg($image);
    imagedestroy($image);
}

// Function to draw a bordered table
function drawTable($image, $data, $startY, $width, $lineHeight, $borderColor, $textColor, $fontPath) {
    $colX = [40, 200, 400, 600]; // Column start points
    $tableWidth = $width - 40;
    foreach ($data as $row) {
        imagerectangle($image, $colX[0] - 10, $startY - $lineHeight + 10, $tableWidth, $startY, $borderColor);
        imagettftext($image, 12, 0, $colX[0], $startY, $textColor, $fontPath, $row[0]);
        imagettftext($image, 12, 0, $colX[2], $startY, $textColor, $fontPath, $row[1]);
        $startY += $lineHeight;
    }
}
?>
