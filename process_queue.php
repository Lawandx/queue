<?php
include 'db_connect.php';

// Response array
$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $service_id = $_POST['service_id'];

    // Check if student_id exists
    $student_query = $conn->prepare("SELECT full_name FROM students WHERE student_id = ?");
    $student_query->bind_param("s", $student_id);
    $student_query->execute();
    $student_result = $student_query->get_result();

    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        $full_name = $student['full_name'];

        // Get employee_id from service_id
        $service_query = $conn->prepare("SELECT employee_id FROM serviceemployee WHERE service_id = ?");
        $service_query->bind_param("s", $service_id);
        $service_query->execute();
        $service_result = $service_query->get_result();
        $service = $service_result->fetch_assoc();
        $employee_id = $service['employee_id'];

        // Generate daily queue number
        $today = date('Y-m-d');
        $queue_number_query = $conn->prepare("SELECT COALESCE(MAX(daily_queue_number), 0) + 1 AS next_queue_number FROM queue WHERE DATE(queue_time) = ?");
        $queue_number_query->bind_param("s", $today);
        $queue_number_query->execute();
        $queue_number_result = $queue_number_query->get_result();
        $queue_number = $queue_number_result->fetch_assoc()['next_queue_number'];

        // Insert into queue table
        $status = "Waiting";
        $queue_time = date("Y-m-d H:i:s"); // Current timestamp

        $insert_query = $conn->prepare("INSERT INTO queue (student_id, service_id, employee_id, daily_queue_number, queue_time, status) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_query->bind_param("ssssss", $student_id, $service_id, $employee_id, $queue_number, $queue_time, $status);

        if ($insert_query->execute()) {
            $response['type'] = 'success';
            $response['message'] = 'Queue record added successfully.';
            $response['queue_number'] = $queue_number;
        } else {
            $response['type'] = 'danger';
            $response['message'] = 'Error: ' . $insert_query->error;
        }
    } else {
        $response['type'] = 'warning';
        $response['message'] = 'Student ID not found.';
    }

    $conn->close();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
