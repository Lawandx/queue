<?php
include 'db_connect.php';

$service_id = $_POST['service_id'];
$service_name = $_POST['service_name'];

$conn->query("UPDATE services SET service_name='$service_name' WHERE service_id='$service_id'");
header('Location: admin_dashboard.php');
?>
