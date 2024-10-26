<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$employee_id = $_GET['employee_id'];

// ลบพนักงานออกจากฐานข้อมูล
$stmt = $conn->prepare("DELETE FROM serviceemployee WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);

if ($stmt->execute()) {
    header("Location: employees.php");
} else {
    echo "<script>alert('เกิดข้อผิดพลาดในเพิ่มพนักงาน'); window.location.href='employees.php';</script>";
}

$stmt->close();
$conn->close();
?>
