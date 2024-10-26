<?php
header('Content-Type: application/json');
include 'db_connect.php';

// ดึงข้อมูลคิวทั้งหมดที่มีสถานะไม่ใช่ 'Received' หรือ 'Not Coming'
$queue_result = $conn->query("SELECT q.queue_id, q.daily_queue_number, s.full_name, srv.service_name, se.employee_name, q.queue_time, q.status 
                              FROM queue q
                              JOIN students s ON q.student_id = s.student_id
                              JOIN services srv ON q.service_id = srv.service_id
                              JOIN serviceemployee se ON srv.service_id = se.service_id
                              WHERE q.status NOT IN ('Received', 'Not Coming')
                              ORDER BY q.queue_time DESC"); // เรียงจากใหม่ไปเก่า

$queues = [];

while ($row = $queue_result->fetch_assoc()) {
    $queues[] = [
        'queue_id' => $row['queue_id'],
        'daily_queue_number' => $row['daily_queue_number'],
        'full_name' => $row['full_name'],
        'service_name' => $row['service_name'],
        'status' => $row['status'],
        'time' => $row['queue_time'] // ฟิลด์ time ยังถูกส่งมา แต่จะไม่แสดงในตาราง
    ];
}

echo json_encode($queues);

$conn->close();
?>
