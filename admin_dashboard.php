<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}
$employees = $conn->query("SELECT * FROM serviceemployee");
$students  = $conn->query("SELECT * FROM students");
$services = $conn->query("SELECT * FROM services");



// Fetch all queue history
$queue_history = $conn->query("SELECT q.queue_id, s.full_name, srv.service_name, se.employee_name, q.queue_time, q.status 
                               FROM queue q
                               JOIN students s ON q.student_id = s.student_id
                               JOIN services srv ON q.service_id = srv.service_id
                               JOIN serviceemployee se ON q.employee_id = se.employee_id
                               ORDER BY q.queue_time DESC");

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
    <div class="grid-container">

        <!-- Header -->
        <header class="header">
            <div class="menu-icon" onclick="openSidebar()">
                <span class="material-icons-outlined">menu</span>
            </div>
            <div class="header-right">
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </header>
        <!-- End Header -->

        <!-- Sidebar -->
        <aside id="sidebar">
            <div class="sidebar-title">
                <div class="sidebar-brand">
                    Academic room
                </div>
                <span class="material-icons-outlined" onclick="closeSidebar()">close</span>
            </div>

            <ul class="sidebar-list">
                <li class="sidebar-list-item">
                    <a href="#" onclick="showPage('dashboard')">
                        <span class="material-icons-outlined">dashboard</span> Dashboard
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="#" onclick="showPage('queue-history')">
                        <span class="material-icons-outlined">history</span> Queue History
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="#" onclick="showPage('employee')">
                        <span class="material-icons-outlined">people</span> Employees
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="#" onclick="showPage('students')">
                        <span class="material-icons-outlined">school</span> Students
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="#" onclick="showPage('services')">
                        <span class="material-icons-outlined">build</span> Services
                    </a>
                </li>
            </ul>

        </aside>
        <!-- End Sidebar -->

        <!-- Main -->
        <main class="main-container" id="dashboard">
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

        </main>

        <!-- End Main -->

        <!-- Queue History -->
        <section class="main-container" id="queue-history" style="display:none;">
            <div class="main-title">
                <p class="font-weight-bold">QUEUE HISTORY</p>
            </div>
            <table id="queueTable" class="display table table-striped">
                <thead>
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
        </section>
        <!-- End Queue History -->

        <!-- Employee Page -->
        <section class="main-container" id="employee" style="display:none;">
            <div class="main-title d-flex justify-content-between align-items-center">
                <p class="font-weight-bold">EMPLOYEES</p>
                <button id="addEmployeeBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">เพิ่มพนักงานใหม่</button>

            </div>

            <!-- Modal สำหรับเพิ่มพนักงาน -->
            <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addEmployeeModalLabel">เพิ่มพนักงานใหม่</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="add_employee.php" method="POST">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">เลขพนักงาน:</label>
                                    <input type="text" class="form-control" name="employee_id" required>
                                </div>
                                <div class="mb-3">
                                    <label for="service_id" class="form-label">บริการ:</label>
                                    <select class="form-select" name="service_id" required>
                                        <option value="">เลือกบริการ</option>
                                        <?php
                                        $services = $conn->query("SELECT service_id, service_name FROM services");
                                        while ($service = $services->fetch_assoc()) {
                                            echo '<option value="' . $service['service_id'] . '">' . $service['service_name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="employee_name" class="form-label">ชื่อ-สกุล:</label>
                                    <input type="text" class="form-control" name="employee_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username:</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password:</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">วันที่เข้าทำงาน:</label>
                                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">สิ้นสุดการทำงาน:</label>
                                    <input type="date" class="form-control" name="end_date">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email:</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="more_details" class="form-label">ข้อมูลเพิ่มเติม:</label>
                                    <textarea class="form-control" name="more_details" rows="3"></textarea>
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
            <!-- Form สำหรับแก้ไขพนักงาน -->
            <div id="editEmployeeForm" class="card mt-4" style="display:none;">
                <div class="card-body">
                    <h5 class="card-title text-center">แก้ไขข้อมูลพนักงาน</h5>
                    <form action="edit_employee.php" method="POST" id="editForm">
                        <input type="hidden" name="employee_id" id="edit_employee_id">

                        <!-- บริการ -->
                        <div class="row mb-3">
                            <label for="edit_service_id" class="col-sm-3 col-form-label text-end fw-bold">บริการ:</label>
                            <div class="col-sm-9">
                                <select class="form-select shadow-sm" name="service_id" id="edit_service_id" required>
                                    <option value="">เลือกบริการ</option>
                                    <?php
                                    $services = $conn->query("SELECT service_id, service_name FROM services");
                                    while ($service = $services->fetch_assoc()) {
                                        echo '<option value="' . $service['service_id'] . '">' . $service['service_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- ชื่อ-สกุล -->
                        <div class="row mb-3">
                            <label for="edit_employee_name" class="col-sm-3 col-form-label text-end fw-bold">ชื่อ-สกุล:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control shadow-sm" name="employee_name" id="edit_employee_name" required>
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="row mb-3">
                            <label for="edit_username" class="col-sm-3 col-form-label text-end fw-bold">Username:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control shadow-sm" name="username" id="edit_username" required>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="row mb-3">
                            <label for="edit_password" class="col-sm-3 col-form-label text-end fw-bold">Password:</label>
                            <div class="col-sm-9">
                                <input type="password" class="form-control shadow-sm" name="password" id="edit_password" required>
                            </div>
                        </div>

                        <!-- วันที่เข้าทำงาน -->
                        <div class="row mb-3">
                            <label for="edit_start_date" class="col-sm-3 col-form-label text-end fw-bold">วันที่เข้าทำงาน:</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control shadow-sm" name="start_date" id="edit_start_date" required>
                            </div>
                        </div>

                        <!-- สิ้นสุดการทำงาน -->
                        <div class="row mb-3">
                            <label for="edit_end_date" class="col-sm-3 col-form-label text-end fw-bold">สิ้นสุดการทำงาน:</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control shadow-sm" name="end_date" id="edit_end_date">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="row mb-3">
                            <label for="edit_email" class="col-sm-3 col-form-label text-end fw-bold">Email:</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control shadow-sm" name="email" id="edit_email" required>
                            </div>
                        </div>

                        <!-- ข้อมูลเพิ่มเติม -->
                        <div class="row mb-3">
                            <label for="edit_more_details" class="col-sm-3 col-form-label text-end fw-bold">ข้อมูลเพิ่มเติม:</label>
                            <div class="col-sm-9">
                                <textarea class="form-control shadow-sm" name="more_details" id="edit_more_details" rows="3"></textarea>
                            </div>
                        </div>

                        <!-- ปุ่มบันทึกการเปลี่ยนแปลงและยกเลิก -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
                            <button type="button" class="btn btn-secondary" onclick="cancelEdit()">ยกเลิก</button>
                        </div>
                    </form>
                </div>
            </div>


            <table id="employeeTable" class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>เลขพนักงาน</th>
                        <th>รหัสบริการ</th>
                        <th>ชื่อ-สกุล</th>
                        <th>username</th>
                        <th>password</th>
                        <th>วันที่เข้าทำงาน</th>
                        <th>สิ้นสุดการทำงาน</th>
                        <th>email</th>
                        <th>ข้อมูลเพิ่มเติม</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($employee = $employees->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $employee['employee_id']; ?></td>
                            <td><?php echo $employee['service_id']; ?></td>
                            <td><?php echo $employee['employee_name']; ?></td>
                            <td><?php echo $employee['username']; ?></td>
                            <td><?php echo $employee['password']; ?></td>
                            <td><?php echo $employee['start_date']; ?></td>
                            <td><?php echo $employee['end_date']; ?></td>
                            <td><?php echo $employee['email']; ?></td>
                            <td><?php echo $employee['more_details']; ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editEmployee(<?php echo $employee['employee_id']; ?>)">แก้ไข</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteEmployee(<?php echo $employee['employee_id']; ?>)">ลบ</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Students Page -->
        <section class="main-container" id="students" style="display:none;">
            <div class="main-title">
                <p class="font-weight-bold">STUDENTS</p>
            </div>

            <table id="studentsTable" class="table table-striped mt-4">
                <thead>
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
        </section>
        <!-- Services Page -->
        <section class="main-container" id="services" style="display:none;">
            <div class="main-title d-flex justify-content-between align-items-center">
                <p class="font-weight-bold">SERVICES</p>
                <button id="addServiceBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">เพิ่มบริการใหม่</button>
            </div>

            <table id="servicesTable" class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Service ID</th>
                        <th>Service Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $services->data_seek(0);
                    while ($service = $services->fetch_assoc()) : ?>
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
        </section>

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
                                <input type="text" class="form-control" name="service_id" required>
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


    </div>


    <!-- Custom JS -->
    <script>
        function cancelEditService() {
            $('#editServiceForm').hide(); // ซ่อนฟอร์มแก้ไขบริการ
        }

        function editService(service_id) {
            $.ajax({
                url: 'get_service_data.php',
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


        function deleteService(service_id) {
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบบริการ ID: ' + service_id + '?')) {
                // ส่ง request ไปยังเซิร์ฟเวอร์เพื่อทำการลบบริการ
                window.location.href = 'delete_service.php?service_id=' + service_id;
            }
        }

        function cancelEdit() {
            $('#editEmployeeForm').hide(); // ซ่อนฟอร์มแก้ไข
        }

        function editEmployee(employee_id) {
            $.ajax({
                url: 'get_employee_data.php', // สร้างไฟล์ get_employee_data.php เพื่อดึงข้อมูลพนักงาน
                type: 'POST',
                data: {
                    employee_id: employee_id
                },
                success: function(response) {
                    const data = JSON.parse(response);

                    // ใส่ค่าข้อมูลเดิมลงในฟอร์ม
                    $('#edit_employee_id').val(data.employee_id);
                    $('#edit_service_id').val(data.service_id);
                    $('#edit_employee_name').val(data.employee_name);
                    $('#edit_username').val(data.username);
                    $('#edit_password').val(data.password);
                    $('#edit_start_date').val(data.start_date);
                    $('#edit_end_date').val(data.end_date);
                    $('#edit_email').val(data.email);
                    $('#edit_more_details').val(data.more_details);

                    // แสดงฟอร์มแก้ไข
                    $('#editEmployeeForm').show();
                },
                error: function(xhr, status, error) {
                    alert('มีข้อผิดพลาดในการดึงข้อมูลพนักงาน');
                }
            });
        }

        document.getElementById('addEmployeeBtn').addEventListener('click', function() {
            const form = document.getElementById('addEmployeeForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });

        function deleteEmployee(employee_id) {
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบพนักงาน ID: ' + employee_id + '?')) {
                // ส่ง request ไปยังเซิร์ฟเวอร์เพื่อทำการลบพนักงาน
                window.location.href = 'delete_employee.php?employee_id=' + employee_id;
            }
        }
        // SIDEBAR TOGGLE
        let sidebarOpen = false;
        const sidebar = document.getElementById('sidebar');

        function openSidebar() {
            if (!sidebarOpen) {
                sidebar.classList.add('sidebar-responsive');
                sidebarOpen = true;
            }
        }

        function closeSidebar() {
            if (sidebarOpen) {
                sidebar.classList.remove('sidebar-responsive');
                sidebarOpen = false;
            }
        }

        function showPage(page) {
            document.getElementById('dashboard').style.display = 'none';
            document.getElementById('queue-history').style.display = 'none';
            document.getElementById('employee').style.display = 'none';
            document.getElementById('students').style.display = 'none';
            document.getElementById('services').style.display = 'none';
            document.getElementById(page).style.display = 'block';
        }


        $(document).ready(function() {
            $('#queueTable').DataTable();
            $('#employeeTable').DataTable();
            $('#studentsTable').DataTable();
            $('#servicesTable').DataTable();


            // Pie Chart.js setup for Year Level Queue Overview
            const ctxPie = document.getElementById('yearLevelChart').getContext('2d');
            const yearLevelChart = new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ['คิวที่ได้รับบริการ', 'คิวที่ไม่มา', 'คิวที่กำลังรอ', 'คิวที่ถูกเรียก'],
                    datasets: [{
                        label: 'จำนวนคิว',
                        data: [0, 0, 0, 0], // Initial data
                        backgroundColor: ['#36a2eb', '#ff6384', '#ffcd56', '#4bc0c0']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Update chart when year level is selected
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

            // Initial load for the chart with the first year level
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

            // Update Monthly Queue Trend chart when year is selected
            $('#yearSelect').change(function() {
                const selectedYear = $(this).val();
                $.ajax({
                    url: 'get_yearly_monthly_data.php',
                    type: 'POST',
                    data: {
                        year: selectedYear
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        monthlyQueueChart.data.labels = data.labels;
                        monthlyQueueChart.data.datasets[0].data = data.values;
                        monthlyQueueChart.update();
                    }
                });
            });

            // Initial load for the monthly queue chart with the first year
            $('#yearSelect').trigger('change');

            // Get the current year and month
            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth() + 1; // Months are zero-based in JavaScript

            // Populate year dropdown for Monthly Queue Trend
            const years = <?php echo json_encode($years); ?>;
            years.forEach(year => {
                $('#yearSelect').append(`<option value="${year}" ${year == currentYear ? 'selected' : ''}>${year}</option>`);
            });

            // Populate month and year dropdowns for Selected Month Queue Count
            const months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

            months.forEach((month, index) => {
                $('#monthSelect').append(`<option value="${index + 1}" ${index + 1 == currentMonth ? 'selected' : ''}>${month}</option>`);
            });

            years.forEach(year => {
                $('#monthYearSelect').append(`<option value="${year}" ${year == currentYear ? 'selected' : ''}>${year}</option>`);
            });

            // Load the chart with the default selected year and month
            loadMonthlyQueueTrend(currentYear);
            loadSelectedMonthQueueTrend(currentMonth, currentYear);

            // Update Monthly Queue Trend chart when year is selected
            $('#yearSelect').change(function() {
                const selectedYear = $(this).val();
                loadMonthlyQueueTrend(selectedYear);
            });


            // Function to load the monthly queue trend chart
            function loadMonthlyQueueTrend(year) {
                $.ajax({
                    url: 'get_yearly_monthly_data.php',
                    type: 'POST',
                    data: {
                        year: year
                    },
                    success: function(response) {
                        const data = JSON.parse(response);

                        if (monthlyQueueChart) {
                            // If the chart already exists, update its data
                            monthlyQueueChart.data.labels = data.labels;
                            monthlyQueueChart.data.datasets[0].data = data.values;
                            monthlyQueueChart.update();
                        } else {
                            // Create the chart if it doesn't exist
                            const ctxBar = document.getElementById('monthlyQueueChart').getContext('2d');
                            monthlyQueueChart = new Chart(ctxBar, {
                                type: 'bar',
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
                        }
                    }
                });
            }

            let selectedMonthChart;

            // Function to load the selected month queue trend chart
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

                        // If selectedMonthChart is not initialized, initialize it
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
                            // Update chart data
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

            // Bind event listeners to handle changes
            $('#monthSelect, #monthYearSelect').change(function() {
                const selectedMonth = $('#monthSelect').val();
                const selectedYear = $('#monthYearSelect').val();
                loadSelectedMonthQueueTrend(selectedMonth, selectedYear);
            });

            // Initial load for the line chart with the first month and year
            $('#monthSelect').trigger('change');
        });

        // Refresh the page every 500 seconds
        setInterval(refreshPage, 500000);

        function refreshPage() {
            location.reload();
        }

        document.getElementById('generateReportBtn').addEventListener('click', function() {
            const selectedDate = document.getElementById('reportDate').value;
            if (selectedDate) {
                window.location.href = `daily_report.php?date=${selectedDate}`;
            } else {
                alert('Please select a date for the report.');
            }
        });
        document.getElementById('generateMonthlyReportBtn').addEventListener('click', function() {
            const selectedMonth = document.getElementById('monthReport').value;
            const selectedYear = document.getElementById('yearReport').value;
            if (selectedMonth && selectedYear) {
                window.location.href = `monthly_report.php?month=${selectedMonth}&year=${selectedYear}`;
            } else {
                alert('กรุณาเลือกเดือนและปีสำหรับรายงานรายเดือน');
            }
        });

        // Populate months and years in the dropdowns
        const currentYear = new Date().getFullYear();
        const currentMonth = new Date().getMonth() + 1;

        const months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        months.forEach((month, index) => {
            $('#monthReport').append(`<option value="${index + 1}" ${index + 1 == currentMonth ? 'selected' : ''}>${month}</option>`);
        });

        const years = <?php echo json_encode($years); ?>;
        years.forEach(year => {
            $('#yearReport').append(`<option value="${year}" ${year == currentYear ? 'selected' : ''}>${year}</option>`);
        });
    </script>
</body>

</html>