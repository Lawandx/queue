<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

$employee_id = $_POST['employee_id'];

$stmt = $conn->prepare("SELECT * FROM serviceemployee WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
    echo json_encode($employee);
} else {
    echo json_encode(['error' => 'Employee not found']);
}

$stmt->close();
$conn->close();
?>
