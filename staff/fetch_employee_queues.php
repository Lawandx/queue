<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'employee') {
    header("Location: ../login.php");
    exit();
}

header('Content-Type: application/json');

if (!isset($_SESSION['employee_id'])) {
    echo json_encode([]);
    exit();
}

$employee_id = $_SESSION['employee_id'];

// Fetch current queues for the logged-in employee
$stmt = $conn->prepare("SELECT q.queue_id, q.daily_queue_number, s.full_name, sv.service_name, q.queue_time, q.status
                        FROM queue q
                        JOIN students s ON q.student_id = s.student_id
                        JOIN services sv ON q.service_id = sv.service_id
                        WHERE q.employee_id = ? AND q.status IN ('Waiting', 'Called')");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$current_queues = [];
while ($row = $result->fetch_assoc()) {
    $current_queues[] = $row;
}

echo json_encode($current_queues);
?>
