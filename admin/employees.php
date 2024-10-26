<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$activePage = 'employees';

// ดึงข้อมูลพนักงานพร้อมชื่อบริการ
$employees = $conn->query("
    SELECT se.*, s.service_name
    FROM serviceemployee se
    LEFT JOIN services s ON se.service_id = s.service_id
");

// ดึงบริการที่ยังไม่มีพนักงานรับผิดชอบ หรือ service_id เป็น '000000' สำหรับฟอร์มเพิ่มพนักงาน
$available_services = $conn->query("
    SELECT s.service_id, s.service_name
    FROM services s
    WHERE s.service_id NOT IN (SELECT service_id FROM serviceemployee WHERE service_id IS NOT NULL)
    OR s.service_id = '000000'
");

// ดึงบริการทั้งหมดสำหรับฟอร์มแก้ไขพนักงาน
$all_services = $conn->query("SELECT service_id, service_name FROM services");
?>



<!DOCTYPE html>
<html lang="th">

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

    <!-- เนื้อหาหลัก -->
    <div class="container-fluid">

        
        <!-- ปุ่มเพิ่มพนักงาน --><br>
        <div class="d-flex justify-content-center">
            <h1>จัดการพนักงาน</h1>
        </div>
        <button class="btn btn-primary " data-bs-toggle="modal" data-bs-target="#addEmployeeModal">เพิ่มพนักงานใหม่</button>
        
        <!-- Modal สำหรับเพิ่มพนักงาน -->
        <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="add_employee.php" method="POST" id="addEmployeeForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addEmployeeModalLabel">เพิ่มพนักงานใหม่</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- เลขพนักงาน -->
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">เลขพนักงาน:</label>
                                <input type="text" class="form-control" name="employee_id" required>
                            </div>
                            <!--ระดับการเข้าถึง -->
                            <div class="mb-3">
                                <label for="access_level" class="form-label">ระดับการเข้าถึง:</label>
                                <select class="form-select" name="access_level" required>
                                    <option value="">เลือกระดับการเข้าถึง</option>
                                    <option value="admin">Admin</option>
                                    <option value="employee">Employee</option>
                                </select>
                            </div>

                            <!-- บริการ -->
                            <div class="mb-3">
                                <label for="service_id" class="form-label">บริการ:</label>
                                <select class="form-select" name="service_id" required>
                                    <option value="">เลือกบริการ</option>
                                    <?php
                                    while ($service = $available_services->fetch_assoc()) {
                                        echo '<option value="' . $service['service_id'] . '">' . $service['service_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- ชื่อ-สกุล -->
                            <div class="mb-3">
                                <label for="employee_name" class="form-label">ชื่อ-สกุล:</label>
                                <input type="text" class="form-control" name="employee_name" required>
                            </div>
                            <!-- Username -->
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <!-- วันที่เข้าทำงาน -->
                            <div class="mb-3">
                                <label for="start_date" class="form-label">วันที่เข้าทำงาน:</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <!-- สิ้นสุดการทำงาน -->
                            <div class="mb-3">
                                <label for="end_date" class="form-label">สิ้นสุดการทำงาน:</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <!-- ข้อมูลเพิ่มเติม -->
                            <div class="mb-3">
                                <label for="more_details" class="form-label">ข้อมูลเพิ่มเติม:</label>
                                <textarea class="form-control" name="more_details" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-success">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal สำหรับแก้ไขพนักงาน -->
        <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="edit_employee.php" method="POST" id="editEmployeeForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editEmployeeModalLabel">แก้ไขข้อมูลพนักงาน</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- เลขพนักงาน (ซ่อน) -->
                            <input type="hidden" name="employee_id" id="edit_employee_id">
                            <!-- ระดับการเข้าถึง -->
                            <div class="mb-3">
                                <label for="edit_access_level" class="form-label">ระดับการเข้าถึง:</label>
                                <select class="form-select" name="access_level" id="edit_access_level" required>
                                    <option value="">เลือกระดับการเข้าถึง</option>
                                    <option value="admin">Admin</option>
                                    <option value="employee">Employee</option>
                                </select>
                            </div>

                            <!-- บริการ -->
                            <div class="mb-3">
                                <label for="edit_service_id" class="form-label">บริการ:</label>
                                <select class="form-select" name="service_id" id="edit_service_id" required>
                                    <option value="">เลือกบริการ</option>
                                    <?php
                                    while ($service = $all_services->fetch_assoc()) {
                                        echo '<option value="' . $service['service_id'] . '">' . $service['service_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- ชื่อ-สกุล -->
                            <div class="mb-3">
                                <label for="edit_employee_name" class="form-label">ชื่อ-สกุล:</label>
                                <input type="text" class="form-control" name="employee_name" id="edit_employee_name" required>
                            </div>
                            <!-- Username -->
                            <div class="mb-3">
                                <label for="edit_username" class="form-label">Username:</label>
                                <input type="text" class="form-control" name="username" id="edit_username" required>
                            </div>
                            <!-- Password -->
                            <div class="mb-3">
                                <label for="edit_password" class="form-label">Password:</label>
                                <input type="password" class="form-control" name="password" id="edit_password" required>
                            </div>
                            <!-- วันที่เข้าทำงาน -->
                            <div class="mb-3">
                                <label for="edit_start_date" class="form-label">วันที่เข้าทำงาน:</label>
                                <input type="date" class="form-control" name="start_date" id="edit_start_date" required>
                            </div>
                            <!-- สิ้นสุดการทำงาน -->
                            <div class="mb-3">
                                <label for="edit_end_date" class="form-label">สิ้นสุดการทำงาน:</label>
                                <input type="date" class="form-control" name="end_date" id="edit_end_date">
                            </div>
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email:</label>
                                <input type="email" class="form-control" name="email" id="edit_email" required>
                            </div>
                            <!-- ข้อมูลเพิ่มเติม -->
                            <div class="mb-3">
                                <label for="edit_more_details" class="form-label">ข้อมูลเพิ่มเติม:</label>
                                <textarea class="form-control" name="more_details" id="edit_more_details" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ตารางข้อมูลพนักงาน -->
        <div class="table-responsive mt-4">
            <table id="employeeTable" class="table table-striped mt-4">
                <thead class="table-dark">
                    <tr>
                        <th>เลขพนักงาน</th>
                        <th>ระดับ</th>
                        <th>บริการ</th>
                        <th>ชื่อ-สกุล</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>วันที่เข้าทำงาน</th>
                        <th>สิ้นสุดการทำงาน</th>
                        <th>Email</th>
                        <th>ข้อมูลเพิ่มเติม</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($employee = $employees->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $employee['employee_id']; ?></td>
                            <td><?php echo $employee['access_level']; ?></td>
                            <td><?php echo $employee['service_name']; ?></td>
                            <td><?php echo $employee['employee_name']; ?></td>
                            <td><?php echo $employee['username']; ?></td>
                            <td><?php echo $employee['password']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($employee['start_date'])); ?></td>
                            <td>
                                <?php
                                $end_date = $employee['end_date'];
                                if ($end_date == '0000-00-00' || empty($end_date)) {
                                    echo 'ยังทำงานอยู่';
                                } else {
                                    echo date('d/m/Y', strtotime($end_date));
                                }
                                ?>
                            </td>
                            <td><?php echo $employee['email']; ?></td>
                            <td><?php echo $employee['more_details']; ?></td>
                            <td class="text-center align-middle">
                               
                                    <button class="btn btn-warning btn-sm" onclick="editEmployee('<?php echo $employee['employee_id']; ?>')">แก้ไข</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteEmployee('<?php echo $employee['employee_id']; ?>')">ลบ</button>
                                
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript ที่จำเป็น -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            // เริ่มต้น DataTable
            $('#employeeTable').DataTable({
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


            // เมื่อคลิกปุ่มเพิ่มพนักงานใหม่
            $('#addEmployeeBtn').on('click', function() {
                // รีเซ็ตฟอร์ม
                $('#addEmployeeForm')[0].reset();
                // แสดง Modal
                $('#addEmployeeModal').modal('show');
            });
        });

        function editEmployee(employee_id) {
            $.ajax({
                url: 'get_employee_data.php',
                type: 'POST',
                data: {
                    employee_id: employee_id
                },
                success: function(response) {
                    try {
                        // ใส่ค่าข้อมูลเดิมลงในฟอร์มแก้ไข
                        $('#edit_employee_id').val(response.employee_id);
                        $('#edit_service_id').val(response.service_id);
                        $('#edit_employee_name').val(response.employee_name);
                        $('#edit_username').val(response.username);
                        $('#edit_password').val(response.password);
                        $('#edit_start_date').val(response.start_date);
                        $('#edit_end_date').val(response.end_date);
                        $('#edit_email').val(response.email);
                        $('#edit_more_details').val(response.more_details);
                        $('#edit_access_level').val(response.access_level);

                        // แสดง Modal แก้ไขพนักงาน
                        $('#editEmployeeModal').modal('show');
                    } catch (e) {
                        console.error('Error processing data:', e);
                        console.log('Response:', response);
                        alert('เกิดข้อผิดพลาดในการประมวลผลข้อมูล');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.log('Response:', xhr.responseText);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                }
            });
        }



        function deleteEmployee(employee_id) {
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบพนักงาน ID: ' + employee_id + '?')) {
                window.location.href = 'delete_employee.php?employee_id=' + employee_id;
            }
        }
    </script>
</body>

</html>