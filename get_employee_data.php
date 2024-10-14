<?php
include 'db_connect.php';

if (isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];

    $stmt = $conn->prepare("SELECT * FROM serviceemployee WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();

    echo json_encode($employee);
}
?>
