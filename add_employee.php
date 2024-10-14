<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_name = $_POST['employee_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $service_id = $_POST['service_id'];
    $contact_info = $_POST['email'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $conn->prepare("INSERT INTO serviceemployee (employee_name, username, password, service_id, email, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $employee_name, $username, $password, $service_id, $contact_info, $start_date, $end_date);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
        header("Location: admin_dashboard.php");
        exit();
    }
}
?>
