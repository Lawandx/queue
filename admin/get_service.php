<?php
session_start();
include '../db_connect.php';

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// รับค่าจาก AJAX
$service_id = $_POST['service_id'];

// ดึงข้อมูลบริการ
$stmt = $conn->prepare("SELECT * FROM services WHERE service_id = ?");
$stmt->bind_param("s", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $service = $result->fetch_assoc();
    echo json_encode($service);
} else {
    echo json_encode(['error' => 'Service not found']);
}
?>
