<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exit();
}

$services = $conn->query("SELECT * FROM services WHERE service_id");

// ตรวจสอบวันที่ล่าสุดที่มีการเปลี่ยนแปลง
$today_date = date('Y-m-d');
$last_checked_date = isset($_SESSION['last_checked_date']) ? $_SESSION['last_checked_date'] : '';

if ($today_date !== $last_checked_date) {
    $conn->query("UPDATE queue SET status = 'Not Coming' WHERE status = 'Waiting' OR status = 'Called'");
    $_SESSION['last_checked_date'] = $today_date;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>หน้าจองคิว - คณะวิทยาศาสตร์ มหาวิทยาลัยนเรศวร</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f8f9fa;
        }

        /* Navbar Styles */
        .navbar-custom {
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand img {
            height: 50px;
            margin-right: 10px;
        }

        .navbar-nav .nav-link {
            color: #333;
            font-weight: 500;
            margin: 0 10px;
        }

        .navbar-nav .nav-link:hover {
            color: #007bff;
        }

        .btn-signin {
            background-color: #007bff;
            color: #fff;
            border-radius: 20px;
            padding: 8px 20px;
            transition: background-color 0.3s;
        }

        .btn-signin:hover {
            background-color: #0056b3;
        }

        /* Hero Section */
        .hero-section {
            background: url(assets/img/faculy.jpg);
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: #fff;
            text-align: center;
            position: relative;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 40px;
        }

        .hero-content .btn-hero {
            background-color: #fcb21e;
            color: #000;
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 30px;
            transition: background-color 0.3s;
        }

        .btn-hero:hover {
            background-color: #e0a800;
        }

        /* Services Section */
        .services-section {
            padding: 60px 0;
        }

        /* ส่วนของเลขในวงกลม */
        .number-circle {
            width: 60px;
            height: 60px;
            background-color: #fcb21e;
            color: #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 10px auto;
        }

        /* สไตล์สำหรับการ์ดบริการ */
        .service-card {
            background-color: #fff;
            border: 2px solid transparent;
            border-radius: 15px;
            padding: 20px 10px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .service-card:hover {
            border-color: #fcb21e;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .service-card.selected {
            border-color: #0a8f08;
            box-shadow: 0 0 10px rgba(10, 143, 8, 0.5);
        }

        .service-card h5 {
            margin-top: 10px;
            font-weight: 600;
        }


        .service-card-two {
            background-color: #fff;
            border: none;
            border-radius: 15px;
            transition: transform 0.3s;
            text-align: center;
            padding: 30px 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .service-card-two:hover {
            transform: translateY(-10px);
        }

        .service-card-two .icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 20px;
        }

        .service-card-two h5 {
            font-weight: 600;
            margin-bottom: 15px;
        }

        .service-card-two p {
            color: #666;
            font-size: 0.95rem;
        }

        /* Booking Section */
        .booking-section {
            padding: 60px 0;
            background-color: #f8f9fa;
        }

        .booking-form {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .booking-form h2 {
            font-weight: 600;
            margin-bottom: 30px;
        }

        .form-control:focus {
            border-color: #fcb21e;
            box-shadow: none;
        }

        .btn-booking {
            background-color: #fcb21e;
            color: #000;
            padding: 10px 30px;
            border-radius: 30px;
            transition: background-color 0.3s;
            font-weight: 600;
        }

        .btn-booking:hover {
            background-color: #e0a800;
        }

        .btn-custom {
            background-color: #fcb21e;
            color: #000;
        }

        .btn-custom:hover {
            background-color: #ff6f00;
            color: #fff;
        }

        /* Footer Styles */
        footer {
            background-color: #fff;
            padding: 40px 0;
            border-top: 1px solid #ddd;
        }

        footer .footer-logo {
            width: 100px;
            margin-bottom: 20px;
        }

        footer .footer-info p {
            margin: 0;
            color: #666;
        }

        footer .social-icons a {
            color: #333;
            margin-right: 15px;
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        footer .social-icons a:hover {
            color: #007bff;
        }

        /* Queue Section */
        .queue-section {
            padding: 60px 0;
            background-color: #fff;
        }

        /* ปรับปรุงการแสดงคิวที่ถูกเรียก */
        #calledQueue {
            min-height: 100px;
        }

        .called-queue-item {
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }


        .called-queue-item i {
            color: #0f5132;
            margin-right: 10px;
        }

        /* ปรับปรุงตารางให้ดูสวยงามขึ้น */
        table.dataTable thead th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }

        table.dataTable tbody td {
            text-align: center;
        }

        table.dataTable tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table.dataTable tbody tr:hover {
            background-color: #e9ecef;
        }

        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .service-card-two .icon {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/SC_Naresuan.png" alt="Logo">
                <span>คณะวิทยาศาสตร์ มหาวิทยาลัยนเรศวร</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"><i class="fas fa-bars"></i></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#">หน้าหลัก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">บริการ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#booking">จองคิว</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-signin" href="login.php">เข้าสู่ระบบ</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-content">
            <h1>ยินดีต้อนรับสู่การจองคิวบริการห้องวิชาการ</h1>
            <p>Welcome to the Academic Room Service Booking</p>
            <a href="#booking" class="btn btn-hero">จองคิวตอนนี้</a>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <h2 class="text-center mb-5">บริการของเรา</h2>
            <div class="row">
                <?php
                // สร้างอาเรย์สำหรับแมปชื่อบริการกับคลาสไอคอน
                $iconMapping = [
                    'สหกิจศึกษาและวิทยานิพนธ์ระดับปริญญาตรี' => 'fas fa-file-alt',
                    'พัฒนาหลักสูตร' => 'fas fa-edit',
                    'การจัดการเรียนการสอน' => 'fa-solid fa-list-check',
                    'บัณฑิตศึกษา ป.โท ป.เอก' => 'fa-solid fa-graduation-cap',
                    'NU งานทะเบียน' => 'fas fa-file-signature',
                    'บริหารนิสิตทุน' => 'fa-solid fa-piggy-bank',
                ];

                $services = $conn->query("SELECT DISTINCT s.service_id, s.service_name 
                FROM services s
                JOIN serviceemployee se ON s.service_id = se.service_id
                WHERE s.service_name != 'admin'");

                while ($row = $services->fetch_assoc()) :
                    $serviceName = $row['service_name'];
                    // ตรวจสอบว่ามีการแมปไอคอนสำหรับชื่อบริการนี้หรือไม่
                    $iconClass = isset($iconMapping[$serviceName]) ? $iconMapping[$serviceName] : 'fas fa-concierge-bell';
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="service-card-two">
                            <div class="icon">
                                <i class="<?php echo $iconClass; ?>"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($serviceName); ?></h5>
                            <p>รายละเอียดเกี่ยวกับบริการนี้...</p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>


    <!-- Booking Section -->
    <section id="booking" class="booking-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="booking-form">
                        <h2 class="text-center mb-4">จองคิวห้องวิชาการ</h2>
                        <p class="text-danger text-center">
                            หมายเหตุ: หากท่านไม่มีรายชื่ออยู่ในคณะวิทยาศาสตร์ โปรดใส่รหัสนิสิต "00000000" ในช่องรหัสนิสิต
                        </p>
                        <form id="queue-form">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">รหัสนิสิต</label>
                                <input type="text" id="student_id" name="student_id" class="form-control" maxlength="8"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">บริการที่ต้องการ</label>
                                <div class="row">
                                    <?php
                                    $serviceCounter = 1;
                                    $services = $conn->query("SELECT DISTINCT s.service_id, s.service_name 
                                    FROM services s
                                    JOIN serviceemployee se ON s.service_id = se.service_id
                                    WHERE s.service_name != 'admin'");

                                    while ($row = $services->fetch_assoc()) :
                                    ?>
                                        <div class="col-6 mb-3">
                                            <div class="service-card" data-service-id="<?php echo $row['service_id']; ?>">
                                                <div class="number-circle">
                                                    <?php echo $serviceCounter; ?>
                                                </div>
                                                <h5><?php echo htmlspecialchars($row['service_name']); ?></h5>
                                            </div>
                                        </div>
                                    <?php
                                        $serviceCounter++;
                                    endwhile;
                                    ?>
                                </div>
                                <input type="hidden" id="service_id" name="service_id" required>
                            </div>
                            <button type="submit" class="btn btn-booking w-100" disabled>ยืนยันการจองคิว</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Queue Display Section -->
    <section class="queue-section">
        <div class="container">
            <h2 class="text-center mb-5">คิวที่ถูกเรียก</h2>
            <div id="calledQueue" class="fs-4 text-center mb-5">
                <!-- Called queue items will be inserted here by JavaScript -->
            </div>
            <h2 class="text-center mb-4">รายการคิวทั้งหมด</h2>
            <div class="table-responsive">
                <table id="queueTable" class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>หมายเลขคิว</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>บริการ</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Queue data will be inserted here by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>




    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <img src="assets/img/nu.png" alt="NU Logo" class="footer-logo" />
            <div class="footer-info">
                <p>คณะวิทยาศาสตร์ มหาวิทยาลัยนเรศวร</p>
                <p>ที่อยู่: 99 หมู่ 9 ตำบลท่าโพธิ์ อำเภอเมือง จังหวัดพิษณุโลก 65000</p>
                <p>โทรศัพท์: 055-963112 | โทรสาร: 055-963113</p>
                <p>Email: saraban_sci@nu.ac.th</p>
            </div>
            <div class="social-icons mt-3">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-line"></i></a>
                <a href="#"><i class="fas fa-globe"></i></a>
            </div>
        </div>
    </footer>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-body">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h5 class="mb-2">คุณได้คิวที่ <span id="queueNumber"></span></h5>
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-body">
                    <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                    <h5 class="mb-2" id="errorModalMessage">ดูเหมือนบางอย่างผิดพลาด</h5>
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery (จำเป็นสำหรับ DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const studentIdField = document.getElementById('student_id');
            const fullNameField = document.getElementById('full_name');
            const submitButton = document.querySelector('button[type="submit"]');
            const calledQueueBox = document.getElementById('calledQueue');
            const serviceCards = document.querySelectorAll('.service-card');
            const serviceIdInput = document.getElementById('service_id');
            const queueNumberSpan = document.getElementById('queueNumber');

            let selectedServiceId = null;
            let queueReadCounts = {}; // ติดตามจำนวนครั้งที่แต่ละคิวถูกอ่าน
            const maxReads = 2; // จำนวนครั้งสูงสุดที่อ่านคิวแต่ละรายการ
            let queueData = []; // เก็บข้อมูลคิวที่ดึงมาจากเซิร์ฟเวอร์

            // Initialize DataTable
            let queueTable = $('#queueTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json"
                },
                "paging": true,
                "searching": true,
                "ordering": true,
                "order": [
                    [0, "desc"]
                ], // เรียงตามหมายเลขคิวจากมากไปน้อย
                "columns": [{
                        "data": "daily_queue_number"
                    },
                    {
                        "data": "full_name"
                    },
                    {
                        "data": "service_name"
                    },
                    {
                        "data": "status",
                        "render": function(data, type, row) {
                            if (data === 'Waiting') {
                                return 'รอเรียก';
                            } else if (data === 'Called') {
                                return 'ถูกเรียก';
                            } else {
                                return data; // คืนค่าตามเดิมหากมีสถานะอื่นๆ
                            }
                        }
                    }
                    // ลบคอลัมน์ time ออกจาก DataTables
                    // { "data": "time" }
                ],
                "responsive": true,
                "autoWidth": false,
                "lengthMenu": [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "ทั้งหมด"]
                ],
                "pageLength": 10,
                "dom": '<"top"f>rt<"bottom"lip><"clear">'
            });

            // ส่วนของการจัดการการเลือกบริการ
            serviceCards.forEach(card => {
                card.addEventListener('click', function() {
                    // ลบคลาส selected จากทุกการ์ด
                    serviceCards.forEach(c => c.classList.remove('selected'));

                    // เพิ่มคลาส selected ให้กับการ์ดที่ถูกคลิก
                    this.classList.add('selected');

                    // ตั้งค่า service_id ที่ถูกเลือก
                    selectedServiceId = this.getAttribute('data-service-id');
                    serviceIdInput.value = selectedServiceId;

                    // ตรวจสอบว่ารหัสนิสิตถูกต้องหรือไม่
                    if (studentIdField.value.length === 8) {
                        submitButton.disabled = false;
                    }
                });
            });

            studentIdField.addEventListener('input', function() {
                fetchFullName();
                if (studentIdField.value.length === 8 && selectedServiceId) {
                    studentIdField.classList.add('border-success');
                    submitButton.disabled = false;
                } else {
                    studentIdField.classList.remove('border-success');
                    submitButton.disabled = true;
                }
            });

            function fetchFullName() {
                const studentId = studentIdField.value;
                if (studentId.length === 8) {
                    fetch('fetch_name.php?student_id=' + studentId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                fullNameField.value = data.full_name;
                                if (selectedServiceId) {
                                    submitButton.disabled = false;
                                }
                            } else {
                                fullNameField.value = "ไม่พบข้อมูล";
                                submitButton.disabled = true;
                                showErrorModal(data.message);
                            }
                        });
                } else {
                    fullNameField.value = "";
                    submitButton.disabled = true;
                }
            }

            document.getElementById('queue-form').addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);

                fetch('process_queue.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.type === 'success') {
                            // ตั้งค่าหมายเลขคิวในโมดัล
                            queueNumberSpan.textContent = data.queue_number;
                            showSuccessModal();
                            this.reset();
                            submitButton.disabled = true;
                            fullNameField.value = "";
                            // รีเซ็ตการ์ดบริการ
                            serviceCards.forEach(c => c.classList.remove('selected'));
                            serviceIdInput.value = "";
                            selectedServiceId = null;
                            // รีเฟรช DataTable
                            fetchQueueData();
                        } else {
                            showErrorModal(data.message);
                        }
                    });
            });

            function fetchQueueData() {
                fetch('fetch_queue.php')
                    .then(response => response.json())
                    .then(data => {
                        queueData = data; // เก็บข้อมูลคิวที่ดึงมา
                        updateQueueTable();
                        updateCalledQueue(calledQueues(data));
                    });
            }

            function updateQueueTable() {
                queueTable.clear();
                queueTable.rows.add(queueData);
                queueTable.draw();
            }

            function calledQueues(data) {
                return data.filter(queue => queue.status === "Called");
            }

            function updateCalledQueue(calledQueues) {
                if (calledQueues.length > 0) {
                    calledQueueBox.innerHTML = ''; // ล้างเนื้อหาเก่า
                    calledQueues.forEach(queue => {
                        const queueItem = document.createElement('div');
                        queueItem.classList.add('called-queue-item');
                        queueItem.innerHTML = `
                    <div>
                        <i class="fas fa-bullhorn"></i>
                        <strong>คิวที่ ${queue.daily_queue_number}</strong>: ${queue.full_name} - ${queue.service_name}
                    </div>
                `;
                        calledQueueBox.appendChild(queueItem);

                        // Initialize or update read count for this queue ID
                        if (!queueReadCounts[queue.queue_id]) {
                            queueReadCounts[queue.queue_id] = 0;
                        }

                        if (queueReadCounts[queue.queue_id] < maxReads) {
                            const utterance = new SpeechSynthesisUtterance();
                            utterance.lang = 'th-TH'; // ภาษาไทย
                            utterance.text = `ขอเชิญคิวที่ ${queue.daily_queue_number} ${queue.full_name} ไปที่ ${queue.service_name}`;
                            window.speechSynthesis.speak(utterance);

                            queueReadCounts[queue.queue_id]++;
                        }
                    });
                } else {
                    calledQueueBox.innerHTML = '<p class="text-muted">ไม่มีคิวที่ถูกเรียก</p>';
                }
            }

            // Initial fetch
            fetchQueueData();
            setInterval(fetchQueueData, 2000);

            // Initialize tooltips and popovers
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });

        function showSuccessModal() {
            const successModalElement = document.getElementById('successModal');
            if (successModalElement) {
                const successModal = new bootstrap.Modal(successModalElement);
                successModal.show();
            } else {
                console.error('Success modal element not found!');
            }
        }

        function showErrorModal(message = 'ไม่พบข้อมูล') {
            const errorModalElement = document.getElementById('errorModal');
            if (errorModalElement) {
                const errorModal = new bootstrap.Modal(errorModalElement);
                const errorModalMessage = errorModalElement.querySelector('h5');
                if (errorModalMessage) {
                    errorModalMessage.textContent = message;
                }
                errorModal.show();
            } else {
                console.error('Error modal element not found!');
            }
        }
    </script>


</body>

</html>