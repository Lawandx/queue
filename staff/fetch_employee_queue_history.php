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

// Fetch queue history for the logged-in employee
$stmt = $conn->prepare("SELECT q.daily_queue_number, s.full_name, sv.service_name, q.queue_time, q.status
                        FROM queue q
                        JOIN students s ON q.student_id = s.student_id
                        JOIN services sv ON q.service_id = sv.service_id
                        WHERE q.employee_id = ?
                        ORDER BY q.queue_time DESC");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$queue_history = [];
while ($row = $result->fetch_assoc()) {
    $queue_history[] = $row;
}

echo json_encode($queue_history);
?>
