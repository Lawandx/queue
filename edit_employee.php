<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $employee_name = $_POST['employee_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $service_id = $_POST['service_id'];
    $email = $_POST['email'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $more_details = $_POST['more_details'];

    $stmt = $conn->prepare("UPDATE serviceemployee SET employee_name = ?, username = ?, password = ?, service_id = ?, email = ?, start_date = ?, end_date = ?, more_details = ? WHERE employee_id = ?");
    $stmt->bind_param("ssssssssi", $employee_name, $username, $password, $service_id, $email, $start_date, $end_date,  $more_details, $employee_id);

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
