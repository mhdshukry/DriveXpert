<?php
session_start();
include '../config.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to rent a car.'); window.location.href='../auth.php';</script>";
    exit;
}

// Fetch available cars from the database
$carQuery = "SELECT car_id, brand, model, rent_per_day FROM cars WHERE availability = 1";
$carResult = $conn->query($carQuery);

$cars = [];
if ($carResult && $carResult->num_rows > 0) {
    while ($car = $carResult->fetch_assoc()) {
        $cars[] = $car;
    }
}

// Fetch optional add-ons
$optionsQuery = "SELECT option_id, option_name, daily_cost FROM additional_options ORDER BY option_id";
$optionsResult = $conn->query($optionsQuery);

$additionalOptions = [];
if ($optionsResult && $optionsResult->num_rows > 0) {
    while ($option = $optionsResult->fetch_assoc()) {
        $additionalOptions[] = $option;
    }
}

// Handle form submission for rental
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $car_id = isset($_POST['car_id']) ? (int) $_POST['car_id'] : 0;
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $selectedOptions = isset($_POST['options']) && is_array($_POST['options']) ? array_map('intval', $_POST['options']) : [];

    $from_date = DateTime::createFromFormat('Y-m-d', $date_from);
    $to_date = DateTime::createFromFormat('Y-m-d', $date_to);

    if (!$from_date || !$to_date) {
        $errorMessage = 'Please provide valid reservation dates.';
    } else {
        $rental_duration = $from_date->diff($to_date)->days;
        if ($rental_duration < 1) {
            $errorMessage = 'Rental duration must be at least one day.';
        }
    }

    $selectedCarLabel = null;
    if (!$errorMessage) {
        foreach ($cars as $car) {
            if ((int) $car['car_id'] === $car_id) {
                $selectedCarLabel = trim($car['brand'] . ' ' . $car['model']);
                break;
            }
        }
        if ($selectedCarLabel === null) {
            $errorMessage = 'Selected car could not be found. Please choose another vehicle.';
        }
    }

    $carBrand = '';
    $carModel = '';
    $rent_per_day = 0.0;

    if (!$errorMessage) {
        $carQuery = $conn->prepare("SELECT brand, model, rent_per_day FROM cars WHERE car_id = ? AND availability = 1");
        $carQuery->bind_param("i", $car_id);
        $carQuery->execute();
        $carQuery->bind_result($carBrand, $carModel, $rent_per_day);
        if (!$carQuery->fetch()) {
            $errorMessage = 'Selected car is not available.';
        }
        $carQuery->close();
        if (!$errorMessage) {
            $selectedCarLabel = trim(($carBrand ?? '') . ' ' . ($carModel ?? '')) ?: $selectedCarLabel;
        }
    }

    $base_cost = 0.0;
    $additional_fees = 0.0;
    $selectedOptionDetails = [];

    if (!$errorMessage) {
        $base_cost = (float) $rent_per_day * (int) $rental_duration;

        if ($selectedOptions) {
            $placeholders = implode(',', array_fill(0, count($selectedOptions), '?'));
            $types = str_repeat('i', count($selectedOptions));
            $optionsStmt = $conn->prepare("SELECT option_id, option_name, daily_cost FROM additional_options WHERE option_id IN ($placeholders)");

            $bindParams = [$types];
            foreach ($selectedOptions as $idx => $optionId) {
                $selectedOptions[$idx] = (int) $optionId;
                $bindParams[] = &$selectedOptions[$idx];
            }

            $optionsStmt->bind_param(...$bindParams);
            $optionsStmt->execute();
            $optionsResultSet = $optionsStmt->get_result();
            while ($optionRow = $optionsResultSet->fetch_assoc()) {
                $optionTotal = (float) $optionRow['daily_cost'] * (int) $rental_duration;
                $additional_fees += $optionTotal;
                $selectedOptionDetails[] = [
                    'id' => (int) $optionRow['option_id'],
                    'name' => $optionRow['option_name'],
                    'daily_cost' => (float) $optionRow['daily_cost'],
                    'total_cost' => $optionTotal,
                ];
            }
            $optionsStmt->close();
        }

        $total_cost = $base_cost + $additional_fees;

        $stmt = $conn->prepare("INSERT INTO rentals (user_id, car_id, date_from, date_to, total_cost, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iissd", $user_id, $car_id, $date_from, $date_to, $total_cost);

        if ($stmt->execute()) {
            $rentalId = $conn->insert_id;
            if ($rentalId && $selectedOptions) {
                $linkStmt = $conn->prepare("INSERT INTO rental_options (rental_id, option_id) VALUES (?, ?)");
                foreach ($selectedOptions as $optionId) {
                    $linkStmt->bind_param("ii", $rentalId, $optionId);
                    $linkStmt->execute();
                }
                $linkStmt->close();
            }

            $userStmt = $conn->prepare("SELECT name, email, phone, nic_number FROM users WHERE user_id = ?");
            $userStmt->bind_param("i", $user_id);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $userData = $userResult ? $userResult->fetch_assoc() : null;
            $userStmt->close();

            $_SESSION['checkout_summary'] = [
                'rental_id' => $rentalId,
                'prepared_at' => time(),
                'car' => [
                    'id' => $car_id,
                    'label' => $selectedCarLabel,
                    'daily_rate' => (float) $rent_per_day,
                ],
                'dates' => [
                    'from' => $date_from,
                    'to' => $date_to,
                    'days' => (int) $rental_duration,
                ],
                'options' => array_map(static function (array $option) {
                    return [
                        'name' => $option['name'],
                        'total_cost' => $option['total_cost'],
                    ];
                }, $selectedOptionDetails),
                'insurance' => null,
                'pricing' => [
                    'base' => $base_cost,
                    'extras' => $additional_fees,
                    'insurance' => 0.0,
                    'total' => $total_cost,
                ],
                'customer' => [
                    'name' => $userData['name'] ?? 'DriveXpert Guest',
                    'email' => $userData['email'] ?? '',
                    'phone' => $userData['phone'] ?? '',
                    'nic' => $userData['nic_number'] ?? '',
                ],
            ];

            $stmt->close();

            header('Location: checkout.php');
            exit;
        }

        $errorMessage = 'Error booking rental. Please try again.';
        $stmt->close();
    }

    if ($errorMessage) {
        echo "<script>alert('" . addslashes($errorMessage) . "');</script>";
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent a Car - DriveXpert</title>
    <link rel="icon" type="image/png" href="../Assets/Images/DriveXpert.png">
    <link rel="stylesheet" href="../Assets/CSS/rent.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>

    <!-- Header Section -->
    <header class="header">
        <div class="logo">
            <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
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

    <!-- Rent Form Section -->
    <section class="rent-form-section">
        <h2>Rental Details</h2>
        <form method="post" class="rent-form" id="rentalForm">
            <!-- Car Selection -->
            <div class="form-group">
                <label for="car-id">Select Car</label>
                <select id="car-id" name="car_id" required>
                    <option value="" data-price="0">Choose a Car</option>
                    <?php foreach ($cars as $car): ?>
                        <option value="<?= $car['car_id'] ?>" data-price="<?= $car['rent_per_day'] ?>"
                            data-label="<?= htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES) ?>">
                            <?= htmlspecialchars($car['brand'] . " " . $car['model']) ?> —
                            $<?= number_format($car['rent_per_day'], 2) ?>/day
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Reservation Dates -->
            <div class="form-group">
                <label>Reservation Dates</label>
                <div class="date-group">
                    <input type="date" name="date_from" id="date-from" required>
                    <span class="date-separator">to</span>
                    <input type="date" name="date_to" id="date-to" required>
                </div>
            </div>

            <!-- Additional Options -->
            <h3>Additional Options</h3>
            <div class="options-list">
                <?php if (!empty($additionalOptions)): ?>
                    <?php foreach ($additionalOptions as $option): ?>
                        <label class="option-item">
                            <input type="checkbox" name="options[]" value="<?= (int) $option['option_id'] ?>"
                                data-price="<?= number_format($option['daily_cost'], 2, '.', '') ?>"
                                data-name="<?= htmlspecialchars($option['option_name'], ENT_QUOTES) ?>">
                            <span class="option-name"><?= htmlspecialchars($option['option_name']) ?></span>
                            <span class="option-price">+$<?= number_format($option['daily_cost'], 2) ?>/day</span>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="option-empty">No additional options available at the moment.</p>
                <?php endif; ?>
            </div>

            <!-- Rental Summary Section -->
            <div class="summary-section">
                <div class="summary-card">
                    <div class="summary-header">
                        <h3>Booking Summary</h3>
                        <span class="summary-chip" id="summary-status">Awaiting selection</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Car</span>
                        <span class="summary-value" id="summary-car">Select a car</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Rental duration</span>
                        <span class="summary-value"><span id="summary-duration">0</span> day(s)</span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row">
                        <span class="summary-label">Base cost</span>
                        <span class="summary-value">$<span id="summary-base">0.00</span></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Extras</span>
                        <span class="summary-value">$<span id="summary-extras">0.00</span></span>
                    </div>
                    <ul class="summary-extras-list" id="summary-extras-list"></ul>
                    <div class="summary-divider"></div>
                    <div class="summary-row summary-total">
                        <span class="summary-label">Total due</span>
                        <span class="summary-value">$<span id="summary-total">0.00</span></span>
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="form-group terms">
                <div class="terms-icon" aria-hidden="true">!</div>
                <div class="terms-content">
                    <label for="terms-checkbox">
                        <input id="terms-checkbox" type="checkbox" required>
                        <span>I agree to the <a href="#">Terms &amp; Conditions</a> and acknowledge the cancellation
                            policy.</span>
                    </label>
                    <p class="terms-hint">Please review the rental guidelines before completing your reservation.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn submit-btn">Reserve Now</button>
        </form>
    </section>

    <!-- Footer Section -->
    <footer class="footer-section">
        <div class="footer-container">
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="../Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
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
                    <li><a href="./client_booking.php">FAQs</a></li>
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
            <p>&copy; 2024 DriveXpert. All Rights Reserved. | Privacy Policy | Terms & Conditions</p>
        </div>
    </footer>

    <?php include __DIR__ . '/status_fab.php'; ?>

    <script>
        const rentalForm = document.getElementById('rentalForm');
        const carSelect = document.getElementById('car-id');
        const dateFromInput = document.getElementById('date-from');
        const dateToInput = document.getElementById('date-to');
        const optionInputs = Array.from(document.querySelectorAll('input[name="options[]"]'));

        const summaryCar = document.getElementById('summary-car');
        const summaryDuration = document.getElementById('summary-duration');
        const summaryBase = document.getElementById('summary-base');
        const summaryExtras = document.getElementById('summary-extras');
        const summaryExtrasList = document.getElementById('summary-extras-list');
        const summaryTotal = document.getElementById('summary-total');
        const summaryStatus = document.getElementById('summary-status');

        function formatDate(date) {
            return date.toISOString().split('T')[0];
        }

        function formatCurrency(value) {
            const amount = typeof value === 'number' ? value : parseFloat(value);
            return (Number.isFinite(amount) ? amount : 0).toFixed(2);
        }

        function setDefaultDates() {
            const today = new Date();
            const tomorrow = new Date();
            tomorrow.setDate(today.getDate() + 1);

            const todayStr = formatDate(today);
            const tomorrowStr = formatDate(tomorrow);

            if (!dateFromInput.value) {
                dateFromInput.value = todayStr;
            }
            if (!dateToInput.value || dateToInput.value <= dateFromInput.value) {
                dateToInput.value = tomorrowStr;
            }

            dateFromInput.min = todayStr;
            dateToInput.min = tomorrowStr;
        }

        function enforceDateOrder() {
            if (!dateFromInput.value) {
                return;
            }

            const fromDate = new Date(dateFromInput.value);
            const minTo = new Date(fromDate);
            minTo.setDate(fromDate.getDate() + 1);
            const minToStr = formatDate(minTo);

            dateToInput.min = minToStr;

            if (!dateToInput.value || dateToInput.value <= dateFromInput.value) {
                dateToInput.value = minToStr;
            }
        }

        function getRentalDuration() {
            if (!dateFromInput.value || !dateToInput.value) {
                return 0;
            }
            const from = new Date(dateFromInput.value);
            const to = new Date(dateToInput.value);
            const diffMs = to.getTime() - from.getTime();
            const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));
            return diffDays > 0 ? diffDays : 0;
        }

        function getSelectedCar() {
            const selectedOption = carSelect.options[carSelect.selectedIndex];
            if (!selectedOption || !selectedOption.value) {
                return null;
            }
            return {
                id: parseInt(selectedOption.value, 10),
                label: selectedOption.getAttribute('data-label') || selectedOption.text,
                dailyRate: parseFloat(selectedOption.getAttribute('data-price')) || 0,
            };
        }

        function getSelectedOptions() {
            return optionInputs
                .filter((input) => input.checked)
                .map((input) => ({
                    id: parseInt(input.value, 10),
                    name: input.getAttribute('data-name') || 'Option',
                    dailyRate: parseFloat(input.getAttribute('data-price')) || 0,
                }));
        }

        function updateExtrasList(selectedOptions, duration) {
            if (!selectedOptions.length || duration === 0) {
                summaryExtrasList.classList.remove('visible');
                summaryExtrasList.innerHTML = '';
                return;
            }

            const fragments = selectedOptions
                .map((option) => {
                    const optionTotal = option.dailyRate * duration;
                    return `<li><span>${option.name}</span><span>$${formatCurrency(optionTotal)}</span></li>`;
                })
                .join('');

            summaryExtrasList.innerHTML = fragments;
            summaryExtrasList.classList.add('visible');
        }

        function updateSummary() {
            const selectedCar = getSelectedCar();
            const duration = getRentalDuration();
            const selectedOptions = getSelectedOptions();

            const baseCost = selectedCar ? selectedCar.dailyRate * duration : 0;
            const extrasCost = selectedOptions.reduce((total, option) => total + option.dailyRate * duration, 0);
            const totalCost = baseCost + extrasCost;

            summaryCar.textContent = selectedCar ? selectedCar.label : 'Select a car';
            summaryDuration.textContent = duration;
            summaryBase.textContent = formatCurrency(baseCost);
            summaryExtras.textContent = formatCurrency(extrasCost);
            summaryTotal.textContent = formatCurrency(totalCost);

            if (selectedCar) {
                summaryStatus.textContent = duration > 0 ? 'Ready to reserve' : 'Select valid dates';
            } else {
                summaryStatus.textContent = 'Awaiting selection';
            }

            updateExtrasList(selectedOptions, duration);
        }

        function initRentalForm() {
            if (!carSelect || !dateFromInput || !dateToInput) {
                return;
            }

            setDefaultDates();
            enforceDateOrder();
            updateSummary();

            carSelect.addEventListener('change', updateSummary);
            dateFromInput.addEventListener('change', () => {
                enforceDateOrder();
                updateSummary();
            });
            dateToInput.addEventListener('change', updateSummary);
            optionInputs.forEach((input) => {
                input.addEventListener('change', updateSummary);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRentalForm, { once: true });
        } else {
            initRentalForm();
        }
    </script>

</body>

</html>