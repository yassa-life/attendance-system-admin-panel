<?php if ($_SESSION['role'] === 'user') : ?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item">
      <a class="nav-link" href="leave.php">
        <i class="icon-plus menu-icon"></i>
        <span class="menu-title">Apply Leave</span>
      </a>
    </li>
    
    <!-- Logout Button -->
    <li class="nav-item mt-auto" style="position: absolute; bottom: 20px; width: calc(100% - 30px);">
      <a class="nav-link" href="#" onclick="logout()">
        <i class="icon-logout menu-icon text-danger"></i>
        <span class="menu-title text-danger">Logout</span>
      </a>
    </li>
  </ul>
</nav>
<?php else : ?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item">
      <a class="nav-link" href="dashboard.php">
        <i class="icon-home menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="leave.php">
        <i class="icon-plus menu-icon"></i>
        <span class="menu-title">Apply Leave</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="approve.php">
        <i class="icon-check menu-icon"></i>
        <span class="menu-title">Approve Leave</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="manage.php">
        <i class="icon-people menu-icon"></i>
        <span class="menu-title">Employee</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="id.php">
        <i class="icon-tag menu-icon"></i>
        <span class="menu-title">Assign ID</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="work.php">
        <i class="icon-briefcase menu-icon"></i>
        <span class="menu-title">Work Hours</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="face.php">
        <i class="icon-user menu-icon"></i>
        <span class="menu-title">Face Data</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="reset.php">
        <i class="icon-hourglass menu-icon"></i>
        <span class="menu-title">Reset Pwd</span>
      </a>
    </li>
    
    <!-- Logout Button -->
    <li class="nav-item" style="position: absolute; bottom: 20px; ">
      <a class="nav-link" href="#" onclick="logout()">
        <i class="icon-logout menu-icon text-danger"></i>
        <span class="menu-title text-danger">Logout</span>
      </a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<!-- logout.php (should be a separate file) -->
<script>
function logout() {
    // Simple confirmation dialog
    if (confirm('Are you sure you want to logout?')) {
        // AJAX call to logout.php
        fetch('process/logout.php')
            .then(response => {
                if (response.ok) {
                    // Redirect to login page after successful logout
                    window.location.href = 'index.php';
                } else {
                    alert('Logout failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during logout.');
            });
    }
}
</script>