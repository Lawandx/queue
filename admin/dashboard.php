<?php
session_start();
include '../db_connect.php';

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$activePage = 'dashboard';
// Fetch queue counts by status
$Received_queues = $conn->query("SELECT COUNT(*) AS count FROM queue WHERE status = 'Received'")->fetch_assoc()['count'];
$Not_Coming_queues = $conn->query("SELECT COUNT(*) AS count FROM queue WHERE status = 'Not Coming'")->fetch_assoc()['count'];
$Waiting_queues = $conn->query("SELECT COUNT(*) AS count FROM queue WHERE status = 'Waiting'")->fetch_assoc()['count'];
$Called_queues = $conn->query("SELECT COUNT(*) AS count FROM queue WHERE status = 'Called'")->fetch_assoc()['count'];

// Fetch monthly queue counts
$monthly_queues = $conn->query("
    SELECT DATE_FORMAT(queue_time, '%Y-%m') AS month, COUNT(*) AS count 
    FROM queue 
    GROUP BY DATE_FORMAT(queue_time, '%Y-%m') 
    ORDER BY DATE_FORMAT(queue_time, '%Y-%m')
");
$monthly_data = [];
while ($row = $monthly_queues->fetch_assoc()) {
    $monthly_data[] = $row;
}

// Fetch total queue count
$total_queues = $conn->query("SELECT COUNT(*) AS total FROM queue")->fetch_assoc()['total'];

// Fetch distinct years
$years_result = $conn->query("
    SELECT DISTINCT YEAR(queue_time) AS year
    FROM queue
    ORDER BY year DESC
");
$years = [];
while ($row = $years_result->fetch_assoc()) {
    $years[] = $row['year'];
}

// Fetch total queue count by service
$service_queue_counts = $conn->query("
    SELECT srv.service_name, COUNT(q.queue_id) AS total_queues
    FROM queue q
    JOIN services srv ON q.service_id = srv.service_id
    GROUP BY srv.service_name
    ORDER BY total_queues DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="styledash.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <!-- Main -->
    <div class="container-fluid">
        <div class="main-title">
            <p class="font-weight-bold">Dashboard</p>
        </div>

        <div class="main-cards">

            <div class="card">
                <div class="card-inner">
                    <p class="text-primary">คิวที่ได้รับบริการ</p>
                    <span class="material-icons-outlined text-blue">task_alt</span>
                </div>
                <span class="text-primary font-weight-bold"><?php echo $Received_queues; ?></span>
            </div>

            <div class="card">
                <div class="card-inner">
                    <p class="text-primary">คิวที่ไม่มา</p>
                    <span class="material-icons-outlined text-orange">highlight_off</span>
                </div>
                <span class="text-primary font-weight-bold"><?php echo $Not_Coming_queues; ?></span>
            </div>

            <div class="card">
                <div class="card-inner">
                    <p class="text-primary">คิวที่กำลังรอ</p>
                    <span class="material-icons-outlined text-green">hourglass_empty</span>
                </div>
                <span class="text-primary font-weight-bold"><?php echo $Waiting_queues; ?></span>
            </div>

            <div class="card">
                <div class="card-inner">
                    <p class="text-primary">คิวที่ถูกเรียก</p>
                    <span class="material-icons-outlined text-red">call</span>
                </div>
                <span class="text-primary font-weight-bold"><?php echo $Called_queues; ?></span>
            </div>

        </div>
        <div class="charts">
            <div class="charts-card">
                <p class="chart-title">ภาพรวมสถานะคิวตามชั้นปี</p>
                <div>
                    <label for="yearLevelSelect">เลือกชั้นปี:</label>
                    <div class="dropdown-container">
                        <select id="yearLevelSelect">
                            <option value="1">ปี 1</option>
                            <option value="2">ปี 2</option>
                            <option value="3">ปี 3</option>
                            <option value="4">ปี 4</option>
                            <!-- เพิ่มตัวเลือกเพิ่มเติมตามที่ต้องการ -->
                        </select>
                    </div>
                </div>
                <div id="yearLevelChartContainer">
                    <canvas id="yearLevelChart"></canvas>
                </div>
            </div>

            <div class="charts-card">
                <p class="chart-title">แนวโน้มคิวรายเดือน</p>
                <div class="dropdown-container">
                    <label for="yearSelect">เลือกปี:</label>
                    <select id="yearSelect">
                        <!-- ตัวเลือกจะถูกเพิ่มโดยอัตโนมัติ -->
                    </select>
                </div>
                <div id="monthlyQueueChartContainer">
                    <canvas id="monthlyQueueChart"></canvas>
                </div>
            </div>

            <div class="charts-card">
                <p class="chart-title">เลือกเดือนสำหรับจำนวนคิว</p>
                <div class="dropdown-container">
                    <label for="monthSelect">เลือกเดือน:</label>
                    <select id="monthSelect">
                        <!-- ตัวเลือกจะถูกเพิ่มโดยอัตโนมัติ -->
                    </select>
                    <label for="monthYearSelect">เลือกปี:</label>
                    <select id="monthYearSelect">
                        <!-- ตัวเลือกจะถูกเพิ่มโดยอัตโนมัติ -->
                    </select>
                </div>
                <div id="selectedMonthChartContainer">
                    <canvas id="selectedMonthChart"></canvas>
                </div>
            </div>

            <div class="charts-card">
                <p class="chart-title">จำนวนคิวทั้งหมดและค่าเฉลี่ยเป็นเปอร์เซ็นต์</p>
                <div class="statistics">
                    <p>จำนวนคิวทั้งหมด: <span id="totalQueues"><?php echo $total_queues; ?></span> คน </p>
                    <p>ได้รับบริการแล้ว: <span id="receivedPercentage"><?php echo number_format(($Received_queues / $total_queues) * 100, 2); ?>%</span></p>
                    <p>จำนวนคิวที่ไม่ได้มารับบริการ: <span id="notReceivedPercentage"><?php echo number_format(($Not_Coming_queues / $total_queues) * 100, 2); ?>%</span></p>

                    <!-- แสดงจำนวนคิวตามบริการ -->
                    <h4 class="mt-4">จำนวนคิวตามบริการ</h4>
                    <ul>
                        <?php while ($service = $service_queue_counts->fetch_assoc()) : ?>
                            <li><?php echo $service['service_name']; ?>: <?php echo $service['total_queues']; ?> คิว</li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <div class="charts-card">
                <p class="chart-title">รายงานสรุปรายวัน</p>
                <div class="dropdown-container">
                    <label for="reportDate">เลือกวันที่:</label>
                    <input type="date" id="reportDate" class="form-control" />
                    <button id="generateReportBtn" class="btn btn-primary mt-2">สร้างรายงาน</button>
                </div>
            </div>
            <div class="charts-card">
                <p class="chart-title">รายงานสรุปรายเดือน</p>
                <div class="dropdown-container">
                    <label for="monthReport">เลือกเดือน:</label>
                    <select id="monthReport" class="form-control">
                        <!-- ตัวเลือกจะถูกเพิ่มโดยอัตโนมัติ -->
                    </select>
                    <label for="yearReport">เลือกปี:</label>
                    <select id="yearReport" class="form-control">
                        <!-- ตัวเลือกจะถูกเพิ่มโดยอัตโนมัติ -->
                    </select>
                    <button id="generateMonthlyReportBtn" class="btn btn-primary mt-2">สร้างรายงานรายเดือน</button>
                </div>
            </div>
        </div>

    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // Pie Chart.js setup for Year Level Queue Overview
            const ctxPie = document.getElementById('yearLevelChart').getContext('2d');
            const yearLevelChart = new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ['คิวที่ได้รับบริการ', 'คิวที่ไม่มา', 'คิวที่กำลังรอ', 'คิวที่ถูกเรียก'],
                    datasets: [{
                        label: 'จำนวนคิว',
                        data: [0, 0, 0, 0], // ข้อมูลเริ่มต้น
                        backgroundColor: ['#36a2eb', '#ff6384', '#ffcd56', '#4bc0c0']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // อัปเดตกราฟเมื่อเลือกชั้นปี
            $('#yearLevelSelect').change(function() {
                const selectedYearLevel = $(this).val();
                $.ajax({
                    url: 'get_queue_status_by_year_level.php',
                    type: 'POST',
                    data: {
                        year_level: selectedYearLevel
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        yearLevelChart.data.datasets[0].data = [
                            data['Received'] || 0,
                            data['Not Coming'] || 0,
                            data['Waiting'] || 0,
                            data['Called'] || 0
                        ];
                        yearLevelChart.update();
                    }
                });
            });

            // โหลดกราฟครั้งแรก
            $('#yearLevelSelect').trigger('change');

            // Bar Chart.js setup for Monthly Queue Trend
            const ctxBar = document.getElementById('monthlyQueueChart').getContext('2d');
            const monthlyQueueChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'จำนวนคิว',
                        data: [],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: '#36a2eb',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'category'
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // อัปเดตกราฟแนวโน้มคิวรายเดือนเมื่อเลือกปี
            $('#yearSelect').change(function() {
                const selectedYear = $(this).val();
                loadMonthlyQueueTrend(selectedYear);
            });

            // โหลดกราฟแนวโน้มคิวรายเดือนครั้งแรก
            $('#yearSelect').trigger('change');

            // รับค่าเดือนและปีปัจจุบัน
            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth() + 1; // เดือนใน JavaScript เริ่มจาก 0

            // เติมข้อมูลปีใน Dropdown สำหรับกราฟแนวโน้มคิวรายเดือน
            const years = <?php echo json_encode($years); ?>;
            years.forEach(year => {
                $('#yearSelect').append(`<option value="${year}" ${year == currentYear ? 'selected' : ''}>${year}</option>`);
            });

            // เติมข้อมูลเดือนและปีใน Dropdown สำหรับจำนวนคิวในเดือนที่เลือก
            const months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

            months.forEach((month, index) => {
                $('#monthSelect').append(`<option value="${index + 1}" ${index + 1 == currentMonth ? 'selected' : ''}>${month}</option>`);
            });

            years.forEach(year => {
                $('#monthYearSelect').append(`<option value="${year}" ${year == currentYear ? 'selected' : ''}>${year}</option>`);
            });

            // โหลดกราฟสำหรับเดือนและปีที่เลือกครั้งแรก
            loadMonthlyQueueTrend(currentYear);
            loadSelectedMonthQueueTrend(currentMonth, currentYear);

            // ฟังก์ชันสำหรับโหลดกราฟแนวโน้มคิวรายเดือน
            function loadMonthlyQueueTrend(year) {
                $.ajax({
                    url: 'get_yearly_monthly_data.php',
                    type: 'POST',
                    data: {
                        year: year
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        monthlyQueueChart.data.labels = data.labels;
                        monthlyQueueChart.data.datasets[0].data = data.values;
                        monthlyQueueChart.update();
                    }
                });
            }

            let selectedMonthChart;

            // ฟังก์ชันสำหรับโหลดกราฟจำนวนคิวในเดือนที่เลือก
            function loadSelectedMonthQueueTrend(month, year) {
                $.ajax({
                    url: 'get_monthly_data.php',
                    type: 'POST',
                    data: {
                        month: month,
                        year: year
                    },
                    success: function(response) {
                        const data = JSON.parse(response);

                        if (!selectedMonthChart) {
                            const ctxLine = document.getElementById('selectedMonthChart').getContext('2d');
                            selectedMonthChart = new Chart(ctxLine, {
                                type: 'line',
                                data: {
                                    labels: data.labels,
                                    datasets: [{
                                        label: 'จำนวนคิว',
                                        data: data.values,
                                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                        borderColor: '#36a2eb',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        x: {
                                            type: 'category'
                                        },
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        } else {
                            // อัปเดตข้อมูลกราฟ
                            selectedMonthChart.data.labels = data.labels;
                            selectedMonthChart.data.datasets[0].data = data.values;
                            selectedMonthChart.update();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching data:", error);
                    }
                });
            }

            // เมื่อมีการเปลี่ยนแปลงเดือนหรือปีที่เลือก
            $('#monthSelect, #monthYearSelect').change(function() {
                const selectedMonth = $('#monthSelect').val();
                const selectedYear = $('#monthYearSelect').val();
                loadSelectedMonthQueueTrend(selectedMonth, selectedYear);
            });

            // โหลดกราฟจำนวนคิวในเดือนที่เลือกครั้งแรก
            $('#monthSelect').trigger('change');
        });

        // รีเฟรชหน้าเว็บทุก ๆ 500 วินาที
        setInterval(refreshPage, 500000);

        function refreshPage() {
            location.reload();
        }

        // ฟังก์ชันสำหรับสร้างรายงานสรุปรายวัน
        document.getElementById('generateReportBtn').addEventListener('click', function() {
            const selectedDate = document.getElementById('reportDate').value;
            if (selectedDate) {
                window.location.href = `daily_report.php?date=${selectedDate}`;
            } else {
                alert('กรุณาเลือกวันที่สำหรับรายงาน');
            }
        });

        // ฟังก์ชันสำหรับสร้างรายงานสรุปรายเดือน
        document.getElementById('generateMonthlyReportBtn').addEventListener('click', function() {
            const selectedMonth = document.getElementById('monthReport').value;
            const selectedYear = document.getElementById('yearReport').value;
            if (selectedMonth && selectedYear) {
                window.location.href = `monthly_report.php?month=${selectedMonth}&year=${selectedYear}`;
            } else {
                alert('กรุณาเลือกเดือนและปีสำหรับรายงานรายเดือน');
            }
        });

        // เติมข้อมูลเดือนและปีใน Dropdown สำหรับรายงาน
        const monthsReport = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        monthsReport.forEach((month, index) => {
            $('#monthReport').append(`<option value="${index + 1}">${month}</option>`);
        });

        const yearsReport = <?php echo json_encode($years); ?>;
        yearsReport.forEach(year => {
            $('#yearReport').append(`<option value="${year}">${year}</option>`);
        });
    </script>

</body>

</html>