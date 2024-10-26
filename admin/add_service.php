<?php
session_start();
include '../db_connect.php';

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// รับค่าจากฟอร์ม
$service_id = $_POST['service_id'];
$service_name = $_POST['service_name'];

// ตรวจสอบว่ามีรหัสบริการนี้อยู่แล้วหรือไม่
$check_service = $conn->prepare("SELECT * FROM services WHERE service_id = ?");
$check_service->bind_param("s", $service_id);
$check_service->execute();
$result = $check_service->get_result();

if ($result->num_rows > 0) {
    // หากมีรหัสบริการนี้อยู่แล้ว
    echo "<script>alert('รหัสบริการนี้มีอยู่แล้วในระบบ'); window.location.href='services.php';</script>";
} else {
    // เพิ่มบริการใหม่
    $stmt = $conn->prepare("INSERT INTO services (service_id, service_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $service_id, $service_name);
    if ($stmt->execute()) {
        header("Location: services.php");
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการเพิ่มบริการ'); window.location.href='services.php';</script>";
    }
}
?>
