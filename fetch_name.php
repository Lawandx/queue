<?php
include 'db_connect.php';

$student_id = $_GET['student_id'];

// Prepare and execute query
$query = $conn->prepare("SELECT full_name FROM students WHERE student_id = ?");
$query->bind_param("s", $student_id);
$query->execute();
$result = $query->get_result();

$response = array();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $response['success'] = true;
    $response['full_name'] = $student['full_name'];
} else {
    $response['success'] = false;
    $response['full_name'] = "No data found";
}

echo json_encode($response);

$conn->close();
?>
