<?php include_once ('includes/check_session.php'); ?>
<?php include ('includes/dbconnection.php'); ?>
<?php include_once ('includes/header.php'); ?>

<div class="container-fluid page-body-wrapper">
  <?php include_once ('includes/sidebar.php'); ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="page-header">
        <h3 class="page-title"> Manage Employees </h3>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Employees</li>
          </ol>
        </nav>
      </div>

      <?php
      // Handle form submissions
      if (isset($_POST['add_employee'])) {
          // Add new employee
          $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
          $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
          $email = mysqli_real_escape_string($conn, $_POST['email']);
          $phone = mysqli_real_escape_string($conn, $_POST['phone']);
          $position = mysqli_real_escape_string($conn, $_POST['position']);
          $working_start = mysqli_real_escape_string($conn, $_POST['working_start']);
          $working_end = mysqli_real_escape_string($conn, $_POST['working_end']);
          
          $sql = "INSERT INTO users (first_name, last_name, email, phone, position, working_hours_start, working_hours_end, is_active) 
                  VALUES ('$first_name', '$last_name', '$email', '$phone', '$position', '$working_start', '$working_end', 1)";
          
          if(mysqli_query($conn, $sql)) {
              echo '<div class="alert alert-success">Employee added successfully!</div>';
          } else {
              echo '<div class="alert alert-danger">Error adding employee: ' . mysqli_error($conn) . '</div>';
          }
      }

      if (isset($_POST['update_employee'])) {
          // Update employee
          $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
          $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
          $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
          $email = mysqli_real_escape_string($conn, $_POST['email']);
          $phone = mysqli_real_escape_string($conn, $_POST['phone']);
          $position = mysqli_real_escape_string($conn, $_POST['position']);
          $working_start = mysqli_real_escape_string($conn, $_POST['working_start']);
          $working_end = mysqli_real_escape_string($conn, $_POST['working_end']);
          $is_active = isset($_POST['is_active']) ? 1 : 0;
          
          $sql = "UPDATE users SET 
                  first_name = '$first_name',
                  last_name = '$last_name',
                  email = '$email',
                  phone = '$phone',
                  position = '$position',
                  working_hours_start = '$working_start',
                  working_hours_end = '$working_end',
                  is_active = '$is_active'
                  WHERE user_id = '$user_id'";
          
          if(mysqli_query($conn, $sql)) {
              echo '<div class="alert alert-success">Employee updated successfully!</div>';
          } else {
              echo '<div class="alert alert-danger">Error updating employee: ' . mysqli_error($conn) . '</div>';
          }
      }

      if (isset($_GET['deactivate'])) {
          // Deactivate employee and remove login credentials
          $user_id = mysqli_real_escape_string($conn, $_GET['deactivate']);

          // Start transaction
          mysqli_begin_transaction($conn);

          try {
              // 1. Update user status to inactive
              $sql1 = "UPDATE users SET is_active = 0 WHERE user_id = '$user_id'";
              mysqli_query($conn, $sql1);

              // 2. Check if user is admin
              $sql_check_admin = "SELECT * FROM administration WHERE user_id = '$user_id'";
              $result = mysqli_query($conn, $sql_check_admin);

              if (mysqli_num_rows($result) > 0) {
                  // 3a. Remove admin login if exists
                  $sql2 = "DELETE FROM administration WHERE user_id = '$user_id'";
              } else {
                  // 3b. Remove regular user login if exists
                  $sql2 = "DELETE FROM user_lo WHERE user_id = '$user_id'";
              }

              mysqli_query($conn, $sql2);

              // Commit transaction
              mysqli_commit($conn);

              echo '<div class="alert alert-success">Employee deactivated and login credentials removed successfully!</div>';
          } catch (Exception $e) {
              // Rollback transaction on error
              mysqli_rollback($conn);
              echo '<div class="alert alert-danger">Error deactivating employee: ' . $e->getMessage() . '</div>';
          }
      }

      if (isset($_GET['activate'])) {
          // Reactivate employee (doesn't restore login credentials)
          $user_id = mysqli_real_escape_string($conn, $_GET['activate']);

          $sql = "UPDATE users SET is_active = 1 WHERE user_id = '$user_id'";

          if (mysqli_query($conn, $sql)) {
              echo '<div class="alert alert-success">Employee activated successfully! Note: Login credentials must be recreated separately.</div>';
          } else {
              echo '<div class="alert alert-danger">Error activating employee: ' . mysqli_error($conn) . '</div>';
          }
      }
      ?>

      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Employee List</h4>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Position</th>
                      <th>Working Hours</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT * FROM users ORDER BY is_active DESC, first_name";
                    $result = mysqli_query($conn, $sql);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['user_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['position']) . "</td>";
                        echo "<td>" . substr($row['working_hours_start'], 0, 5) . " - " . substr($row['working_hours_end'], 0, 5) . "</td>";
                        echo "<td><span class='badge badge-" . ($row['is_active'] ? 'success' : 'danger') . "'>" . ($row['is_active'] ? 'Active' : 'Inactive') . "</span></td>";
                        echo "<td>";

                        // Edit button always shown
                        echo "<button class='btn btn-primary btn-sm edit-btn' data-id='" . $row['user_id'] . "' 
                                data-first='" . htmlspecialchars($row['first_name']) . "'
                                data-last='" . htmlspecialchars($row['last_name']) . "'
                                data-email='" . htmlspecialchars($row['email']) . "'
                                data-phone='" . htmlspecialchars($row['phone']) . "'
                                data-position='" . htmlspecialchars($row['position']) . "'
                                data-start='" . $row['working_hours_start'] . "'
                                data-end='" . $row['working_hours_end'] . "'
                                data-active='" . $row['is_active'] . "'>Edit</button>";

                        // Show activate/deactivate based on current status
                        if ($row['is_active']) {
                            echo "<a href='manage.php?deactivate=" . $row['user_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to deactivate this employee? This will remove their login credentials.\")'>Deactivate</a>";
                        } else {
                            echo "<a href='manage.php?activate=" . $row['user_id'] . "' class='btn btn-success btn-sm'>Activate</a>";
                        }

                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title" id="form-title">Add New Employee</h4>
              <form class="forms-sample" method="post" action="">
                <input type="hidden" name="user_id" id="user_id">
                <div class="form-group">
                  <label for="first_name">First Name</label>
                  <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                  <label for="last_name">Last Name</label>
                  <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                  <label for="phone">Phone</label>
                  <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="form-group">
                  <label for="position">Position</label>
                  <input type="text" class="form-control" id="position" name="position" required>
                </div>
                <div class="form-group">
                  <label for="working_start">Working Hours Start</label>
                  <input type="time" class="form-control" id="working_start" name="working_start" value="08:00" required>
                </div>
                <div class="form-group">
                  <label for="working_end">Working Hours End</label>
                  <input type="time" class="form-control" id="working_end" name="working_end" value="17:00" required>
                </div>
                <div class="form-group">
                  <div class="form-check">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked> Active
                    </label>
                  </div>
                </div>
                <button type="submit" class="btn btn-primary mr-2" name="add_employee" id="submit-btn">Add Employee</button>
                <button type="button" class="btn btn-light" id="cancel-edit" style="display:none;">Cancel</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include_once ('includes/footer.php'); ?>
  </div>
</div>

<script>
// Handle edit button clicks
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const firstName = this.getAttribute('data-first');
        const lastName = this.getAttribute('data-last');
        const email = this.getAttribute('data-email');
        const phone = this.getAttribute('data-phone');
        const position = this.getAttribute('data-position');
        const start = this.getAttribute('data-start');
        const end = this.getAttribute('data-end');
        const active = this.getAttribute('data-active');
        
        // Fill the form
        document.getElementById('user_id').value = id;
        document.getElementById('first_name').value = firstName;
        document.getElementById('last_name').value = lastName;
        document.getElementById('email').value = email;
        document.getElementById('phone').value = phone;
        document.getElementById('position').value = position;
        document.getElementById('working_start').value = start;
        document.getElementById('working_end').value = end;
        document.getElementById('is_active').checked = active === '1';
        
        // Change form mode
        document.getElementById('form-title').textContent = 'Edit Employee';
        document.getElementById('submit-btn').textContent = 'Update Employee';
        document.getElementById('submit-btn').name = 'update_employee';
        document.getElementById('cancel-edit').style.display = 'inline-block';
    });
});

// Handle cancel edit
document.getElementById('cancel-edit').addEventListener('click', function() {
    resetForm();
});

function resetForm() {
    document.getElementById('user_id').value = '';
    document.getElementById('form-title').textContent = 'Add New Employee';
    document.getElementById('submit-btn').textContent = 'Add Employee';
    document.getElementById('submit-btn').name = 'add_employee';
    document.getElementById('cancel-edit').style.display = 'none';
    document.querySelector('form').reset();
    document.getElementById('working_start').value = '08:00';
    document.getElementById('working_end').value = '17:00';
    document.getElementById('is_active').checked = true;
}

function confirmDeactivation(userId) {
    if (confirm("Are you sure you want to deactivate this employee?")) {
        window.location.href = 'manage_employees.php?delete=' + userId;
    }
}
</script>