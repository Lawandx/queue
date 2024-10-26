<?php
session_start();
include '../db_connect.php';

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}


$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// เตรียมคำสั่ง SQL สำหรับการนับจำนวนคิวรายเดือนตามบริการ
$monthly_service_counts = $conn->prepare("
    SELECT srv.service_name, COUNT(q.queue_id) AS total_queues, 
           SUM(CASE WHEN q.status = 'Received' THEN 1 ELSE 0 END) AS received,
           SUM(CASE WHEN q.status = 'Not Coming' THEN 1 ELSE 0 END) AS not_coming
    FROM queue q
    JOIN services srv ON q.service_id = srv.service_id
    WHERE MONTH(q.queue_time) = ? AND YEAR(q.queue_time) = ?
    GROUP BY srv.service_name
    ORDER BY total_queues DESC
");
$monthly_service_counts->bind_param("ss", $month, $year);
$monthly_service_counts->execute();
$service_counts = $monthly_service_counts->get_result();

// เตรียมคำสั่ง SQL สำหรับการนับจำนวนนักเรียนตามสาขาวิชาและชั้นปี
$student_counts = $conn->prepare("
    SELECT s.field_of_study, s.year_level, COUNT(q.queue_id) AS total_students
    FROM queue q
    JOIN students s ON q.student_id = s.student_id
    WHERE MONTH(q.queue_time) = ? AND YEAR(q.queue_time) = ?
    GROUP BY s.field_of_study, s.year_level
    ORDER BY total_students DESC
");
$student_counts->bind_param("ss", $month, $year);
$student_counts->execute();
$students = $student_counts->get_result();

// คำนวณค่าเฉลี่ยของจำนวนคิวที่ให้บริการต่อวัน
$avg_queues_per_day = $conn->prepare("
    SELECT AVG(daily_queues) AS avg_queues
    FROM (
        SELECT COUNT(queue_id) AS daily_queues 
        FROM queue 
        WHERE MONTH(queue_time) = ? AND YEAR(queue_time) = ? 
        GROUP BY DATE(queue_time)
    ) AS daily_counts
");
$avg_queues_per_day->bind_param("ss", $month, $year);
$avg_queues_per_day->execute();
$avg_queues = $avg_queues_per_day->get_result()->fetch_assoc()['avg_queues'];

// ดึงข้อมูลจำนวนคิวรายชั่วโมงที่ได้รับบริการ
$hourly_service_counts = $conn->prepare("
    SELECT HOUR(queue_time) AS hour, COUNT(queue_id) AS count
    FROM queue
    WHERE MONTH(queue_time) = ? AND YEAR(queue_time) = ? AND status = 'Received'
    GROUP BY HOUR(queue_time)
    ORDER BY hour
");
$hourly_service_counts->bind_param("ss", $month, $year);
$hourly_service_counts->execute();
$hourly_counts = $hourly_service_counts->get_result();

// สรุปจำนวนคิวแยกตามวันในเดือน
$daily_counts = $conn->prepare("
    SELECT DATE(queue_time) AS day, 
           COUNT(queue_id) AS total_queues, 
           SUM(CASE WHEN status = 'Received' THEN 1 ELSE 0 END) AS received,
           SUM(CASE WHEN status = 'Not Coming' THEN 1 ELSE 0 END) AS not_coming
    FROM queue
    WHERE MONTH(queue_time) = ? AND YEAR(queue_time) = ?
    GROUP BY DATE(queue_time)
    ORDER BY day
");
$daily_counts->bind_param("ss", $month, $year);
$daily_counts->execute();
$daily_result = $daily_counts->get_result();

// สถิติการให้บริการแยกตามพนักงาน
$employee_counts = $conn->prepare("
    SELECT se.employee_name, COUNT(q.queue_id) AS total_queues, 
           SUM(CASE WHEN q.status = 'Received' THEN 1 ELSE 0 END) AS received,
           SUM(CASE WHEN q.status = 'Not Coming' THEN 1 ELSE 0 END) AS not_coming
    FROM queue q
    JOIN serviceemployee se ON q.employee_id = se.employee_id
    WHERE MONTH(q.queue_time) = ? AND YEAR(q.queue_time) = ?
    GROUP BY se.employee_name
    ORDER BY total_queues DESC
");
$employee_counts->bind_param("ss", $month, $year);
$employee_counts->execute();
$employee_result = $employee_counts->get_result();

// สถิติเปรียบเทียบกับเดือนก่อนหน้า
$prev_month = $month - 1;
$prev_year = $year;
if ($month == 1) {
    $prev_month = 12;
    $prev_year = $year - 1;
}
$comparison_counts = $conn->prepare("
    SELECT 
        (SELECT COUNT(queue_id) FROM queue WHERE MONTH(queue_time) = ? AND YEAR(queue_time) = ?) AS current_month_count,
        (SELECT COUNT(queue_id) FROM queue WHERE MONTH(queue_time) = ? AND YEAR(queue_time) = ?) AS prev_month_count
");
$comparison_counts->bind_param("ssss", $month, $year, $prev_month, $prev_year);
$comparison_counts->execute();
$comparison_result = $comparison_counts->get_result()->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปรายเดือน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2pdf.js/dist/html2pdf.bundle.min.js"></script>
    <style>
        .chart-container {
            max-width: 100%;
            margin: 20px 0;
            padding: 5px;
            page-break-inside: avoid;
        }

        canvas {
            max-height: 300px !important;
            width: 100% !important;
        }

        table {
            width: 100%;
            margin: 10px 0;
            background-color: #fff;
        }

        th,
        td {
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        tfoot {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .report-section {
            margin-bottom: 20px;
        }

        .text-center {
            text-align: center;
        }

        .btn-back {
            margin-top: 20px;
        }

        .btn-download {
            margin-bottom: 20px;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        @media print {
            .chart-container {
                page-break-before: always;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-3">
        <h1 class="text-center">รายงานสรุปรายเดือน - <?php echo "$month/$year"; ?></h1>

        <div class="btn-container">
            <a href="dashboard.php" class="btn btn-secondary">กลับไปที่แดชบอร์ด</a>
            <button id="downloadPDF" class="btn btn-primary">ดาวน์โหลดรายงานเป็น PDF</button>
        </div>

        <div class="report-section chart-container">
            <h4 class="mt-3">จำนวนคิวรวมต่อบริการ</h4>
            <canvas id="serviceChart"></canvas>
        </div>

        <div class="report-section">
            <h4 class="mt-3">สถิติการให้บริการแยกตามวันในเดือน</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>วันที่</th>
                            <th>ได้รับบริการแล้ว</th>
                            <th>ไม่ได้มา</th>
                            <th>จำนวนคิวรวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_received = 0;
                        $total_not_coming = 0;
                        $total_queues = 0;
                        while ($day = $daily_result->fetch_assoc()) :
                            $total_received += $day['received'];
                            $total_not_coming += $day['not_coming'];
                            $total_queues += $day['total_queues'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($day['day']); ?></td>
                                <td><?php echo htmlspecialchars($day['received']); ?></td>
                                <td><?php echo htmlspecialchars($day['not_coming']); ?></td>
                                <td><?php echo htmlspecialchars($day['total_queues']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>รวมทั้งหมด</td>
                            <td><?php echo $total_received; ?></td>
                            <td><?php echo $total_not_coming; ?></td>
                            <td><?php echo $total_queues; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <br>
        <div class="report-section">
            <h4 class="mt-3">สถิติการให้บริการแยกตามพนักงาน</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ชื่อพนักงาน</th>
                            <th>ได้รับบริการแล้ว</th>
                            <th>ไม่ได้มา</th>
                            <th>จำนวนคิวรวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_received_emp = 0;
                        $total_not_coming_emp = 0;
                        $total_queues_emp = 0;
                        while ($employee = $employee_result->fetch_assoc()) :
                            $total_received_emp += $employee['received'];
                            $total_not_coming_emp += $employee['not_coming'];
                            $total_queues_emp += $employee['total_queues'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($employee['employee_name']); ?></td>
                                <td><?php echo htmlspecialchars($employee['received']); ?></td>
                                <td><?php echo htmlspecialchars($employee['not_coming']); ?></td>
                                <td><?php echo htmlspecialchars($employee['total_queues']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>รวมทั้งหมด</td>
                            <td><?php echo $total_received_emp; ?></td>
                            <td><?php echo $total_not_coming_emp; ?></td>
                            <td><?php echo $total_queues_emp; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="report-section chart-container">
            <h4 class="mt-3">เปรียบเทียบกับเดือนก่อนหน้า</h4>
            <canvas id="comparisonChart"></canvas>
        </div>
        <br>
        <div class="report-section">
            <h4 class="mt-3">ค่าเฉลี่ยการให้บริการต่อวันในเดือน <?php echo "$month/$year"; ?></h4>
            <p class="text-center">ค่าเฉลี่ยของจำนวนคิวที่ได้รับบริการต่อวัน: <?php echo number_format($avg_queues, 2); ?> คิว/วัน</p>
        </div>
        <br>
        <div class="report-section">
            <h4 class="mt-3">จำนวนนิสิตที่มาใช้บริการตามสาขาวิชาและชั้นปี</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>สาขาวิชา</th>
                            <th>ชั้นปี</th>
                            <th>จำนวนนิสิต</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_students = 0;
                        while ($student = $students->fetch_assoc()) :
                            $total_students += $student['total_students'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['field_of_study']); ?></td>
                                <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                                <td><?php echo htmlspecialchars($student['total_students']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">รวมทั้งหมด</td>
                            <td><?php echo $total_students; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>


    </div>

    <script>
        const serviceCtx = document.getElementById('serviceChart').getContext('2d');
        const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');

        const serviceChart = new Chart(serviceCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php while ($service = $service_counts->fetch_assoc()) : ?> '<?php echo htmlspecialchars($service['service_name']); ?>',
                    <?php endwhile; ?>
                ],
                datasets: [{
                        label: 'จำนวนคิวรวม',
                        data: [
                            <?php $service_counts->data_seek(0);
                            while ($service = $service_counts->fetch_assoc()) : ?>
                                <?php echo $service['total_queues']; ?>,
                            <?php endwhile; ?>
                        ],
                        backgroundColor: '#36a2eb'
                    },
                    {
                        label: 'ได้รับบริการแล้ว',
                        data: [
                            <?php $service_counts->data_seek(0);
                            while ($service = $service_counts->fetch_assoc()) : ?>
                                <?php echo $service['received']; ?>,
                            <?php endwhile; ?>
                        ],
                        backgroundColor: '#4bc0c0'
                    },
                    {
                        label: 'ไม่ได้มา',
                        data: [
                            <?php $service_counts->data_seek(0);
                            while ($service = $service_counts->fetch_assoc()) : ?>
                                <?php echo $service['not_coming']; ?>,
                            <?php endwhile; ?>
                        ],
                        backgroundColor: '#ff6384'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const comparisonChart = new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: ['เดือนนี้', 'เดือนก่อน'],
                datasets: [{
                    label: 'จำนวนคิว',
                    data: [
                        <?php echo $comparison_result['current_month_count']; ?>,
                        <?php echo $comparison_result['prev_month_count']; ?>
                    ],
                    backgroundColor: ['#36a2eb', '#ff6384']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        document.getElementById('downloadPDF').addEventListener('click', function() {
            const element = document.body;
            const opt = {
                margin: 0.5,
                filename: `รายงานสรุปรายเดือน_<?php echo "$month" . "_" . "$year"; ?>.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };
            html2pdf().from(element).set(opt).save();
        });
    </script>
</body>

</html>