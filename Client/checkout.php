<?php
session_start();

$currentUri = $_SERVER['REQUEST_URI'] ?? 'Client/checkout.php';
$summary = $_SESSION['checkout_summary'] ?? null;
$errors = [];

function escape_output($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$checkoutCssPath = __DIR__ . '/../Assets/CSS/checkout.css';
$checkoutCssVersion = file_exists($checkoutCssPath) ? (string) filemtime($checkoutCssPath) : (string) time();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - DriveXpert</title>
    <link rel="icon" type="image/png" href="../Assets/Images/DriveXpert.png">
    <link rel="stylesheet" href="../Assets/CSS/checkout.css?v=<?= $checkoutCssVersion; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000&family=Oswald:wght@200..700&display=swap"
        rel="stylesheet">
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

    <!-- Checkout Hero Section -->
    <section class="checkout-hero">
        <div class="checkout-hero-content">
            <span class="eyebrow">Reservation Review</span>
            <h1>Finish Your Booking</h1>
            <p class="subheading">Payments happen at the DriveXpert counter. Review the details below before you
                confirm.</p>
        </div>
    </section>

    <main class="checkout-content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h3>We need a quick fix</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= escape_output($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (is_array($summary)): ?>
            <?php
            $pickupFormatted = DateTime::createFromFormat('Y-m-d', $summary['dates']['from']);
            $returnFormatted = DateTime::createFromFormat('Y-m-d', $summary['dates']['to']);
            $preparedAt = isset($summary['prepared_at']) ? date('M d, Y at H:i', (int) $summary['prepared_at']) : null;
            ?>
            <div class="checkout-grid">
                <article class="booking-card">
                    <header class="card-header">
                        <span class="status-chip">Pending pickup</span>
                        <h2><?= escape_output($summary['car']['label']); ?></h2>
                        <p class="rate-line">$<?= number_format((float) $summary['car']['daily_rate'], 2); ?>/day ·
                            <?= (int) $summary['dates']['days']; ?> day(s)</p>
                        <?php if ($preparedAt): ?>
                            <p class="timestamp">Prepared on <?= escape_output($preparedAt); ?></p>
                        <?php endif; ?>
                    </header>

                    <section class="card-section">
                        <h3>Travel Dates</h3>
                        <div class="date-pair">
                            <div>
                                <span class="label">Pick-up</span>
                                <span
                                    class="value"><?= $pickupFormatted ? $pickupFormatted->format('M d, Y') : escape_output($summary['dates']['from']); ?></span>
                            </div>
                            <div>
                                <span class="label">Return</span>
                                <span
                                    class="value"><?= $returnFormatted ? $returnFormatted->format('M d, Y') : escape_output($summary['dates']['to']); ?></span>
                            </div>
                        </div>
                    </section>

                    <section class="card-section">
                        <h3>Driver Details</h3>
                        <ul class="info-list">
                            <li><span>Name</span><span><?= escape_output($summary['customer']['name']); ?></span></li>
                            <?php if (!empty($summary['customer']['phone'])): ?>
                                <li><span>Phone</span><span><?= escape_output($summary['customer']['phone']); ?></span></li>
                            <?php endif; ?>
                            <?php if (!empty($summary['customer']['email'])): ?>
                                <li><span>Email</span><span><?= escape_output($summary['customer']['email']); ?></span></li>
                            <?php endif; ?>
                            <?php if (!empty($summary['customer']['nic'])): ?>
                                <li><span>NIC</span><span><?= escape_output($summary['customer']['nic']); ?></span></li>
                            <?php endif; ?>
                        </ul>
                    </section>

                    <?php if (!empty($summary['options'])): ?>
                        <section class="card-section">
                            <h3>Extras</h3>
                            <ul class="info-list">
                                <?php foreach ($summary['options'] as $option): ?>
                                    <li>
                                        <span><?= escape_output($option['name']); ?> (<?= (int) $summary['dates']['days']; ?>
                                            day(s))</span>
                                        <span>$<?= number_format((float) $option['total_cost'], 2); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($summary['insurance'])): ?>
                        <section class="card-section">
                            <h3>Insurance</h3>
                            <ul class="info-list">
                                <li>
                                    <span><?= escape_output($summary['insurance']['name']); ?></span>
                                    <span>$<?= number_format((float) $summary['pricing']['insurance'], 2); ?></span>
                                </li>
                            </ul>
                        </section>
                    <?php endif; ?>

                    <section class="card-section total-section">
                        <div><span>Base
                                cost</span><span>$<?= number_format((float) $summary['pricing']['base'], 2); ?></span></div>
                        <div>
                            <span>Extras</span><span>$<?= number_format((float) $summary['pricing']['extras'], 2); ?></span>
                        </div>
                        <div>
                            <span>Insurance</span><span>$<?= number_format((float) $summary['pricing']['insurance'], 2); ?></span>
                        </div>
                        <div class="grand-total"><span>Total due at
                                pickup</span><span>$<?= number_format((float) $summary['pricing']['total'], 2); ?></span>
                        </div>
                    </section>
                </article>

                <aside class="instructions-card">
                    <h2>Pay In Person</h2>
                    <p>We hold your <?= escape_output($summary['car']['label']); ?> for 24 hours after confirmation. Finish
                        the steps below to secure it.</p>
                    <ol class="steps-list">
                        <li>Visit the DriveXpert counter with your NIC/passport and driver's license.</li>
                        <li>Pay the full amount of $<?= number_format((float) $summary['pricing']['total'], 2); ?> by cash
                            or card.</li>
                        <li>Sign the rental agreement and collect your pickup receipt.</li>
                    </ol>
                    <div class="contact-card">
                        <strong>Need help?</strong>
                        <p>Call <a href="tel:+12345678901">+1 234 567 8901</a> or email <a
                                href="mailto:info@drivexpert.com">info@drivexpert.com</a>.</p>
                    </div>
                    <div class="terms-block">
                        <label for="confirm-terms">
                            <input type="checkbox" id="confirm-terms">
                            <span>I agree to settle payment in person and follow the DriveXpert rental terms.</span>
                        </label>
                    </div>
                    <button type="button" class="btn primary-btn" id="confirm-booking" disabled>Confirm Reservation
                        Hold</button>
                    <p class="fine-print">Your booking stays pending until payment is received on-site.</p>
                </aside>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>No reservation details found</h2>
                <p>Start a new booking on the <a href="./rent.php">rent page</a> to generate a checkout summary.</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Success Popup Modal -->
    <div id="success-popup" class="success-popup" aria-hidden="true">
        <div class="popup-content">
            <i class="fas fa-check-circle success-icon"></i>
            <h3>Reservation on Hold</h3>
            <p>We have recorded your booking. Bring this summary to the DriveXpert counter to finalize payment and pick
                up your vehicle.</p>
            <button type="button" class="btn close-btn" id="close-popup">Close</button>
        </div>
    </div>

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
                    <li><a href="#faq">FAQs</a></li>
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
        const IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        const SIGNIN_URL = <?php echo json_encode('../auth.php?action=signin&return=' . urlencode($currentUri)); ?>;
        const confirmButton = document.getElementById('confirm-booking');
        const termsCheckbox = document.getElementById('confirm-terms');
        const popup = document.getElementById('success-popup');
        const closePopup = document.getElementById('close-popup');

        if (termsCheckbox && confirmButton) {
            confirmButton.disabled = !termsCheckbox.checked;
            termsCheckbox.addEventListener('change', () => {
                confirmButton.disabled = !termsCheckbox.checked;
            });
        }

        const hidePopup = () => {
            if (!popup) {
                return;
            }
            popup.classList.remove('visible');
            popup.setAttribute('aria-hidden', 'true');
        };

        const openPopup = () => {
            if (!popup) {
                return;
            }
            popup.classList.add('visible');
            popup.setAttribute('aria-hidden', 'false');
            setTimeout(hidePopup, 7000);
        };

        if (confirmButton) {
            confirmButton.addEventListener('click', () => {
                if (termsCheckbox && !termsCheckbox.checked) {
                    termsCheckbox.focus();
                    return;
                }
                if (!IS_LOGGED_IN) {
                    window.location.href = SIGNIN_URL;
                    return;
                }
                openPopup();
                setTimeout(() => {
                    window.location.href = 'client_booking.php';
                }, 1600);
            });
        }

        if (closePopup) {
            closePopup.addEventListener('click', hidePopup);
        }

        if (popup) {
            popup.addEventListener('click', (event) => {
                if (event.target === popup) {
                    hidePopup();
                }
            });
        }
    </script>

</body>

</html>