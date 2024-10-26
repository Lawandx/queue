<?php
session_start();
include '../db_connect.php';

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// รับค่าจาก URL
$service_id = $_GET['service_id'];

// ตรวจสอบว่ามีพนักงานที่ใช้บริการนี้อยู่หรือไม่
$check_employee = $conn->prepare("SELECT * FROM serviceemployee WHERE service_id = ?");
$check_employee->bind_param("s", $service_id);
$check_employee->execute();
$result = $check_employee->get_result();

if ($result->num_rows > 0) {
    // หากมีพนักงานที่ใช้บริการนี้ ไม่สามารถลบได้
    echo "<script>alert('ไม่สามารถลบบริการนี้ได้ เนื่องจากมีพนักงานที่เกี่ยวข้อง'); window.location.href='services.php';</script>";
} else {
    // ลบบริการ
    $stmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
    $stmt->bind_param("s", $service_id);
    if ($stmt->execute()) {
        header("Location: services.php");
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลบบริการ'); window.location.href='services.php';</script>";
    }
}
?>
