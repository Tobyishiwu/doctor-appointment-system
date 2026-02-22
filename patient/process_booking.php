<?php
header('Content-Type: application/json');
session_start();
require "../../config/database.php";

// 1. Strict Authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// 2. CSRF & Validation
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Security token expired. Refresh page.']);
    exit();
}

$patientId = $_SESSION['user_id'];
$doctorId  = (int)($_POST['doctor_id'] ?? 0);
$date      = $_POST['appointment_date'] ?? '';
$time      = $_POST['appointment_time'] ?? '';
$notes     = htmlspecialchars(trim($_POST['notes'] ?? ''));

try {
    $pdo->beginTransaction();

    // 3. Prevent collisions (Final check before commit)
    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? FOR UPDATE");
    $stmt->execute([$doctorId, $date, $time]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception("This slot was just booked by someone else.");
    }

    // 4. Insert
    $insert = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, notes, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $insert->execute([$patientId, $doctorId, $date, $time, $notes]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}