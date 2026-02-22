<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$patientId = $_SESSION['user_id'];

/* ===========================
   FETCH APPOINTMENTS
=========================== */
$stmt = $pdo->prepare("
    SELECT appointments.*, users.name AS doctor_name
    FROM appointments
    JOIN doctors ON appointments.doctor_id = doctors.id
    JOIN users ON doctors.user_id = users.id
    WHERE appointments.patient_id = ?
    ORDER BY appointment_date DESC, appointment_time DESC
");
$stmt->execute([$patientId]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   METRICS
=========================== */
$totalAppointments = count($appointments);
$pendingCount = 0;
$upcomingCount = 0;
$today = date('Y-m-d');

foreach ($appointments as $appt) {
    if ($appt['status'] === 'pending') $pendingCount++;
    if ($appt['appointment_date'] >= $today && $appt['status'] === 'approved') {
        $upcomingCount++;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Dashboard - MedBook</title>
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>

<div class="wrapper">

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar js-sidebar">
        <div class="sidebar-content js-simplebar">
            <a class="sidebar-brand" href="#">
                <span class="align-middle fw-bold">MedBook</span>
            </a>

            <ul class="sidebar-nav">
                <li class="sidebar-item active">
                    <a class="sidebar-link" href="dashboard.php">
                        <i data-feather="home"></i> Dashboard
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="book_appointment.php">
                        <i data-feather="plus-circle"></i> Book Appointment
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

                <!-- Metrics -->
                <div class="row g-4 mb-4">

                    <div class="col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total</h6>
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
                                    <h6 class="text-muted mb-1">Upcoming</h6>
                                    <h3 class="fw-bold"><?= $upcomingCount ?></h3>
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
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($appointment['doctor_name']); ?></td>
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
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i data-feather="calendar" class="text-muted mb-3"></i>
                                <h5 class="text-muted">No appointments yet</h5>
                                <p class="text-muted small">
                                    Book your first consultation to get started.
                                </p>
                                <a href="book_appointment.php" class="btn btn-primary mt-2">
                                    Book Appointment
                                </a>
                            </div>
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