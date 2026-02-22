<?php
session_start();
require "../config/database.php";

/* ===========================
   AUTH CHECK
=========================== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

/* ===========================
   GET DOCTOR PROFILE
=========================== */
$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch();

if (!$doctor) {
    die("Doctor profile not found.");
}

$doctorId = $doctor['id'];

/* ===========================
   CSRF TOKEN
=========================== */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ===========================
   HANDLE STATUS UPDATE (POST ONLY)
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $appointmentId = (int) $_POST['appointment_id'];
    $action = $_POST['action'];

    if (in_array($action, ['approved', 'rejected', 'completed'])) {

        $update = $pdo->prepare("
            UPDATE appointments 
            SET status = ? 
            WHERE id = ? AND doctor_id = ?
        ");
        $update->execute([$action, $appointmentId, $doctorId]);

        $_SESSION['success'] = "Appointment status updated.";
    }

    header("Location: dashboard.php");
    exit();
}

/* ===========================
   FETCH APPOINTMENTS
=========================== */
$stmt = $pdo->prepare("
    SELECT appointments.*, users.name AS patient_name
    FROM appointments
    JOIN users ON appointments.patient_id = users.id
    WHERE appointments.doctor_id = ?
    ORDER BY appointment_date DESC, appointment_time DESC
");
$stmt->execute([$doctorId]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   DASHBOARD METRICS
=========================== */
$totalAppointments = count($appointments);
$pendingCount = 0;
$todayCount = 0;

foreach ($appointments as $appt) {
    if ($appt['status'] === 'pending') $pendingCount++;
    if ($appt['appointment_date'] === date('Y-m-d')) $todayCount++;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard - MedBook</title>
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>

<div class="wrapper">

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar js-sidebar">
        <div class="sidebar-content js-simplebar">
            <a class="sidebar-brand" href="#">
                <span class="align-middle fw-bold">MedBook Doctor</span>
            </a>

            <ul class="sidebar-nav">
                <li class="sidebar-item active">
                    <a class="sidebar-link" href="dashboard.php">
                        <i data-feather="home"></i> Dashboard
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
                <h4 class="mb-0 fw-semibold">My Appointments</h4>
                <div>
                    <span class="text-muted">Welcome, </span>
                    <strong><?= htmlspecialchars($_SESSION['name']); ?></strong>
                </div>
            </div>
        </nav>

        <main class="content">
            <div class="container-fluid p-4">

                <!-- Flash Message -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- Metrics -->
                <div class="row g-4 mb-4">

                    <div class="col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Appointments</h6>
                                    <h3 class="fw-bold"><?= $totalAppointments ?></h3>
                                </div>
                                <i data-feather="calendar" class="text-primary"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Pending</h6>
                                    <h3 class="fw-bold"><?= $pendingCount ?></h3>
                                </div>
                                <i data-feather="clock" class="text-warning"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Today</h6>
                                    <h3 class="fw-bold"><?= $todayCount ?></h3>
                                </div>
                                <i data-feather="activity" class="text-success"></i>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Appointments Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <?php if ($appointments): ?>
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th width="220">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($appointment['patient_name']); ?></td>
                                        <td><?= htmlspecialchars($appointment['appointment_date']); ?></td>
                                        <td><?= htmlspecialchars($appointment['appointment_time']); ?></td>
                                        <td>
                                            <span class="badge bg-<?=
                                                $appointment['status'] === 'approved' ? 'success' :
                                                ($appointment['status'] === 'rejected' ? 'danger' :
                                                ($appointment['status'] === 'completed' ? 'primary' : 'warning'))
                                            ?>">
                                                <?= ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($appointment['status'] === 'pending'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?= $appointment['id']; ?>">
                                                    <input type="hidden" name="action" value="approved">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                                    <button class="btn btn-sm btn-success">Approve</button>
                                                </form>

                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?= $appointment['id']; ?>">
                                                    <input type="hidden" name="action" value="rejected">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                                    <button class="btn btn-sm btn-danger">Reject</button>
                                                </form>

                                            <?php elseif ($appointment['status'] === 'approved'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?= $appointment['id']; ?>">
                                                    <input type="hidden" name="action" value="completed">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                                    <button class="btn btn-sm btn-primary">Mark Completed</button>
                                                </form>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No appointments yet.</p>
                        <?php endif; ?>

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