<?php
include 'db_connect.php';

$month = $_POST['month'];
$year = $_POST['year'];

// Fetch queue counts for the selected month and year
$query = "
    SELECT DATE_FORMAT(queue_time, '%d') AS day, COUNT(*) AS count
    FROM queue
    WHERE MONTH(queue_time) = ? AND YEAR(queue_time) = ?
    GROUP BY DATE_FORMAT(queue_time, '%d')
    ORDER BY DATE_FORMAT(queue_time, '%d')
";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $month, $year);
$stmt->execute();
$result = $stmt->get_result();

$data = [
    'labels' => [],
    'values' => []
];

while ($row = $result->fetch_assoc()) {
    $data['labels'][] = $row['day'];
    $data['values'][] = $row['count'];
}

echo json_encode($data);
