<?php
session_start();
include 'db_connect.php';

if (isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];

    $stmt = $conn->prepare("DELETE FROM serviceemployee WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
        header("Location: admin_dashboard.php");
        exit();
    }
} else {
    $_SESSION['error'] = "No employee ID provided";
    header("Location: admin_dashboard.php");
    exit();
}
