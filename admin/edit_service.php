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

// อัปเดตข้อมูลบริการ
$stmt = $conn->prepare("UPDATE services SET service_name = ? WHERE service_id = ?");
$stmt->bind_param("ss", $service_name, $service_id);

if ($stmt->execute()) {
    header("Location: services.php");
} else {
    echo "<script>alert('เกิดข้อผิดพลาดในการแก้ไขข้อมูลบริการ'); window.location.href='services.php';</script>";
}
?>
