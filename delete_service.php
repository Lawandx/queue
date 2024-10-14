<?php
include 'db_connect.php';

$service_id = $_GET['service_id'];

$conn->query("DELETE FROM services WHERE service_id='$service_id'");
header('Location: admin_dashboard.php');
?>
