<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

// Fetch employee details
$stmt = $conn->prepare("SELECT * FROM serviceemployee WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติคิว</title>
    <link id="favicon" rel="icon" href="favicon.ico" type="image/x-icon">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS with Bootstrap 5 integration -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        .nav {
            background-color: #343a40;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .nav-logo img {
            width: 40px;
        }

        .nav-menu ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .nav-menu ul li {
            margin-left: 30px;
        }

        .nav-menu ul li a {
            color: white;
            font-weight: 500;
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .nav-menu ul li a:hover {
            color: #007bff;
        }

        .nav-menu ul li a.active {
            color: #007bff;
        }

        .container {
            margin-top: 50px;
        }

        .table-striped th {
            background-color: #F3B937;
            color: black;
        }

        .table-striped tbody tr:hover {
            background-color: #f1f1f1;
        }

        .welcome-text {
            color: #343a40;
            font-size: 1.5rem;
        }

        /* Additional Styling for Tables */
        .table thead th {
            vertical-align: middle;
            text-align: center;
        }

        .table tbody td {
            vertical-align: middle;
            text-align: center;
        }

        .btn-custom {
            border-radius: 50px;
            padding: 0.5rem 1rem;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn-custom.btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-custom.btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-custom.btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-custom:hover {
            filter: brightness(0.9);
        }
    </style>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- DataTables JS with Bootstrap 5 integration -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Initialize DataTable
            const queueHistoryTable = $('#queueHistoryTable').DataTable({
                "language": {
                    "decimal": "",
                    "emptyTable": "ไม่มีข้อมูลในตาราง",
                    "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                    "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "loadingRecords": "กำลังโหลด...",
                    "processing": "กำลังประมวลผล...",
                    "search": "ค้นหา:",
                    "zeroRecords": "ไม่พบข้อมูลที่ค้นหา",
                    "paginate": {
                        "first": "หน้าแรก",
                        "last": "หน้าสุดท้าย",
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    },
                    "aria": {
                        "sortAscending": ": เปิดการเรียงลำดับจากน้อยไปมาก",
                        "sortDescending": ": เปิดการเรียงลำดับจากมากไปน้อย"
                    }
                },
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true
            });

            fetchQueueHistoryData(); // Initial fetch
            setInterval(fetchQueueHistoryData, 5000); // Refresh every 5 seconds

            function fetchQueueHistoryData() {
                fetch('fetch_employee_queue_history.php')
                    .then(response => response.json())
                    .then(data => {
                        queueHistoryTable.clear();
                        data.forEach(queue => {
                            queueHistoryTable.row.add([
                                queue.daily_queue_number,
                                queue.full_name,
                                queue.service_name,
                                queue.queue_time,
                                queue.status
                            ]);
                        });
                        queueHistoryTable.draw();
                    })
                    .catch(error => console.error('Error fetching queue history:', error));
            }
        });
    </script>
</head>

<body>
    <div class="wrapper">
        <nav class="nav d-flex justify-content-between align-items-center">
            <div class="nav-logo">
                <img src="SC_Naresuan.png" alt="Logo">
            </div>
            <div class="nav-menu">
                <ul>
                    <li><a href="staff_dashboard.php" class="link">Dashboard</a></li>
                    <li><a href="queue_history.php" class="link active">History</a></li>
                    <li><a href="logout.php" class="link">Logout</a></li>
                </ul>
            </div>
        </nav>
        <div class="container mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="welcome-text">ยินดีต้อนรับ <?php echo htmlspecialchars($employee['employee_name']); ?></h1>
            </div>

            <h2 class="text-dark mb-4">ประวัติคิวของคุณ</h2>
            <table id="queueHistoryTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ลำดับคิว</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>บริการ</th>
                        <th>เวลา</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody id="queue-history-table-body">
                    <!-- Queue history data will be inserted here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
