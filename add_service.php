<?php
include 'db_connect.php';

$service_id = $_POST['service_id'];
$service_name = $_POST['service_name'];

$conn->query("INSERT INTO services (service_id, service_name) VALUES ('$service_id', '$service_name')");
header('Location: admin_dashboard.php');
?>
