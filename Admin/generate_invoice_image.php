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

    // Create a blank image with a white background
    $width = 800;
    $height = 1200;
    $image = imagecreatetruecolor($width, $height);
    $background = imagecolorallocate($image, 255, 255, 255);
    imagefilledrectangle($image, 0, 0, $width, $height, $background);

    // Define colors
    $textColor = imagecolorallocate($image, 0, 0, 0);
    $blue = imagecolorallocate($image, 41, 128, 185);

    // Set up fonts (replace with path to your TTF fonts)
    $fontPath = './arial.ttf';

    // Add company logo
    $logoPath = '../Assets/Images/DriveXpert.png';
    if (file_exists($logoPath)) {
        $logo = imagecreatefrompng($logoPath);
        imagecopyresized($image, $logo, 20, 20, 0, 0, 100, 50, imagesx($logo), imagesy($logo));
        imagedestroy($logo);
    }

    // Company Information
    imagettftext($image, 20, 0, 150, 50, $textColor, $fontPath, 'DriveXpert Rental Company');
    imagettftext($image, 12, 0, 20, 100, $textColor, $fontPath, '1234 Elm Street, Suite 567, City Name');
    imagettftext($image, 12, 0, 20, 130, $textColor, $fontPath, 'Phone: +123 456 7890 | Email: info@drivexpert.com');

    // Invoice Title
    imagettftext($image, 18, 0, 300, 180, $blue, $fontPath, 'Rental Invoice');

    // Customer and Rental Information Table
    imagettftext($image, 14, 0, 20, 220, $textColor, $fontPath, 'Invoice Details');
    $startY = 250;
    $lineHeight = 30;

    // Table content
    $tableData = [
        ['Rental ID:', $data['rental_id'], 'Rental Period:', $data['date_from'] . ' to ' . $data['date_to']],
        ['Customer Name:', $data['customer_name'], 'Email:', $data['customer_email']],
        ['Phone:', $data['customer_phone'], 'Total Fees:', '$' . number_format($data['total_fees'], 2)],
    ];

    foreach ($tableData as $row) {
        imagettftext($image, 12, 0, 40, $startY, $textColor, $fontPath, $row[0]);
        imagettftext($image, 12, 0, 200, $startY, $textColor, $fontPath, $row[1]);
        imagettftext($image, 12, 0, 400, $startY, $textColor, $fontPath, $row[2]);
        imagettftext($image, 12, 0, 600, $startY, $textColor, $fontPath, $row[3]);
        $startY += $lineHeight;
    }

    // Car Details
    imagettftext($image, 14, 0, 20, $startY + 30, $blue, $fontPath, 'Car Details');
    $carData = [
        ['Brand:', $data['car_brand'], 'Model:', $data['car_model']],
        ['Seats:', $data['seat_count'], 'Max Speed:', $data['max_speed'] . ' km/h'],
        ['Efficiency:', $data['km_per_liter'] . ' km/l', '', '']
    ];

    $startY += 60;
    foreach ($carData as $row) {
        imagettftext($image, 12, 0, 40, $startY, $textColor, $fontPath, $row[0]);
        imagettftext($image, 12, 0, 200, $startY, $textColor, $fontPath, $row[1]);
        imagettftext($image, 12, 0, 400, $startY, $textColor, $fontPath, $row[2]);
        imagettftext($image, 12, 0, 600, $startY, $textColor, $fontPath, $row[3]);
        $startY += $lineHeight;
    }

    // Fine and Additional Options
    if (!empty($data['fine_details'])) {
        imagettftext($image, 14, 0, 20, $startY + 30, $blue, $fontPath, 'Fine Details');
        imagettftext($image, 12, 0, 40, $startY + 60, $textColor, $fontPath, $data['fine_details']);
        $startY += 90;
    }

    if (!empty($data['additional_options'])) {
        imagettftext($image, 14, 0, 20, $startY, $blue, $fontPath, 'Additional Options');
        imagettftext($image, 12, 0, 40, $startY + 30, $textColor, $fontPath, $data['additional_options']);
        $startY += 60;
    }

    // Signature section
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
?>
