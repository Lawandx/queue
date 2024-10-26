<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'employee') {
    header("Location: ../login.php");
    exit();
}
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['queue_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
    exit();
}

$queue_id = $data['queue_id'];
$new_status = $data['status'];

// Validate status
$valid_statuses = ['Called', 'Not Coming', 'Received'];
if (!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit();
}

// Update queue status
$stmt = $conn->prepare("UPDATE queue SET status = ? WHERE queue_id = ?");
$stmt->bind_param("si", $new_status, $queue_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
}

$stmt->close();
$conn->close();
