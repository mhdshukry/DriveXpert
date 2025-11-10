<?php
session_start();
require_once 'config.php';

$cars = [];
$additionalOptions = [];

if (isset($conn) && $conn instanceof mysqli) {
    $carResult = $conn->query("SELECT car_id, brand, model, rent_per_day FROM cars WHERE availability = 1 ORDER BY brand, model");
    if ($carResult && $carResult->num_rows > 0) {
        while ($row = $carResult->fetch_assoc()) {
            $cars[] = $row;
        }
    }
    if ($carResult instanceof mysqli_result) {
        $carResult->free();
    }

    $optionResult = $conn->query("SELECT option_id, option_name, daily_cost FROM additional_options ORDER BY option_id");
    if ($optionResult && $optionResult->num_rows > 0) {
        while ($row = $optionResult->fetch_assoc()) {
            $additionalOptions[] = $row;
        }
    }
    if ($optionResult instanceof mysqli_result) {
        $optionResult->free();
    }

    $conn->close();
}

// Allow viewing rent form publicly; block actual submission if not logged in.
$currentUri = $_SERVER['REQUEST_URI'] ?? 'rent.php';
$signinUrl = 'auth.php?action=signin&return=' . urlencode($currentUri);
$signupUrl = 'auth.php?action=signup&return=' . urlencode($currentUri);
$logoutPath = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Admin/logout.php' : 'Client/logout.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent a Car - DriveXpert</title>
    <link rel="stylesheet" href="./Assets/CSS/rent.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" href="./Assets/Images/DriveXpert.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000&family=Oswald:wght@200..700&display=swap"
        rel="stylesheet">
</head>

<body>

    <!-- Header Section -->
    <header class="header">
        <div class="logo">
            <img src="./Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
        </div>
        <nav class="nav-links">
            <a href="./index.php">Home</a>
            <a href="./rent.php">Rent</a>
            <a href="./Cars.php">Cars</a>
            <a href="./aboutus.php">About Us</a>
            <a href="./ContactUs.php">Contact Us</a>
        </nav>
        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn logout-btn" onclick="window.location.href='./<?php echo $logoutPath; ?>'">Logout</button>
            <?php else: ?>
                <button class="btn" onclick="window.location.href='./<?php echo $signinUrl; ?>'">Sign In</button>
                <button class="btn" onclick="window.location.href='./<?php echo $signupUrl; ?>'">Sign Up</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- Rent Page Hero Section -->
    <section class="rent-hero">
        <div class="rent-hero-content">
            <p class="subheading">Choose from our premium fleet for a hassle-free car rental experience.</p>
            <h2 class="main-heading">Book Your <span class="highlight">Drive</span></h2>
        </div>
    </section>

    <!-- Rent Form Section -->
    <section class="rent-form-section">
        <h2>Rental <span class="highlight">Details</span></h2>
        <form class="rent-form" id="rentForm" method="post" action="checkout.php">
            <!-- Car Selection -->
            <div class="form-group">
                <label for="car-id">Select Car Brand &amp; Model</label>
                <select id="car-id" name="car_id" required>
                    <option value="">Choose a Brand &amp; Model</option>
                    <?php if (!empty($cars)): ?>
                        <?php foreach ($cars as $car): ?>
                            <?php $carLabel = $car['brand'] . ' ' . $car['model']; ?>
                            <option value="<?= (int) $car['car_id']; ?>"
                                data-price="<?= number_format((float) $car['rent_per_day'], 2, '.', ''); ?>"
                                data-label="<?= htmlspecialchars($carLabel, ENT_QUOTES); ?>">
                                <?= htmlspecialchars($carLabel); ?> —
                                $<?= number_format((float) $car['rent_per_day'], 2); ?>/day
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No cars currently available</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Personal Information -->
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="nic">NIC Number</label>
                <input type="text" id="nic" name="nic" required>
            </div>

            <!-- Reservation Date -->
            <div class="form-group date-group">
                <label>Reservation Date</label>
                <input type="date" id="date-from" name="date-from" required>
                <span>to</span>
                <input type="date" id="date-to" name="date-to" required>
            </div>

            <!-- Insurance Option -->
            <div class="form-group">
                <label for="insurance">Choose Insurance Plan</label>
                <select id="insurance" name="insurance" required>
                    <option value="Standard Coverage">Standard Coverage</option>
                    <option value="Full Coverage">Full Coverage</option>
                    <option value="Third-Party Only">Third-Party Only</option>
                </select>
            </div>

            <!-- Additional Options -->
            <h3>Additional Options</h3>
            <div class="options-list">
                <?php if (!empty($additionalOptions)): ?>
                    <?php foreach ($additionalOptions as $option): ?>
                        <label class="option-item">
                            <input type="checkbox" name="options[]" value="<?= (int) $option['option_id']; ?>"
                                data-price="<?= number_format((float) $option['daily_cost'], 2, '.', ''); ?>"
                                data-name="<?= htmlspecialchars($option['option_name'], ENT_QUOTES); ?>">
                            <span class="option-name"><?= htmlspecialchars($option['option_name']); ?></span>
                            <span class="option-price">+$<?= number_format((float) $option['daily_cost'], 2); ?>/day</span>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="option-empty">No additional options available at the moment.</p>
                <?php endif; ?>
            </div>

            <!-- Payment Summary -->
            <div class="summary-section">
                <div class="summary-card">
                    <div class="summary-header">
                        <h3>Summary</h3>
                        <span class="summary-chip" id="public-summary-status">Awaiting selection</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Car</span>
                        <span class="summary-value" id="selected-car">Not selected</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Rental duration</span>
                        <span class="summary-value"><span id="rental-duration">0</span> day(s)</span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row">
                        <span class="summary-label">Base cost</span>
                        <span class="summary-value">$<span id="base-cost">0.00</span></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Extras</span>
                        <span class="summary-value">$<span id="additional-fees">0.00</span></span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row summary-total">
                        <span class="summary-label">Total due</span>
                        <span class="summary-value">$<span id="total-cost">0.00</span></span>
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="form-group terms">
                <div class="terms-icon" aria-hidden="true">!</div>
                <div class="terms-content">
                    <label for="public-terms-checkbox">
                        <input id="public-terms-checkbox" type="checkbox" name="terms" required>
                        <span>I agree to the <a href="#">Terms &amp; Conditions</a> of DriveXpert rentals and
                            acknowledge the cancellation policy.</span>
                    </label>
                    <p class="terms-hint">Agreement is required before we can confirm your reservation.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn submit-btn">Reserve Now</button>
        </form>
    </section>

    <!-- Footer Section -->
    <footer class="footer-section">
        <div class="footer-container">
            <!-- Logo and Contact Info -->
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="./Assets/Images/DriveXpert.png" alt="DriveXpert Logo">
                </div>
                <p class="footer-description">DriveXpert is your trusted car rental service provider. We offer a wide
                    range of vehicles at the best prices to make your driving experience smooth and comfortable.</p>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="./index.php">Home</a></li>
                    <li><a href="./rent.php">Rent</a></li>
                    <li><a href="./Cars.php">Cars</a></li>
                    <li><a href="./aboutus.php">About Us</a></li>
                    <li><a href="./ContactUs.php">Contact Us</a></li>
                    <li><a href="./checkout.php">FAQs</a></li>
                </ul>
            </div>

            <!-- Social Media Links -->
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

            <!-- Newsletter Signup -->
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

    <script>
        (function () {
            const carSelect = document.getElementById('car-id');
            const dateFromInput = document.getElementById('date-from');
            const dateToInput = document.getElementById('date-to');
            const optionInputs = Array.from(document.querySelectorAll('input[name="options[]"]'));

            const summaryCar = document.getElementById('selected-car');
            const summaryDuration = document.getElementById('rental-duration');
            const summaryBase = document.getElementById('base-cost');
            const summaryExtras = document.getElementById('additional-fees');
            const summaryTotal = document.getElementById('total-cost');
            const summaryStatus = document.getElementById('public-summary-status');

            function formatCurrency(value) {
                const amount = typeof value === 'number' ? value : parseFloat(value);
                return (Number.isFinite(amount) ? amount : 0).toFixed(2);
            }

            function formatDate(date) {
                return date.toISOString().split('T')[0];
            }

            function setDefaultDates() {
                const today = new Date();
                const tomorrow = new Date();
                tomorrow.setDate(today.getDate() + 1);

                const todayStr = formatDate(today);
                const tomorrowStr = formatDate(tomorrow);

                if (dateFromInput) {
                    if (!dateFromInput.value) {
                        dateFromInput.value = todayStr;
                    }
                    dateFromInput.min = todayStr;
                }

                if (dateToInput) {
                    if (!dateToInput.value || dateToInput.value <= (dateFromInput?.value || '')) {
                        dateToInput.value = tomorrowStr;
                    }
                    dateToInput.min = tomorrowStr;
                }
            }

            function enforceDateOrder() {
                if (!dateFromInput || !dateToInput || !dateFromInput.value) {
                    return;
                }

                const fromDate = new Date(dateFromInput.value);
                if (Number.isNaN(fromDate.getTime())) {
                    return;
                }

                const minTo = new Date(fromDate);
                minTo.setDate(fromDate.getDate() + 1);
                const minToStr = formatDate(minTo);

                dateToInput.min = minToStr;
                if (!dateToInput.value || new Date(dateToInput.value) <= fromDate) {
                    dateToInput.value = minToStr;
                }
            }

            function getRentalDuration() {
                if (!dateFromInput || !dateToInput || !dateFromInput.value || !dateToInput.value) {
                    return 0;
                }
                const from = new Date(dateFromInput.value);
                const to = new Date(dateToInput.value);
                if (Number.isNaN(from.getTime()) || Number.isNaN(to.getTime())) {
                    return 0;
                }
                const diffMs = to.getTime() - from.getTime();
                const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));
                return diffDays > 0 ? diffDays : 0;
            }

            function getSelectedCar() {
                if (!carSelect) {
                    return null;
                }
                const option = carSelect.options[carSelect.selectedIndex];
                if (!option || !option.value) {
                    return null;
                }
                return {
                    label: option.getAttribute('data-label') || option.textContent,
                    dailyRate: parseFloat(option.getAttribute('data-price')) || 0,
                };
            }

            function getSelectedOptions() {
                return optionInputs
                    .filter((input) => input.checked)
                    .map((input) => ({
                        name: input.getAttribute('data-name') || 'Option',
                        dailyRate: parseFloat(input.getAttribute('data-price')) || 0,
                    }));
            }

            function updateSummary() {
                const selectedCar = getSelectedCar();
                const duration = getRentalDuration();
                const extras = getSelectedOptions();

                const baseCost = selectedCar ? selectedCar.dailyRate * duration : 0;
                const extrasCost = extras.reduce((sum, option) => sum + option.dailyRate * duration, 0);
                const total = baseCost + extrasCost;

                if (summaryCar) {
                    summaryCar.textContent = selectedCar ? selectedCar.label : 'Not selected';
                }
                if (summaryDuration) {
                    summaryDuration.textContent = duration;
                }
                if (summaryBase) {
                    summaryBase.textContent = formatCurrency(baseCost);
                }
                if (summaryExtras) {
                    summaryExtras.textContent = formatCurrency(extrasCost);
                }
                if (summaryTotal) {
                    summaryTotal.textContent = formatCurrency(total);
                }
                if (summaryStatus) {
                    summaryStatus.textContent = selectedCar ? (duration > 0 ? 'Ready to reserve' : 'Select valid dates') : 'Awaiting selection';
                }
            }

            function init() {
                setDefaultDates();
                enforceDateOrder();
                updateSummary();

                if (carSelect) {
                    carSelect.addEventListener('change', updateSummary);
                }
                if (dateFromInput) {
                    dateFromInput.addEventListener('change', () => {
                        enforceDateOrder();
                        updateSummary();
                    });
                }
                if (dateToInput) {
                    dateToInput.addEventListener('change', updateSummary);
                }
                optionInputs.forEach((input) => input.addEventListener('change', updateSummary));
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init, { once: true });
            } else {
                init();
            }
        })();
    </script>

    <script>
        const IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        const publicRentForm = document.getElementById('rentForm');
        if (publicRentForm) {
            publicRentForm.addEventListener('submit', function (e) {
                if (!IS_LOGGED_IN) {
                    e.preventDefault();
                    window.location.href = 'auth.php?action=signin&return=rent.php';
                }
            });
        }
    </script>
</body>

</html>