<?php 
// get_yearly_monthly_data.php
include 'db_connect.php';

$year = $_POST['year'];

$queues = $conn->query("
    SELECT DATE_FORMAT(queue_time, '%Y-%m') AS month, COUNT(*) AS count 
    FROM queue 
    WHERE YEAR(queue_time) = '$year'
    GROUP BY DATE_FORMAT(queue_time, '%Y-%m')
    ORDER BY DATE_FORMAT(queue_time, '%Y-%m')
");

$data = ['labels' => [], 'values' => []];
while ($row = $queues->fetch_assoc()) {
    $data['labels'][] = $row['month'];
    $data['values'][] = $row['count'];
}

echo json_encode($data);
