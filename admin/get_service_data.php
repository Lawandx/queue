<?php
include 'db_connect.php';

if (isset($_POST['service_id'])) {
    $service_id = $_POST['service_id'];
    $service = $conn->query("SELECT * FROM services WHERE service_id = '$service_id'")->fetch_assoc();
    echo json_encode($service);
}
?>
