<?php
session_start();
include '../db_connect.php';

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    // ส่งกลับข้อผิดพลาดในรูปแบบ JSON
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// รับค่าปีที่ส่งมาจาก AJAX
$year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');

// ดึงข้อมูลจำนวนคิวในแต่ละเดือนของปีที่เลือก
$sql = "
    SELECT MONTH(queue_time) AS month, COUNT(*) AS count 
    FROM queue 
    WHERE YEAR(queue_time) = ?
    GROUP BY MONTH(queue_time)
    ORDER BY MONTH(queue_time)
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $year);
$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$values = [];

$months = [
    1 => 'มกราคม',
    2 => 'กุมภาพันธ์',
    3 => 'มีนาคม',
    4 => 'เมษายน',
    5 => 'พฤษภาคม',
    6 => 'มิถุนายน',
    7 => 'กรกฎาคม',
    8 => 'สิงหาคม',
    9 => 'กันยายน',
    10 => 'ตุลาคม',
    11 => 'พฤศจิกายน',
    12 => 'ธันวาคม'
];

// เตรียมข้อมูลสำหรับกราฟ
while ($row = $result->fetch_assoc()) {
    $monthNum = intval($row['month']);
    $labels[] = $months[$monthNum];
    $values[] = intval($row['count']);
}

// ส่งข้อมูลกลับในรูปแบบ JSON
echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
?>
