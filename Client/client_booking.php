<?php
session_start();
require_once '../config.php';
require_once __DIR__ . '/../Admin/rental_detail_helpers.php';

if (!isset($_SESSION['user_id'])) {
	echo "<script>alert('Please log in to view your bookings.'); window.location.href='../auth.php';</script>";
	exit;
}

$userId = (int) $_SESSION['user_id'];

$currentUser = [
	'name' => '',
	'email' => '',
	'phone' => '',
	'nic_number' => ''
];

if ($stmt = $conn->prepare('SELECT name, email, phone, nic_number FROM users WHERE user_id = ?')) {
	$stmt->bind_param('i', $userId);
	if ($stmt->execute() && ($result = $stmt->get_result())) {
		if ($row = $result->fetch_assoc()) {
			$currentUser = array_map(static fn($value) => $value ?? '', $row);
		}
		$result->free();
	}
	$stmt->close();
}

$assetPrefix = '../';

function escape_output($value): string
{
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_display_date(?string $date): string
{
	if (!$date) {
		return '-';
	}
	$dateTime = DateTime::createFromFormat('Y-m-d', $date);
	if ($dateTime instanceof DateTime) {
		return $dateTime->format('M d, Y');
	}
	return escape_output($date);
}

function format_timestamp(?string $timestamp): ?string
{
	if (!$timestamp) {
		return null;
	}
	$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $timestamp);
	if ($dateTime instanceof DateTime) {
		return $dateTime->format('M d, Y at H:i');
	}
	return $timestamp;
}

$statusMeta = [
	'pending' => [
		'label' => 'Awaiting Confirmation',
		'message' => 'Our concierge team is verifying your reservation details. Expect a confirmation shortly.',
		'pill_class' => 'status-pending',
	],
	'confirmed' => [
		'label' => 'Ready for Pickup',
		'message' => 'Your vehicle is reserved and will be prepped before you arrive at the DriveXpert counter.',
		'pill_class' => 'status-confirmed',
	],
	'completed' => [
		'label' => 'Journey Completed',
		'message' => 'This rental has been wrapped up. Thanks for riding with DriveXpert!',
		'pill_class' => 'status-completed',
	],
];

$statusOrder = ['pending' => 1, 'confirmed' => 2, 'completed' => 3];

$rawBookings = [];
$queries = [
	[
		'status_key' => 'pending',
		'sql' => "SELECT r.rental_id, r.car_id, r.date_from, r.date_to, r.total_cost, r.status,
			           r.created_at,
			           c.brand AS car_brand, c.model AS car_model,
			           c.seat_count, c.max_speed, c.km_per_liter, c.rent_per_day,
			           c.car_picture, c.logo_picture
			FROM rentals r
			JOIN cars c ON r.car_id = c.car_id
			WHERE r.user_id = ? AND r.status = 'pending'",
	],
	[
		'status_key' => 'confirmed',
		'sql' => "SELECT cr.rental_id, cr.car_id, cr.date_from, cr.date_to, cr.total_cost, cr.status,
			           cr.created_at,
			           c.brand AS car_brand, c.model AS car_model,
			           c.seat_count, c.max_speed, c.km_per_liter, c.rent_per_day,
			           c.car_picture, c.logo_picture
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
			           COALESCE(c.brand, rh.car_brand) AS car_brand,
			           COALESCE(c.model, rh.car_model) AS car_model,
			           c.seat_count, c.max_speed, c.km_per_liter, c.rent_per_day,
			           c.car_picture, c.logo_picture
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
	$statusKey = $row['status_key'];
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
$rentalIds = array_column($bookings, 'rental_id');

$extrasByRental = [];
$insuranceByRental = [];
$finesByRental = [];

if (!empty($rentalIds)) {
	$supplementary = collectSupplementaryRentalData($conn, $rentalIds);
	$extrasByRental = $supplementary['extras'] ?? [];
	$insuranceByRental = $supplementary['insurance'] ?? [];
	$finesByRental = $supplementary['fines'] ?? [];
}

foreach ($bookings as &$booking) {
	$rentalId = (int) $booking['rental_id'];
	$statusKey = $booking['status_key'] ?? ($booking['status'] ?? 'pending');
	$statusKey = strtolower($statusKey);
	if (!isset($statusMeta[$statusKey])) {
		$statusKey = 'pending';
	}

	$booking['status_key'] = $statusKey;
	$booking['status_step'] = $statusOrder[$statusKey] ?? 1;
	$booking['status_label'] = $statusMeta[$statusKey]['label'];
	$booking['status_message'] = $statusMeta[$statusKey]['message'];
	$booking['status_pill_class'] = $statusMeta[$statusKey]['pill_class'];

	$booking['total_cost'] = isset($booking['total_cost']) ? (float) $booking['total_cost'] : 0.0;

	$pickupDate = $booking['date_from'] ?? null;
	$returnDate = $booking['date_to'] ?? null;
	$pickupDateTime = $pickupDate ? DateTime::createFromFormat('Y-m-d', $pickupDate) : null;
	$returnDateTime = $returnDate ? DateTime::createFromFormat('Y-m-d', $returnDate) : null;

	$durationDays = 1;
	if ($pickupDateTime && $returnDateTime) {
		$diff = $pickupDateTime->diff($returnDateTime);
		$durationDays = max(1, (int) $diff->days);
	}

	$booking['pickup_display'] = format_display_date($pickupDate);
	$booking['return_display'] = format_display_date($returnDate);
	$booking['duration_days'] = $durationDays;

	$progressPercent = ($booking['status_step'] - 1);
	$progressPercent = max(0, min(2, $progressPercent));
	$booking['progress_percent'] = $progressPercent * 50;

	$carBrand = $booking['car_brand'] ?? ($booking['brand'] ?? '');
	$carModel = $booking['car_model'] ?? ($booking['model'] ?? '');
	$booking['car_brand'] = $carBrand;
	$booking['car_model'] = $carModel;
	$booking['car_label'] = trim($carBrand . ' ' . $carModel) ?: 'DriveXpert Vehicle';
	$booking['car_picture_url'] = !empty($booking['car_picture'])
		? ('../' . ltrim($booking['car_picture'], '/\\'))
		: null;

	$extrasList = [];
	if (!empty($extrasByRental[$rentalId])) {
		foreach ($extrasByRental[$rentalId] as $extra) {
			$dailyCost = isset($extra['daily_cost']) ? (float) $extra['daily_cost'] : 0.0;
			$extraTotal = $dailyCost * $durationDays;
			$extrasList[] = [
				'name' => $extra['name'] ?? $extra['option_name'] ?? 'Add-on',
				'total_cost' => $extraTotal,
				'daily_cost' => $dailyCost,
			];
		}
	}
	$booking['extras'] = $extrasList;

	$insuranceDetail = $insuranceByRental[$rentalId] ?? null;
	if ($insuranceDetail) {
		$insuranceDetail['total_cost'] = isset($insuranceDetail['cost']) ? (float) $insuranceDetail['cost'] : 0.0;
	}
	$booking['insurance_detail'] = $insuranceDetail;

	$fineEntries = $finesByRental[$rentalId] ?? [];
	if (!empty($fineEntries)) {
		$primaryFine = $fineEntries[0];
		$booking['fine'] = [
			'amount' => isset($primaryFine['amount']) ? (float) $primaryFine['amount'] : 0.0,
			'reason' => $primaryFine['reason'] ?? 'Late fee',
		];
	} else {
		$booking['fine'] = null;
	}

	$booking['created_display'] = format_timestamp($booking['created_at'] ?? null);

	$detailRow = $booking;
	$detailRow['customer_name'] = $currentUser['name'] ?? '';
	$detailRow['customer_email'] = $currentUser['email'] ?? '';
	$detailRow['customer_phone'] = $currentUser['phone'] ?? '';
	$detailRow['nic_number'] = $currentUser['nic_number'] ?? '';

	$detailPayload = buildRentalDetailPayload(
		$detailRow,
		$extrasByRental,
		$insuranceByRental,
		$finesByRental,
		['symbol' => '₱', 'code' => 'PHP']
	);

	$detailJson = json_encode($detailPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if ($detailJson === false) {
		$detailJson = '{}';
	}
	$booking['detail_payload'] = $detailPayload;
	$booking['detail_payload_json'] = $detailJson;
}
unset($booking);

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
foreach ($bookings as $booking) {
	$totals[$booking['status_key']] = ($totals[$booking['status_key']] ?? 0) + 1;
}

$activeReservations = $totals['pending'] + $totals['confirmed'];
$completedTrips = $totals['completed'];
$nextPickupLabel = 'No upcoming pickup yet';
$today = new DateTimeImmutable('today');
$nextPickupDate = null;

foreach ($bookings as $booking) {
	if (($statusOrder[$booking['status_key']] ?? 0) >= $statusOrder['completed']) {
		continue;
	}
	$pickupDateTime = ($booking['date_from'] ?? '') ? DateTime::createFromFormat('Y-m-d', $booking['date_from']) : null;
	if (!$pickupDateTime) {
		continue;
	}
	if ($pickupDateTime < $today) {
		continue;
	}
	if ($nextPickupDate === null || $pickupDateTime < $nextPickupDate) {
		$nextPickupDate = $pickupDateTime;
		$nextPickupLabel = $pickupDateTime->format('M d, Y');
	}
}

$lastUpdated = date('M d, Y at H:i');

$bookingCssPath = __DIR__ . '/../Assets/CSS/booking.css';
$bookingCssVersion = file_exists($bookingCssPath) ? (string) filemtime($bookingCssPath) : (string) time();
$modalScriptPath = __DIR__ . '/../Assets/JS/rental-detail-modal.js';
$modalScriptVersion = file_exists($modalScriptPath) ? (string) filemtime($modalScriptPath) : (string) time();

if ($conn instanceof mysqli) {
	$conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Booking Status - DriveXpert</title>
	<link rel="icon" type="image/png" href="<?= $assetPrefix; ?>Assets/Images/DriveXpert.png">
	<link rel="stylesheet" href="<?= $assetPrefix; ?>Assets/CSS/booking.css?v=<?= $bookingCssVersion; ?>">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link
		href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000&family=Oswald:wght@200..700&display=swap"
		rel="stylesheet">
</head>

<body>
	<header class="header">
		<div class="logo">
			<img src="<?= $assetPrefix; ?>Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
		</div>
		<nav class="nav-links">
			<a href="./Home.php">Home</a>
			<a href="./rent.php">Rent</a>
			<a href="./Cars.php">Cars</a>
			<a href="./aboutus.php">About Us</a>
			<a href="./ContactUs.php">Contact Us</a>
		</nav>
		<div class="auth-buttons">
			<button class="btn logout-btn" onclick="window.location.href='logout.php'">Logout</button>
		</div>
	</header>

	<section class="status-hero">
		<div class="status-hero-content">
			<span class="eyebrow">My DriveXpert Journey</span>
			<h1>Track Your Booking Status</h1>
			<p class="subheading">Follow every step of your reservation in real-time. When you are ready, bring this
				summary to the DriveXpert counter to complete payment and hit the road.</p>
			<div class="hero-metrics">
				<div class="metric-card">
					<span class="metric-label">Active reservations</span>
					<span class="metric-value"><?= escape_output((string) $activeReservations); ?></span>
				</div>
				<div class="metric-card">
					<span class="metric-label">Completed trips</span>
					<span class="metric-value"><?= escape_output((string) $completedTrips); ?></span>
				</div>
				<div class="metric-card">
					<span class="metric-label">Next pickup</span>
					<span class="metric-value"><?= escape_output($nextPickupLabel); ?></span>
				</div>
			</div>
			<p class="snapshot">Last updated <?= escape_output($lastUpdated); ?></p>
		</div>
	</section>

	<main class="booking-layout">
		<section class="status-overview">
			<div class="overview-header">
				<h2>Your reservation timeline</h2>
				<p>Each reservation card shows where you are in the DriveXpert process, including extras, insurance, and
					any outstanding actions.</p>
			</div>
			<div class="status-counts">
				<div class="status-chip status-pending">Pending <?= escape_output((string) $totals['pending']); ?></div>
				<div class="status-chip status-confirmed">Confirmed <?= escape_output((string) $totals['confirmed']); ?>
				</div>
				<div class="status-chip status-completed">Completed <?= escape_output((string) $totals['completed']); ?>
				</div>
			</div>
		</section>

		<section class="bookings-section">
			<?php if (empty($bookings)): ?>
				<div class="empty-state">
					<i class="fas fa-map-signs"></i>
					<h3>No reservations yet</h3>
					<p>You have not booked a DriveXpert vehicle. Reserve your next ride to see it appear here.</p>
					<button class="btn primary" onclick="window.location.href='rent.php'">Plan a rental</button>
				</div>
			<?php else: ?>
				<div class="booking-grid">
					<?php foreach ($bookings as $booking): ?>
						<article class="booking-card reveal">
							<div class="card-header">
								<div class="car-meta">
									<h3><?= escape_output($booking['car_label']); ?></h3>
									<p class="reservation-id">Reservation #<?= escape_output((string) $booking['rental_id']); ?>
									</p>
								</div>
								<span
									class="status-pill <?= escape_output($booking['status_pill_class']); ?>"><?= escape_output($booking['status_label']); ?></span>
							</div>
							<div class="card-body">
								<?php if (!empty($booking['car_picture_url'])): ?>
									<div class="vehicle-visual">
										<img src="<?= escape_output($booking['car_picture_url']); ?>"
											alt="<?= escape_output($booking['car_label']); ?>">
									</div>
								<?php endif; ?>
								<div class="booking-progress">
									<div class="progress-track">
										<div class="progress-fill"
											style="width: <?= escape_output((string) $booking['progress_percent']); ?>%"></div>
									</div>
									<div class="progress-steps">
										<span class="step">Reserved</span>
										<span class="step">Confirmed</span>
										<span class="step">Completed</span>
									</div>
									<p class="status-message"><?= escape_output($booking['status_message']); ?></p>
								</div>
								<div class="booking-details">
									<div class="detail-col">
										<h4>Trip details</h4>
										<ul>
											<li><i class="far fa-calendar-check"></i> Pickup
												<?= escape_output($booking['pickup_display']); ?>
											</li>
											<li><i class="far fa-calendar"></i> Return
												<?= escape_output($booking['return_display']); ?>
											</li>
											<li><i class="far fa-clock"></i> Duration
												<?= escape_output((string) $booking['duration_days']); ?> day(s)
											</li>
										</ul>
									</div>
									<div class="detail-col">
										<h4>Extras</h4>
										<?php if (!empty($booking['extras'])): ?>
											<ul>
												<?php foreach ($booking['extras'] as $extra): ?>
													<li><i class="fas fa-plus-circle"></i> <?= escape_output($extra['name']); ?> &mdash;
														₱<?= number_format($extra['total_cost'], 2); ?></li>
												<?php endforeach; ?>
											</ul>
										<?php else: ?>
											<p class="muted">No add-ons selected</p>
										<?php endif; ?>
									</div>
									<div class="detail-col">
										<h4>Coverage & totals</h4>
										<ul>
											<li><i class="fas fa-receipt"></i> Base:
												₱<?= number_format($booking['total_cost'], 2); ?></li>
											<?php if (!empty($booking['insurance_detail'])): ?>
												<li><i class="fas fa-shield-alt"></i> Insurance:
													<?= escape_output($booking['insurance_detail']['name']); ?>
													(₱<?= number_format($booking['insurance_detail']['total_cost'], 2); ?>)
												</li>
											<?php endif; ?>
											<?php if (!empty($booking['fine'])): ?>
												<li class="fine-item"><i class="fas fa-exclamation-triangle"></i> Fine:
													₱<?= number_format($booking['fine']['amount'], 2); ?> &mdash;
													<?= escape_output($booking['fine']['reason']); ?>
												</li>
											<?php endif; ?>
										</ul>
									</div>
								</div>
							</div>
							<footer class="card-footer">
								<span class="created">Requested <?= escape_output($booking['created_display'] ?? ''); ?></span>
								<div class="card-footer-actions">
									<button type="button" class="btn tertiary js-view-booking"
										data-rental-details='<?= htmlspecialchars($booking['detail_payload_json'], ENT_QUOTES, 'UTF-8'); ?>'>View
										reservation details</button>
									<?php if (in_array($booking['status_key'], ['pending', 'confirmed'], true)): ?>
										<button type="button" class="btn secondary"
											onclick="window.location.href='checkout.php?rental_id=<?= urlencode((string) $booking['rental_id']); ?>'">View
											payment details</button>
									<?php endif; ?>
								</div>
							</footer>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	</main>

	<footer class="footer-section">
		<div class="footer-container">
			<div class="footer-column">
				<div class="footer-logo">
					<img src="<?= $assetPrefix; ?>Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
				</div>
				<p class="footer-description">DriveXpert is your trusted car rental service provider. We offer a wide
					range of vehicles at the best prices to make your driving experience smooth and comfortable.</p>
			</div>
			<div class="footer-column">
				<h4>Quick Links</h4>
				<ul class="footer-links">
					<li><a href="./Home.php">Home</a></li>
					<li><a href="./rent.php">Rent</a></li>
					<li><a href="./Cars.php">Cars</a></li>
					<li><a href="./aboutus.php">About Us</a></li>
					<li><a href="./ContactUs.php">Contact Us</a></li>
					<li><a href="./client_booking.php">My Bookings</a></li>
				</ul>
			</div>
			<div class="footer-column">
				<h4>Follow Us</h4>
				<ul class="footer-social">
					<li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
					<li><a href="#"><i class="fab fa-twitter"></i></a></li>
					<li><a href="#"><i class="fab fa-instagram"></i></a></li>
					<li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
				</ul>
				<p class="footer-contact">Contact: +1 234 567 8901</p>
				<p class="footer-email">Email: info@drivexpert.com</p>
			</div>
			<div class="footer-column">
				<h4>Newsletter</h4>
				<p>Subscribe to our newsletter for the latest offers and updates!</p>
				<form class="footer-newsletter-form">
					<input type="email" placeholder="Enter your email" required>
					<button type="submit" class="btn newsletter-btn">Subscribe</button>
				</form>
			</div>
		</div>

		<div class="footer-bottom">
			<p>&copy; 2024 DriveXpert. All Rights Reserved. | Privacy Policy | Terms &amp; Conditions</p>
		</div>
	</footer>

	<?php include __DIR__ . '/status_fab.php'; ?>

	<script
		src="<?= $assetPrefix; ?>Assets/JS/rental-detail-modal.js?v=<?= htmlspecialchars($modalScriptVersion, ENT_QUOTES, 'UTF-8'); ?>"></script>
	<script>
		const observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					entry.target.classList.add('visible');
					observer.unobserve(entry.target);
				}
			});
		}, { threshold: 0.2 });

		document.querySelectorAll('.reveal').forEach((card) => observer.observe(card));
	</script>
</body>

</html>