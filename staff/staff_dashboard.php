<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['access_level'] !== 'employee') {
    header("Location: ../login.php");
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
    <title>Staff Dashboard</title>
    <link id="favicon" rel="icon" href="favicon.ico" type="image/x-icon">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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

        .notification {
            background-color: #ffcc00;
            padding: 1rem;
            border-radius: 10px;
            color: #343a40;
            margin-bottom: 1.5rem;
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
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
    </style>
    <script>
        let notificationsEnabled = false; // เริ่มต้นให้การแจ้งเตือนปิดอยู่
        let notifiedQueueIds = new Set(); // เก็บ queue_id ที่เคยแจ้งเตือนไปแล้ว
        let originalTitle = document.title; // เก็บชื่อ title เดิม

        function playSound() {
            if (notificationsEnabled) {
                const audio = new Audio('notification.mp3'); // เปลี่ยนเป็นไฟล์เสียงที่คุณต้องการ
                audio.play();
            }
        }

        function changeTitle(newTitle) {
            document.title = newTitle;
        }

        function resetTitle() {
            document.title = originalTitle;
        }

        window.addEventListener('focus', function () {
            resetTitle(); // เปลี่ยน title กลับเมื่อผู้ใช้กดเข้ามาที่หน้าเว็บ
        });

        document.addEventListener('DOMContentLoaded', function () {
            const toggleNotificationBtn = document.getElementById('toggleNotificationBtn');

            if (toggleNotificationBtn) {
                toggleNotificationBtn.addEventListener('click', function () {
                    notificationsEnabled = !notificationsEnabled;
                    this.textContent = notificationsEnabled ? 'ปิดการแจ้งเตือน' : 'เปิดการแจ้งเตือน';
                });
            }

            fetchQueueData(); // Initial fetch
            setInterval(fetchQueueData, 5000); // Refresh every 5 seconds
        });

        function fetchQueueData() {
            fetch('fetch_employee_queues.php')
                .then(response => response.json())
                .then(data => {
                    const queueTableBody = document.getElementById('queue-table-body');
                    const notificationBox = document.getElementById('notification-box');
                    queueTableBody.innerHTML = '';

                    data.forEach(queue => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${queue.daily_queue_number}</td>
                            <td>${queue.full_name}</td>
                            <td>${queue.service_name}</td>
                            <td>${queue.queue_time}</td>
                            <td>${queue.status}</td>
                            <td>
                                <button class="btn btn-custom btn-success btn-sm" onclick="updateStatus(${queue.queue_id}, 'Called')">เรียก</button>
                                <button class="btn btn-custom btn-danger btn-sm" onclick="updateStatus(${queue.queue_id}, 'Not Coming')">ไม่มา</button>
                                <button class="btn btn-custom btn-primary btn-sm" onclick="updateStatus(${queue.queue_id}, 'Received')">รับแล้ว</button>
                            </td>
                        `;
                        queueTableBody.appendChild(row);

                        if (queue.status === 'Waiting' && !notifiedQueueIds.has(queue.queue_id)) {
                            notifiedQueueIds.add(queue.queue_id); // เพิ่ม queue_id ไปยัง set
                            if (notificationsEnabled) {
                                playSound(); // เล่นเสียงแจ้งเตือนเมื่อมีคิวใหม่ที่เป็น Waiting
                                notificationBox.style.display = 'block';
                                notificationBox.innerHTML = `<i class="fas fa-bell"></i> คุณมีคิวใหม่!`;
                                changeTitle('คุณมีคิวใหม่!'); // เปลี่ยน title เป็น "คุณมีคิวใหม่!"

                                setTimeout(() => {
                                    notificationBox.style.display = 'none';
                                }, 3000); // ซ่อนการแจ้งเตือนหลังจาก 3 วินาที
                            }
                        }
                    });
                });
        }

        function updateStatus(queueId, newStatus) {
            fetch('update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        queue_id: queueId,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchQueueData(); // Refresh the queue list
                    } else {
                        alert('Failed to update status');
                    }
                });
        }
    </script>
</head>

<body>
    <div class="wrapper">
        <nav class="nav d-flex justify-content-between align-items-center">
            <div class="nav-logo">
                <img src="../assets/img/SC_Naresuan.png" alt="Logo">
            </div>
            <div class="nav-menu">
                <ul>
                    <li><a href="staff_dashboard.php" class="link active">Queue</a></li>
                    <li><a href="queue_history.php" class="link">History</a></li>
                    <li><a href="logout.php" class="link">Logout</a></li>
                </ul>
            </div>
        </nav>
        <div class="container mt-5">
            <div id="notification-box" class="notification"></div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="welcome-text">ยินดีต้อนรับ <?php echo htmlspecialchars($employee['employee_name']); ?></h1>
                <button id="toggleNotificationBtn" class="btn btn-success">เปิดการแจ้งเตือน</button>
            </div>

            <h2 class="text-dark mb-4">รายการคิวของคุณ</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ลำดับคิว</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>บริการ</th>
                        <th>เวลา</th>
                        <th>สถานะ</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="queue-table-body">
                    <!-- Queue data will be inserted here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
