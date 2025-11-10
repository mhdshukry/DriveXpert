<?php
session_start();
header('Content-Type: application/json');

$response = [
    'authorized' => false,
    'bookings' => [],
    'totals' => [
        'pending' => 0,
        'confirmed' => 0,
        'completed' => 0,
    ],
    'nextPickup' => null,
    'lastUpdated' => date('c'),
];

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode($response);
    return;
}

$userId = (int) $_SESSION['user_id'];

require_once __DIR__ . '/../config.php';

$statusMeta = [
    'pending' => [
        'label' => 'Awaiting Confirmation',
        'pill_class' => 'status-pending',
    ],
    'confirmed' => [
        'label' => 'Ready for Pickup',
        'pill_class' => 'status-confirmed',
    ],
    'completed' => [
        'label' => 'Journey Completed',
        'pill_class' => 'status-completed',
    ],
];

$defaultMeta = $statusMeta['pending'];

$statusOrder = ['pending' => 1, 'confirmed' => 2, 'completed' => 3];

$rawBookings = [];

$queries = [
    [
        'status_key' => 'pending',
        'sql' => "SELECT r.rental_id, r.car_id, r.date_from, r.date_to, r.total_cost, r.status,
                           r.created_at, c.brand, c.model
                    FROM rentals r
                    JOIN cars c ON r.car_id = c.car_id
                    WHERE r.user_id = ? AND r.status = 'pending'",
    ],
    [
        'status_key' => 'confirmed',
        'sql' => "SELECT cr.rental_id, cr.car_id, cr.date_from, cr.date_to, cr.total_cost, cr.status,
                           cr.created_at, c.brand, c.model
                    FROM confirmed_rentals cr
                    JOIN cars c ON cr.car_id = c.car_id
                    WHERE cr.user_id = ?",
    ],
    [
        'status_key' => 'completed',
        'sql' => "SELECT rh.rental_id,
                           COALESCE(cr.car_id, r.car_id) AS car_id,
                           rh.date_from, rh.date_to, rh.total_fees AS total_cost,
                           'completed' AS status,
                           rh.created_at,
                           COALESCE(c.brand, rh.car_brand) AS brand,
                           COALESCE(c.model, rh.car_model) AS model
                    FROM rental_history rh
                    LEFT JOIN confirmed_rentals cr ON rh.rental_id = cr.rental_id
                    LEFT JOIN rentals r ON rh.rental_id = r.rental_id
                    LEFT JOIN cars c ON c.car_id = COALESCE(cr.car_id, r.car_id)
                    WHERE rh.customer_id = ?",
    ],
];

foreach ($queries as $query) {
    if ($stmt = $conn->prepare($query['sql'])) {
        $stmt->bind_param('i', $userId);
        if ($stmt->execute()) {
            if ($result = $stmt->get_result()) {
                while ($row = $result->fetch_assoc()) {
                    $row['status_key'] = $query['status_key'];
                    $rawBookings[] = $row;
                }
                $result->free();
            }
        }
        $stmt->close();
    }
}

$bookingsById = [];
foreach ($rawBookings as $row) {
    $statusKey = strtolower($row['status_key']);
    $step = $statusOrder[$statusKey] ?? 1;
    $rentalId = (int) ($row['rental_id'] ?? 0);
    if ($rentalId === 0) {
        continue;
    }
    if (!isset($bookingsById[$rentalId]) || $step >= ($bookingsById[$rentalId]['status_step'] ?? 0)) {
        $row['status_step'] = $step;
        $bookingsById[$rentalId] = $row;
    }
}

$bookings = array_values($bookingsById);

usort($bookings, function (array $a, array $b) use ($statusOrder) {
    $statusCompare = ($statusOrder[$b['status_key']] ?? 0) <=> ($statusOrder[$a['status_key']] ?? 0);
    if ($statusCompare !== 0) {
        return $statusCompare;
    }
    return strcmp($b['date_from'] ?? '', $a['date_from'] ?? '');
});

$totals = [
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
];

$nextPickupDate = null;
$today = new DateTimeImmutable('today');

foreach ($bookings as $booking) {
    $statusKey = strtolower($booking['status_key']);
    if (isset($totals[$statusKey])) {
        $totals[$statusKey]++;
    }

    if (($statusOrder[$statusKey] ?? 0) >= $statusOrder['completed']) {
        continue;
    }

    $pickupDate = $booking['date_from'] ?? null;
    if (!$pickupDate) {
        continue;
    }
    $pickupDateTime = DateTime::createFromFormat('Y-m-d', $pickupDate);
    if (!$pickupDateTime) {
        continue;
    }
    if ($pickupDateTime < $today) {
        continue;
    }
    if ($nextPickupDate === null || $pickupDateTime < $nextPickupDate) {
        $nextPickupDate = $pickupDateTime;
    }
}

$formatDisplayDate = static function (?string $date): string {
    if (!$date) {
        return '-';
    }
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    if ($dt instanceof DateTime) {
        return $dt->format('M d, Y');
    }
    return $date;
};

$payloadBookings = [];
foreach ($bookings as $booking) {
    $statusKey = strtolower($booking['status_key']);
    $meta = $statusMeta[$statusKey] ?? $defaultMeta;
    $payloadBookings[] = [
        'rentalId' => (int) $booking['rental_id'],
        'carLabel' => trim(($booking['brand'] ?? '') . ' ' . ($booking['model'] ?? '')) ?: 'DriveXpert Vehicle',
        'statusKey' => $statusKey,
        'statusLabel' => $meta['label'],
        'pillClass' => $meta['pill_class'],
        'pickupDate' => $formatDisplayDate($booking['date_from'] ?? null),
        'returnDate' => $formatDisplayDate($booking['date_to'] ?? null),
        'createdAt' => $booking['created_at'] ?? null,
    ];
}

if ($nextPickupDate instanceof DateTime) {
    $response['nextPickup'] = $nextPickupDate->format('M d, Y');
}

$response['authorized'] = true;
$response['bookings'] = array_slice($payloadBookings, 0, 4);
$response['totals'] = $totals;
$response['hasBookings'] = !empty($payloadBookings);
$response['lastUpdated'] = date('c');

if ($conn instanceof mysqli) {
    $conn->close();
}

echo json_encode($response);
