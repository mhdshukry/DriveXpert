<?php
session_start();

$action = isset($_GET['action']) ? $_GET['action'] : 'signin';
$error = isset($_GET['error']) ? $_GET['error'] : '';
$rawReturn = isset($_GET['return']) ? $_GET['return'] : '';
$sanitizedReturn = '';

if ($rawReturn && !(stripos($rawReturn, '://') !== false || strncmp($rawReturn, '//', 2) === 0)) {
	if (preg_match('#^/?[A-Za-z0-9_\-/]+\.php$#', $rawReturn)) {
		$sanitizedReturn = $rawReturn;
	}
}

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
	if ($_SESSION['role'] === 'admin') {
		$target = $sanitizedReturn ?: 'Admin/admin_dashboard.php';
		header('Location: ' . $target);
		exit;
	}
	if ($_SESSION['role'] === 'customer') {
		$target = $sanitizedReturn ?: 'Client/Home.php';
		header('Location: ' . $target);
		exit;
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>DriveXpert</title>
	<link rel="icon" type="image/png" href="./Assets/Images/DriveXpert.png">
	<link rel="stylesheet" href="./Assets/CSS/auth.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body>

	<?php $return = $sanitizedReturn; ?>
	<div class="container <?php echo ($action === 'signup') ? 'right-panel-active' : ''; ?>" id="container">
		<div class="form-container sign-up-container">
			<form action="signup.php" method="POST">
				<h1>Create Account</h1>
				<div class="social-container">
					<a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
					<a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
					<a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
				</div>
				<span>or use your email for registration</span>
				<input type="text" name="name" placeholder="Name" required />
				<input type="email" name="email" placeholder="Email" required />
				<input type="text" name="phone" placeholder="Phone Number" required />
				<input type="text" name="nic_number" placeholder="NIC Number" required />
				<input type="password" name="password" placeholder="Password" required />
				<input type="hidden" name="return" value="<?php echo htmlspecialchars($return, ENT_QUOTES); ?>">
				<button type="submit">Sign Up</button>
			</form>
		</div>
		<div class="form-container sign-in-container">
			<form action="signin.php" method="POST">
				<h1>Sign in</h1>
				<div class="social-container">
					<a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
					<a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
					<a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
				</div>
				<span>or use your account</span>
				<input type="email" name="email" placeholder="Email" required />
				<input type="password" name="password" placeholder="Password" required />
				<?php if ($error === 'invalid'): ?>
					<p class="error-message">Invalid email or password. Please try again.</p>
				<?php endif; ?>
				<a href="#">Forgot your password?</a>
				<input type="hidden" name="return" value="<?php echo htmlspecialchars($return, ENT_QUOTES); ?>">
				<button type="submit">Sign In</button>
			</form>
		</div>
		<div class="overlay-container">
			<div class="overlay">
				<div class="overlay-panel overlay-left">
					<h1>Welcome Back!</h1>
					<p>To keep connected with us please login with your personal info</p>
					<button class="ghost" id="signIn">Sign In</button>
				</div>
				<div class="overlay-panel overlay-right">
					<h1>Hello, Friend!</h1>
					<p>Enter your personal details and start your journey with us</p>
					<button class="ghost" id="signUp">Sign Up</button>
				</div>
			</div>
		</div>
	</div>

	<script src="./Assets/JS/auth.js"></script>
</body>

</html>