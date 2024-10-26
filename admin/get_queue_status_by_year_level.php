<?php
session_start();
include '../db_connect.php';

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    // ส่งกลับข้อผิดพลาดในรูปแบบ JSON
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// รับค่าที่ส่งมาจาก AJAX
$year_level = $_POST['year_level'];

// ตรวจสอบว่ามีการส่งค่า year_level มาหรือไม่
if (!isset($year_level)) {
    echo json_encode(['error' => 'Year level not specified']);
    exit();
}

// ดึงข้อมูลสถานะคิวตามชั้นปี
$sql = "
    SELECT status, COUNT(*) as count
    FROM queue q
    JOIN students s ON q.student_id = s.student_id
    WHERE s.year_level = ?
    GROUP BY status
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $year_level);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['status']] = (int)$row['count'];
}

// ส่งข้อมูลกลับในรูปแบบ JSON
echo json_encode($data);
