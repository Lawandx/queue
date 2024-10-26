<?php
// daily_report.php

session_start();
include '../db_connect.php';

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}


$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Prepare the SQL query using prepared statements for service counts
$stmt = $conn->prepare("
    SELECT srv.service_name, COUNT(q.queue_id) AS total_queues
    FROM queue q
    JOIN services srv ON q.service_id = srv.service_id
    WHERE DATE(q.queue_time) = ?
    GROUP BY srv.service_name
    ORDER BY total_queues DESC
");
$stmt->bind_param("s", $date);
$stmt->execute();
$daily_service_counts = $stmt->get_result();

// Prepare the SQL query for status counts (RECEIVED, NOT COMING)
$status_stmt = $conn->prepare("
    SELECT status, COUNT(queue_id) AS count 
    FROM queue 
    WHERE DATE(queue_time) = ?
    GROUP BY status
");
$status_stmt->bind_param("s", $date);
$status_stmt->execute();
$status_counts = $status_stmt->get_result();

$status_summary = [];
while ($row = $status_counts->fetch_assoc()) {
    $status_summary[$row['status']] = $row['count'];
}

// Prepare the SQL query for student counts by field of study
$student_stmt = $conn->prepare("
    SELECT s.field_of_study, COUNT(q.queue_id) AS total_students
    FROM queue q
    JOIN students s ON q.student_id = s.student_id
    WHERE DATE(q.queue_time) = ?
    GROUP BY s.field_of_study
    ORDER BY total_students DESC
");
$student_stmt->bind_param("s", $date);
$student_stmt->execute();
$student_counts = $student_stmt->get_result();

// Calculate total queues
$total_queues_stmt = $conn->prepare("
    SELECT COUNT(queue_id) AS total 
    FROM queue 
    WHERE DATE(queue_time) = ?
");
$total_queues_stmt->bind_param("s", $date);
$total_queues_stmt->execute();
$total_queues = $total_queues_stmt->get_result()->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานประจำวัน - <?php echo htmlspecialchars($date); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            font-family: 'TH SarabunPSK', sans-serif;
        }

        h1, h3 {
            text-align: center;
           
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .table {
            margin-top: 20px;
        }

        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }

        #download-pdf {
            float: right;
            margin-bottom: 20px;
        }

        @media print {
            #download-pdf, .btn-secondary {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-5" id="report-content">
        <h1>รายงานสรุปประจำวัน</h1>
        <h3>วันที่: <?php echo htmlspecialchars($date); ?></h3>

        <h4 class="mt-4">สรุปคิว</h4>
        <ul>
            <li>จำนวนคิวทั้งหมด: <?php echo htmlspecialchars($total_queues); ?> คิว</li>
            <li>ได้รับบริการแล้ว: <?php echo htmlspecialchars(isset($status_summary['Received']) ? $status_summary['Received'] : 0); ?> คน</li>
            <li>ไม่ได้มา: <?php echo htmlspecialchars(isset($status_summary['Not Coming']) ? $status_summary['Not Coming'] : 0); ?> คน</li>
        </ul>

        <h4 class="mt-4">จำนวนคิวตามบริการ</h4>
        <table class="table table-striped mt-2">
            <thead class="table-dark">
                <tr>
                    <th>ชื่อบริการ</th>
                    <th>จำนวนคิว</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($service = $daily_service_counts->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                        <td><?php echo htmlspecialchars($service['total_queues']); ?> คิว</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h4 class="mt-4">นิสิตที่มาใช้บริการตามสาขา</h4>
        <table class="table table-striped mt-2">
            <thead class="table-dark">
                <tr>
                    <th>สาขาวิชา</th>
                    <th>จำนวนนิสิต</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $student_counts->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['field_of_study']); ?></td>
                        <td><?php echo htmlspecialchars($student['total_students']); ?> คน</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn btn-secondary mt-3">กลับไปที่แดชบอร์ด</a>
        <a href="#" id="download-pdf" class="btn btn-primary mt-3">ดาวน์โหลด PDF</a>
    </div>

    <script>
        document.getElementById('download-pdf').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            const fileName = 'รายงานประจำวัน_<?php echo htmlspecialchars($date); ?>.pdf';

            html2canvas(document.getElementById('report-content'), {
                scale: 2
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const imgWidth = 210; // ความกว้างของ PDF ในหน่วยมิลลิเมตร
                const pageHeight = 295; // ความสูงของหน้า PDF ในหน่วยมิลลิเมตร
                const imgHeight = canvas.height * imgWidth / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    doc.addPage();
                    doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                doc.save(fileName);
            }).catch(error => {
                console.error("เกิดข้อผิดพลาดในการสร้าง PDF: ", error);
            });
        });
    </script>

</body>

</html>
