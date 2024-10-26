<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$activePage = 'students';

// Fetch students
$students = $conn->query("SELECT * FROM students");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Students</title>
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

    <div class="container-fluid">
        <br>
        <div class="d-flex justify-content-center">
            <h1>รายชื่อนิสิต</h1>
        </div>
        <div class="table-responsive mt-4"></div>
        <table id="studentsTable" class="table table-striped mt-4">
            <thead class="table-dark">
                <tr>
                    <th>รหัสนิสิต</th>
                    <th>ชื่อ-สกุล</th>
                    <th>สาขา</th>
                    <th>คณะ</th>
                    <th>ชั้นปี</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $students->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $student['student_id']; ?></td>
                        <td><?php echo $student['full_name']; ?></td>
                        <td><?php echo $student['field_of_study']; ?></td>
                        <td><?php echo $student['faculty_of_study']; ?></td>
                        <td><?php echo $student['year_level']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- ใส่สคริปต์ JavaScript ที่จำเป็น -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#studentsTable').DataTable({
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