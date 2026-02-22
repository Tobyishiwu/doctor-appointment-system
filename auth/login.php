<?php
session_start();
require "../config/database.php";

/* ===========================
   REDIRECT IF ALREADY LOGGED IN
=========================== */
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: ../{$role}/dashboard.php");
    exit();
}

/* ===========================
   HANDLE LOGIN
=========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, name, role, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true); // Prevent session fixation

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        header("Location: ../{$user['role']}/dashboard.php");
        exit();

    } else {

        // Basic delay to reduce brute force attempts
        sleep(1);

        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Login - MedBook</title>

	<link href="../assets/css/app.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
	<div class="row vh-100">

		<!-- Left Branding Panel -->
		<div class="col-lg-6 d-none d-lg-flex bg-primary text-white align-items-center justify-content-center">
			<div class="text-center px-5">
				<h1 class="display-5 fw-bold">MedBook</h1>
				<p class="lead mt-3">
					A modern doctor–patient appointment system built for efficiency, clarity and seamless healthcare coordination.
				</p>
			</div>
		</div>

		<!-- Login Panel -->
		<div class="col-lg-6 d-flex align-items-center justify-content-center">

			<div class="card shadow-sm border-0 p-4" style="width:100%; max-width:420px;">
				
				<div class="text-center mb-4">
					<h3 class="fw-semibold">Sign In</h3>
					<p class="text-muted small">Access your dashboard securely</p>
				</div>

				<?php if (isset($_SESSION['error'])): ?>
					<div class="alert alert-danger alert-dismissible fade show">
						<?= htmlspecialchars($_SESSION['error']); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
					</div>
					<?php unset($_SESSION['error']); ?>
				<?php endif; ?>

				<form method="POST" autocomplete="off">

					<div class="mb-3">
						<label class="form-label small text-muted">Email Address</label>
						<input 
							type="email" 
							name="email" 
							class="form-control form-control-lg"
							placeholder="doctor@clinic.com"
							required>
					</div>

					<div class="mb-4">
						<label class="form-label small text-muted">Password</label>
						<input 
							type="password" 
							name="password" 
							class="form-control form-control-lg"
							placeholder="Enter your password"
							required>
					</div>

					<div class="d-grid">
						<button type="submit" class="btn btn-primary btn-lg">
							Sign In
						</button>
					</div>

				</form>

                
                <div class="text-center mt-4 small">
                    Not yet registered?
                    <a href="register.php" class="fw-semibold text-decoration-none">Create an account</a>
                </div>

				<div class="text-center mt-4 small text-muted">
					© <?= date("Y"); ?> MedBook Healthcare System
				</div>

			</div>

		</div>

	</div>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>