<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Dashboard stats
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pendingAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();
$todayAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>MedBook Admin Dashboard</title>
	<link href="../assets/css/app.css" rel="stylesheet">
</head>

<body>
<div class="wrapper">

	<!-- Sidebar -->
	<nav id="sidebar" class="sidebar js-sidebar">
		<div class="sidebar-content js-simplebar">
			<a class="sidebar-brand" href="#">
				<span class="align-middle fw-bold">MedBook Admin</span>
			</a>

			<ul class="sidebar-nav">
				<li class="sidebar-item active">
					<a class="sidebar-link" href="dashboard.php">
						<i data-feather="home"></i>
						<span class="align-middle">Dashboard</span>
					</a>
				</li>

				<li class="sidebar-item">
					<a class="sidebar-link" href="manage_doctors.php">
						<i data-feather="users"></i>
						<span class="align-middle">Manage Doctors</span>
					</a>
				</li>

				<li class="sidebar-item">
					<a class="sidebar-link d-flex justify-content-between align-items-center" href="appointments.php">
						<span>
							<i data-feather="calendar"></i>
							Appointments
						</span>
						<?php if ($pendingAppointments > 0): ?>
							<span class="badge bg-warning text-dark">
								<?= $pendingAppointments ?>
							</span>
						<?php endif; ?>
					</a>
				</li>

				<li class="sidebar-item">
					<a class="sidebar-link" href="../auth/logout.php">
						<i data-feather="log-out"></i>
						<span class="align-middle">Logout</span>
					</a>
				</li>
			</ul>
		</div>
	</nav>

	<!-- Main -->
	<div class="main">

		<!-- Navbar -->
		<nav class="navbar navbar-expand navbar-light navbar-bg">
			<div class="container-fluid">
				<h4 class="mb-0 fw-semibold">Dashboard Overview</h4>
				<div>
					<span class="text-muted">Welcome, </span>
					<strong><?= htmlspecialchars($_SESSION['name']); ?></strong>
				</div>
			</div>
		</nav>

		<main class="content">
			<div class="container-fluid p-4">

				<div class="row g-4">

					<!-- Doctors -->
					<div class="col-md-4 col-xl-3">
						<div class="card shadow-sm border-0">
							<div class="card-body d-flex justify-content-between align-items-center">
								<div>
									<h6 class="text-muted mb-1">Doctors</h6>
									<h3 class="fw-bold"><?= $totalDoctors ?></h3>
								</div>
								<div class="stat bg-primary bg-opacity-10 p-3 rounded-circle">
									<i data-feather="user-check" class="text-primary"></i>
								</div>
							</div>
						</div>
					</div>

					<!-- Patients -->
					<div class="col-md-4 col-xl-3">
						<div class="card shadow-sm border-0">
							<div class="card-body d-flex justify-content-between align-items-center">
								<div>
									<h6 class="text-muted mb-1">Patients</h6>
									<h3 class="fw-bold"><?= $totalPatients ?></h3>
								</div>
								<div class="stat bg-success bg-opacity-10 p-3 rounded-circle">
									<i data-feather="users" class="text-success"></i>
								</div>
							</div>
						</div>
					</div>

					<!-- Total Appointments -->
					<div class="col-md-4 col-xl-3">
						<div class="card shadow-sm border-0">
							<div class="card-body d-flex justify-content-between align-items-center">
								<div>
									<h6 class="text-muted mb-1">Total Appointments</h6>
									<h3 class="fw-bold"><?= $totalAppointments ?></h3>
								</div>
								<div class="stat bg-info bg-opacity-10 p-3 rounded-circle">
									<i data-feather="calendar" class="text-info"></i>
								</div>
							</div>
						</div>
					</div>

					<!-- Today -->
					<div class="col-md-6 col-xl-3">
						<div class="card shadow-sm border-0">
							<div class="card-body d-flex justify-content-between align-items-center">
								<div>
									<h6 class="text-muted mb-1">Today's Appointments</h6>
									<h3 class="fw-bold"><?= $todayAppointments ?></h3>
								</div>
								<div class="stat bg-warning bg-opacity-10 p-3 rounded-circle">
									<i data-feather="clock" class="text-warning"></i>
								</div>
							</div>
						</div>
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