<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ===========================
   HANDLE DELETE
=========================== */
if (isset($_GET['delete'])) {

    $doctorUserId = (int) $_GET['delete'];

    try {
        $pdo->beginTransaction();

        // Get doctor id from doctors table
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
        $stmt->execute([$doctorUserId]);
        $doctor = $stmt->fetch();

        if ($doctor) {
            $doctorId = $doctor['id'];

            // Delete related appointments first
            $pdo->prepare("DELETE FROM appointments WHERE doctor_id = ?")
                ->execute([$doctorId]);

            // Delete doctor profile
            $pdo->prepare("DELETE FROM doctors WHERE user_id = ?")
                ->execute([$doctorUserId]);

            // Delete user record
            $pdo->prepare("DELETE FROM users WHERE id = ? AND role='doctor'")
                ->execute([$doctorUserId]);

            $pdo->commit();
            $_SESSION['success'] = "Doctor removed successfully.";
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Something went wrong.";
    }

    header("Location: manage_doctors.php");
    exit();
}

/* ===========================
   HANDLE CREATE
=========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $specialization = trim($_POST['specialization']);

    if (empty($name) || empty($email) || empty($password) || empty($specialization)) {
        $_SESSION['error'] = "All fields are required.";
    } else {

        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $_SESSION['error'] = "Email already exists.";
        } else {

            $pdo->beginTransaction();

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (?, ?, ?, 'doctor')
            ");
            $stmt->execute([$name, $email, $hashedPassword]);

            $userId = $pdo->lastInsertId();

            $stmt2 = $pdo->prepare("
                INSERT INTO doctors (user_id, specialization)
                VALUES (?, ?)
            ");
            $stmt2->execute([$userId, $specialization]);

            $pdo->commit();
            $_SESSION['success'] = "Doctor added successfully.";
        }
    }

    header("Location: manage_doctors.php");
    exit();
}

/* ===========================
   FETCH DOCTORS
=========================== */
$doctors = $pdo->query("
    SELECT users.id, users.name, users.email, doctors.specialization
    FROM users
    JOIN doctors ON users.id = doctors.user_id
    WHERE users.role = 'doctor'
    ORDER BY users.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Doctors - MedBook</title>
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>

<div class="wrapper">

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar js-sidebar">
        <div class="sidebar-content js-simplebar">
            <a class="sidebar-brand" href="dashboard.php">
                <span class="align-middle fw-bold">MedBook Admin</span>
            </a>

            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a class="sidebar-link" href="dashboard.php">
                        <i data-feather="home"></i> Dashboard
                    </a>
                </li>
                <li class="sidebar-item active">
                    <a class="sidebar-link" href="manage_doctors.php">
                        <i data-feather="user-check"></i> Manage Doctors
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="../auth/logout.php">
                        <i data-feather="log-out"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main">
        <nav class="navbar navbar-light navbar-bg">
            <div class="container-fluid">
                <h4 class="mb-0 fw-semibold">Manage Doctors</h4>
            </div>
        </nav>

        <main class="content">
            <div class="container-fluid p-4">

                <!-- Flash Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Add Doctor Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Add New Doctor</h5>
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="password" name="password" class="form-control" placeholder="Temporary Password" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="specialization" class="form-control" placeholder="Specialization" required>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i data-feather="plus"></i> Add Doctor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Doctors Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-3">Doctor Directory</h5>

                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Specialization</th>
                                    <th width="120">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctors as $doctor): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($doctor['name']); ?></td>
                                        <td><?= htmlspecialchars($doctor['email']); ?></td>
                                        <td><?= htmlspecialchars($doctor['specialization']); ?></td>
                                        <td>
                                            <a href="?delete=<?= $doctor['id']; ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to remove this doctor?');">
                                                <i data-feather="trash-2"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script src="../assets/js/app.js"></script>
<script>
    feather.replace();
</script>
</body>
</html>