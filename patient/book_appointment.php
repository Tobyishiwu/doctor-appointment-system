<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$patientId = $_SESSION['user_id'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$doctors = $pdo->query("
    SELECT doctors.id, users.name 
    FROM doctors
    JOIN users ON doctors.user_id = users.id
    ORDER BY users.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $doctorId = (int) $_POST['doctor_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $notes = trim($_POST['notes']);

    if (empty($doctorId) || empty($date) || empty($time)) {
        $_SESSION['error'] = "Please complete all required fields.";
    } elseif ($date < date('Y-m-d')) {
        $_SESSION['error'] = "You cannot book a past date.";
    } else {
        $hour = date("H", strtotime($time));
        if ($hour < 9 || $hour >= 17) {
            $_SESSION['error'] = "Appointments are available between 9AM and 5PM only.";
        } else {
            $checkDoctor = $pdo->prepare("
                SELECT id FROM appointments
                WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status IN ('pending','approved')
            ");
            $checkDoctor->execute([$doctorId, $date, $time]);

            $checkPatient = $pdo->prepare("
                SELECT id FROM appointments
                WHERE patient_id = ? AND appointment_date = ? AND status IN ('pending','approved')
            ");
            $checkPatient->execute([$patientId, $date]);

            if ($checkDoctor->rowCount() > 0) {
                $_SESSION['error'] = "This time slot is already taken.";
            } elseif ($checkPatient->rowCount() > 0) {
                $_SESSION['error'] = "You already have an appointment on this date.";
            } else {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, notes) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$patientId, $doctorId, $date, $time, $notes]);
                    $pdo->commit();
                    $_SESSION['success'] = true;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['error'] = "Something went wrong. Please try again.";
                }
            }
        }
    }
    header("Location: book_appointment.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - MedBook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
    <style>
        :root {
            --med-primary: #0d6efd;
            --med-secondary: #f8f9fa;
            --med-success: #198754;
            --sidebar-width: 260px;
        }

        body {
            background-color: #f4f7f6;
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* 2026 Enhanced Sidebar */
        #sidebar {
            background: linear-gradient(180deg, #212529 0%, #000 100%);
            min-height: 100vh;
            width: var(--sidebar-width);
            transition: all 0.3s;
        }

        .sidebar-link {
            color: rgba(255,255,255,0.75);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: 8px;
            margin: 4px 15px;
            transition: 0.2s;
        }

        .sidebar-link:hover, .sidebar-item.active .sidebar-link {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }

        .sidebar-link i { margin-right: 12px; }

        /* Modernized Content Card */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            border-color: var(--med-primary);
        }

        .btn-confirm {
            padding: 12px 30px;
            font-weight: 700;
            border-radius: 10px;
            letter-spacing: 0.5px;
            transition: 0.3s;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        /* Success Step Animation */
        .success-circle {
            width: 80px;
            height: 80px;
            background: rgba(25, 135, 84, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <nav id="sidebar">
        <div class="p-4 mb-3">
            <h3 class="text-white fw-bold mb-0">MedBook<span class="text-primary">.</span></h3>
        </div>
        <ul class="list-unstyled">
            <li class="sidebar-item">
                <a class="sidebar-link" href="dashboard.php">
                    <i data-feather="grid"></i> Dashboard
                </a>
            </li>
            <li class="sidebar-item active">
                <a class="sidebar-link" href="book_appointment.php">
                    <i data-feather="calendar"></i> Bookings
                </a>
            </li>
            <li class="sidebar-item mt-5">
                <a class="sidebar-link text-danger" href="../auth/logout.php">
                    <i data-feather="log-out"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <div class="flex-grow-1">
        <nav class="navbar navbar-expand navbar-light bg-white border-bottom px-4 py-3">
            <h4 class="fw-bold mb-0">Schedule Appointment</h4>
        </nav>

        <main class="p-4">
            <div class="container-fluid">

            <?php if (isset($_SESSION['success'])): ?>
                <div class="card p-5 text-center">
                    <div class="success-circle mb-4">
                        <i data-feather="check" class="text-success" style="width:40px; height:40px;"></i>
                    </div>
                    <h2 class="fw-bold text-dark">All Set!</h2>
                    <p class="text-muted fs-5">Your appointment has been successfully added to our schedule.</p>
                    <div class="mt-4">
                        <a href="dashboard.php" class="btn btn-primary px-5 py-3 fw-bold rounded-pill">View My Schedule</a>
                    </div>
                </div>
                <script>
                    setTimeout(() => { window.location.href = "dashboard.php"; }, 3500);
                </script>
                <?php unset($_SESSION['success']); ?>

            <?php else: ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center">
                        <i data-feather="alert-circle" class="me-2"></i>
                        <?= $_SESSION['error']; ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body p-4 p-md-5">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">1. Choose Specialist</label>
                                    <select name="doctor_id" class="form-select" required>
                                        <option value="">Select a Professional</option>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?= $doctor['id']; ?>">Dr. <?= htmlspecialchars($doctor['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">2. Pick a Date</label>
                                    <input type="date" name="appointment_date" min="<?= date('Y-m-d'); ?>" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">3. Preferred Time</label>
                                    <select name="appointment_time" class="form-select" required>
                                        <option value="">Select Time Slot</option>
                                        <?php
                                        for ($h = 9; $h < 17; $h++) {
                                            $timeVal = str_pad($h, 2, "0", STR_PAD_LEFT);
                                            echo "<option value='{$timeVal}:00'>{$timeVal}:00</option>";
                                            echo "<option value='{$timeVal}:30'>{$timeVal}:30</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">4. Additional Notes</label>
                                    <textarea name="notes" rows="1" class="form-control" placeholder="Briefly describe your concern..."></textarea>
                                </div>
                            </div>

                            <div class="mt-5 border-top pt-4 text-end">
                                <button type="submit" class="btn btn-primary btn-confirm shadow">
                                    Confirm Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            </div>
        </main>
    </div>
</div>

<script src="https://unpkg.com/feather-icons"></script>
<script>
    feather.replace();
</script>

</body>
const doctorSelect = document.querySelector('select[name="doctor_id"]');
const dateInput = document.querySelector('input[name="appointment_date"]');
const timeSelect = document.querySelector('select[name="appointment_time"]');

async function updateAvailableSlots() {
    const doctorId = doctorSelect.value;
    const date = dateInput.value;

    if (!doctorId || !date) return;

    timeSelect.innerHTML = '<option>Checking slots...</option>';

    try {
        const response = await fetch(`get_available_slots.php?doctor_id=${doctorId}&date=${date}`);
        const bookedSlots = await response.json();

        // Clear and rebuild the dropdown
        timeSelect.innerHTML = '<option value="">Select Time Slot</option>';
        
        for (let h = 9; h < 17; h++) {
            const times = [`${h.toString().padStart(2, '0')}:00`, `${h.toString().padStart(2, '0')}:30` ];
            
            times.forEach(t => {
                const isBooked = bookedSlots.includes(t);
                const option = document.createElement('option');
                option.value = t;
                option.textContent = isBooked ? `${t} (Booked)` : t;
                option.disabled = isBooked;
                timeSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error("Error fetching slots:", error);
    }
}

// Trigger when doctor or date changes
[doctorSelect, dateInput].forEach(el => el.addEventListener('change', updateAvailableSlots));
</html>