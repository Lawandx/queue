<?php
include 'db_connect.php';

$limit = isset($_GET['show_all']) && $_GET['show_all'] == 'true' ? '' : 'LIMIT 5';

// Fetch queue data
$queue_result = $conn->query("SELECT q.queue_id, q.daily_queue_number, s.full_name, srv.service_name, se.employee_name, q.queue_time, q.status 
                              FROM queue q
                              JOIN students s ON q.student_id = s.student_id
                              JOIN services srv ON q.service_id = srv.service_id
                              JOIN serviceemployee se ON srv.service_id = se.service_id
                              WHERE q.status NOT IN ('Received', 'Not Coming')
                              ORDER BY q.queue_time DESC $limit");

$queues = [];

while ($row = $queue_result->fetch_assoc()) {
    $queues[] = $row;
}

echo json_encode($queues);

$conn->close();
