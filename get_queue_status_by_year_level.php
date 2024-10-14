<?php
include 'db_connect.php';

// Get the selected year level from the request
$year_level = isset($_POST['year_level']) ? $_POST['year_level'] : '';

// Fetch queue counts by status for the selected year level
$query = "
    SELECT q.status, COUNT(*) AS count
    FROM queue q
    JOIN students s ON q.student_id = s.student_id
    WHERE s.year_level = ?
    GROUP BY q.status
";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $year_level);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['status']] = (int)$row['count'];
}

// Return data as JSON
echo json_encode($data);
?>
