<?php
session_start();
include '../db_connect.php';

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// รับค่าเดือนและปีที่ส่งมาจาก AJAX
$month = isset($_POST['month']) ? intval($_POST['month']) : date('n');
$year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');

// ดึงข้อมูลจำนวนคิวในแต่ละวันของเดือนที่เลือก
$sql = "
    SELECT DAY(queue_time) AS day, COUNT(*) AS count 
    FROM queue 
    WHERE YEAR(queue_time) = ? AND MONTH(queue_time) = ?
    GROUP BY DAY(queue_time)
    ORDER BY DAY(queue_time)
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $year, $month);
$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$values = [];

// เตรียมข้อมูลสำหรับกราฟ
while ($row = $result->fetch_assoc()) {
    $day = intval($row['day']);
    $labels[] = $day;
    $values[] = intval($row['count']);
}

// ส่งข้อมูลกลับในรูปแบบ JSON
echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
?>
