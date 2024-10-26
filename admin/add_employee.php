<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// รับค่าจากฟอร์ม
$employee_id = $_POST['employee_id'];
$service_id = $_POST['service_id'];
$employee_name = $_POST['employee_name'];
$username = $_POST['username'];
$password = $_POST['password'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$email = $_POST['email'];
$more_details = $_POST['more_details'];
$access_level = $_POST['access_level'];

// เข้ารหัสรหัสผ่าน (ถ้าจำเป็น)
// $hashed_password = password_hash($password, PASSWORD_DEFAULT);

// เพิ่มข้อมูลลงฐานข้อมูล
$stmt = $conn->prepare("INSERT INTO serviceemployee (employee_id, service_id, username, password, employee_name, start_date, end_date, email, more_details, access_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssss", $employee_id, $service_id, $username, $password, $employee_name, $start_date, $end_date, $email, $more_details, $access_level);

if ($stmt->execute()) {
    header("Location: employees.php");
} else {
    echo "<script>alert('เกิดข้อผิดพลาดในเพิ่มพนักงาน'); window.location.href='employees.php';</script>";
}

$stmt->close();
$conn->close();
?>
