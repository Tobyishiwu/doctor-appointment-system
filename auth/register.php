<?php
session_start();
require "../config/database.php";

/* ===========================
   REDIRECT IF LOGGED IN
=========================== */
if (isset($_SESSION['user_id'])) {
    header("Location: ../{$_SESSION['role']}/dashboard.php");
    exit();
}

/* ===========================
   HANDLE REGISTRATION
=========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: register.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters.";
        header("Location: register.php");
        exit();
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $_SESSION['error'] = "Email already exists.";
        header("Location: register.php");
        exit();
    }

    try {
        $pdo->beginTransaction();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role)
            VALUES (?, ?, ?, 'patient')
        ");
        $stmt->execute([$name, $email, $hashedPassword]);

        $userId = $pdo->lastInsertId();

        $pdo->commit();

        // Auto login after registration
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = 'patient';
        $_SESSION['name'] = $name;

        header("Location: ../patient/dashboard.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: register.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Account - MedBook</title>
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row vh-100">

        <!-- Branding Panel -->
        <div class="col-lg-6 d-none d-lg-flex bg-primary text-white align-items-center justify-content-center">
            <div class="text-center px-5">
                <h1 class="display-5 fw-bold">MedBook</h1>
                <p class="lead mt-3">
                    Seamless appointment booking designed for modern healthcare systems.
                </p>
            </div>
        </div>

        <!-- Registration Panel -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center">

            <div class="card shadow-sm border-0 p-4" style="width:100%; max-width:450px;">

                <div class="text-center mb-4">
                    <h3 class="fw-semibold">Create Account</h3>
                    <p class="text-muted small">Register as a patient</p>
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
                        <label class="form-label small text-muted">Full Name</label>
                        <input 
                            type="text" 
                            name="name" 
                            class="form-control form-control-lg"
                            placeholder="John Doe"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Email Address</label>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control form-control-lg"
                            placeholder="you@email.com"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-muted">Password</label>
                        <input 
                            type="password" 
                            name="password" 
                            class="form-control form-control-lg"
                            placeholder="Minimum 6 characters"
                            required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Create Account
                        </button>
                    </div>

                </form>

                <div class="text-center mt-4 small">
                    Already have an account?
                    <a href="login.php" class="fw-semibold text-decoration-none">Sign In</a>
                </div>

                <div class="text-center mt-3 small text-muted">
                    © <?= date("Y"); ?> MedBook Healthcare
                </div>

            </div>

        </div>

    </div>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>