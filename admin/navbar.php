<!-- admin/navbar.php -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <!-- Left section -->
    <a class="navbar-brand" href="#">Academic Room</a>

    <!-- Center section -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-center p-2" id="navbarNav">
      <ul class="navbar-nav">
        <!-- Navigation links -->
        <li class="nav-item mx-3">
          <a class="nav-link <?php if($activePage == 'dashboard') echo 'active'; ?>" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mx-3">
          <a class="nav-link <?php if($activePage == 'queue_history') echo 'active'; ?>" href="queue_history.php">Queue History</a>
        </li>
        <li class="nav-item mx-3">
          <a class="nav-link <?php if($activePage == 'employees') echo 'active'; ?>" href="employees.php">Employees</a>
        </li>
        <li class="nav-item mx-3">
          <a class="nav-link <?php if($activePage == 'students') echo 'active'; ?>" href="students.php">Students</a>
        </li>
        <li class="nav-item mx-3">
          <a class="nav-link <?php if($activePage == 'services') echo 'active'; ?>" href="services.php">Services</a>
        </li>
      </ul>
    </div>

    <!-- Right section -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a href="../logout.php" class="btn btn-danger">Logout</a>
      </li>
    </ul>
  </div>
</nav>
