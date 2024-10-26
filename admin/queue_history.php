<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$activePage = 'queue_history';

// ดึงข้อมูลประวัติคิวทั้งหมด
$queue_history = $conn->query("SELECT q.queue_id, s.full_name, srv.service_name, se.employee_name, q.queue_time, q.status  
                               FROM queue q
                               JOIN students s ON q.student_id = s.student_id
                               JOIN services srv ON q.service_id = srv.service_id 
                               JOIN serviceemployee se ON q.employee_id = se.employee_id
                               ORDER BY q.queue_time DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Queue History</title>
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

    <!-- ส่วนเนื้อหาของหน้า Queue History -->
    <div class="container-fluid">
        <br>
        <div class="d-flex justify-content-center">
            <h1>ประวัติคิว</h1>
        </div>
        <!-- แสดงตารางประวัติคิว -->
        <div class="table-responsive mt-4">
            <table id="queueTable" class="table table-striped mt-4">
                <thead class="table-dark">
                    <tr>
                        <th>Queue ID</th>
                        <th>Student Name</th>
                        <th>Service Name</th>
                        <th>Employee Name</th>
                        <th>Queue Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($queue = $queue_history->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $queue['queue_id']; ?></td>
                            <td><?php echo $queue['full_name']; ?></td>
                            <td><?php echo $queue['service_name']; ?></td>
                            <td><?php echo $queue['employee_name']; ?></td>
                            <td><?php echo $queue['queue_time']; ?></td>
                            <td><?php echo $queue['status']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ใส่สคริปต์ JavaScript ที่จำเป็น -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#queueTable').DataTable({
                "pageLength": 20,
                "lengthMenu": [
                    [20, 40, 60, 80, 100, -1],
                    ['20', '40', '60', '80', '100', 'ทั้งหมด']
                ],
                "language": {
                    "search": "ค้นหา:",
                    "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                    "zeroRecords": "ไม่พบข้อมูล",
                    "info": "หน้าที่ _PAGE_ จาก _PAGES_",
                    "infoEmpty": "ไม่มีข้อมูล",
                    "infoFiltered": "(ค้นหาจากทั้งหมด _MAX_ รายการ)",
                    "paginate": {
                        "first": "แรก",
                        "last": "สุดท้าย",
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    },
                }
            });
        });
    </script>
</body>

</html>

</body>

</html>