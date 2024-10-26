<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$activePage = 'services';

// Fetch services
$services = $conn->query("SELECT * FROM services");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Services</title>
    <!-- ลิงก์ CSS และ JavaScript ที่จำเป็น -->
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
        <!-- ปุ่มเพิ่มบริการ --><br>
        <div class="d-flex justify-content-center">
            <h1>จัดการบริการ</h1>
        </div>
        <button id="addServiceBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">เพิ่มบริการใหม่</button>
        <br>

        <!-- Modal สำหรับเพิ่มบริการ -->
        <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addServiceModalLabel">เพิ่มบริการใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="add_service.php" method="POST">
                            <div class="mb-3">
                                <label for="service_id" class="form-label">รหัสบริการ:</label>
                                <input type="text" class="form-control" name="service_id" maxlength="7" required>
                            </div>
                            <div class="mb-3">
                                <label for="service_name" class="form-label">ชื่อบริการ:</label>
                                <input type="text" class="form-control" name="service_name" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                <button type="submit" class="btn btn-success">บันทึก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal สำหรับแก้ไขบริการ -->
        <div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editServiceModalLabel">แก้ไขข้อมูลบริการ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="edit_service.php" method="POST">
                            <input type="hidden" name="service_id" id="edit_service_id_hidden">

                            <div class="mb-3">
                                <label for="edit_service_name" class="form-label">ชื่อบริการ:</label>
                                <input type="text" class="form-control" name="service_name" id="edit_service_name" required>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                <button type="submit" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <table id="servicesTable" class="table table-striped mt-4">
                <thead class="table-dark">
                    <tr>
                        <th>Service ID</th>
                        <th>Service Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($service = $services->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $service['service_id']; ?></td>
                            <td><?php echo $service['service_name']; ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editService('<?php echo $service['service_id']; ?>')">แก้ไข</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteService('<?php echo $service['service_id']; ?>')">ลบ</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ใส่สคริปต์ JavaScript ที่จำเป็น -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            // เริ่มต้น DataTable สำหรับตารางบริการ
            $('#servicesTable').DataTable({
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

        // ฟังก์ชันสำหรับแสดง Modal แก้ไขบริการ
        function editService(service_id) {
            $.ajax({
                url: 'get_service.php',
                type: 'POST',
                data: {
                    service_id: service_id
                },
                success: function(response) {
                    const data = JSON.parse(response);

                    // ใส่ค่าข้อมูลเดิมลงในฟอร์ม
                    $('#edit_service_id_hidden').val(data.service_id);
                    $('#edit_service_name').val(data.service_name);

                    // แสดง Modal แก้ไขบริการ
                    $('#editServiceModal').modal('show');
                },
                error: function(xhr, status, error) {
                    alert('มีข้อผิดพลาดในการดึงข้อมูลบริการ');
                }
            });
        }

        // ฟังก์ชันสำหรับลบบริการ
        function deleteService(service_id) {
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบบริการ ID: ' + service_id + '?')) {
                // ส่ง request ไปยังเซิร์ฟเวอร์เพื่อทำการลบบริการ
                window.location.href = 'delete_service.php?service_id=' + service_id;
            }
        }
    </script>

</body>

</html>