<?php include_once ('includes/check_session.php'); ?>
<?php include ('includes/dbconnection.php'); ?>
<?php include_once ('includes/header.php'); ?>

<div class="container-fluid page-body-wrapper">
  <?php include_once ('includes/sidebar.php'); ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="page-header">
        <h3 class="page-title">Leave Approvals</h3>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Leave Approvals</li>
          </ol>
        </nav>
      </div>

      <?php
      // Process approval/rejection
      if(isset($_POST['action'])) {
          $leave_id = mysqli_real_escape_string($conn, $_POST['leave_id']);
          $action = mysqli_real_escape_string($conn, $_POST['action']);
          $admin_id = $_SESSION['admin_id']; // Get admin ID from session
          
          // Update leave application
          $sql = "UPDATE leave_application SET 
                  status = '$action',
                  approevs_by = '$admin_id'
                  WHERE leave_id = '$leave_id'";
          
          if(mysqli_query($conn, $sql)) {
              $message = "Leave application has been " . $action . " successfully!";
              echo '<div class="alert alert-success">' . $message . '</div>';
          } else {
              echo '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
          }
      }
      ?>

      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Pending Leave Applications</h4>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Employee</th>
                      <th>Leave Type</th>
                      <th>Leave Date</th>
                      <th>Reason</th>
                      <th>Applied On</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT leave_application.*, 
                           CONCAT(users.first_name, ' ', users.last_name) as employee_name
                           FROM leave_application
                           JOIN users ON leave_application.user_id = users.user_id
                           WHERE leave_application.status = 'pending'
                           ORDER BY leave_application.applied_at DESC";
                    
                    $result = mysqli_query($conn, $sql);
                    
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
                        echo "<td>" . ucfirst($row['leave_type']) . "</td>";
                        echo "<td>" . date('d M Y', strtotime($row['leave_date'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['reason']) . "</td>";
                        echo "<td>" . date('d M Y', strtotime($row['applied_at'])) . "</td>";
                        echo "<td>
                                <form method='post' style='display:inline;'>
                                  <input type='hidden' name='leave_id' value='" . $row['leave_id'] . "'>
                                  <button type='submit' name='action' value='approved' class='btn btn-success btn-sm'>Approve</button>
                                </form>
                                <form method='post' style='display:inline; margin-left:5px;'>
                                  <input type='hidden' name='leave_id' value='" . $row['leave_id'] . "'>
                                  <button type='submit' name='action' value='rejected' class='btn btn-danger btn-sm'>Reject</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    
                    if(mysqli_num_rows($result) == 0) {
                        echo "<tr><td colspan='6' class='text-center'>No pending leave applications</td></tr>";
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
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Recently Processed Leaves</h4>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Employee</th>
                      <th>Leave Type</th>
                      <th>Leave Date</th>
                      <th>Status</th>
                      <th>Processed By</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT leave_application.*, 
                           CONCAT(users.first_name, ' ', users.last_name) as employee_name,
                           administration.username as admin_name
                           FROM leave_application
                           JOIN users ON leave_application.user_id = users.user_id
                           LEFT JOIN administration ON leave_application.approevs_by = administration.admin_id
                           WHERE leave_application.status != 'pending'
                           ORDER BY leave_application.applied_at DESC
                           LIMIT 10";
                    
                    $result = mysqli_query($conn, $sql);
                    
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
                        echo "<td>" . ucfirst($row['leave_type']) . "</td>";
                        echo "<td>" . date('d M Y', strtotime($row['leave_date'])) . "</td>";
                        echo "<td><span class='badge badge-" . 
                             ($row['status'] == 'approved' ? 'success' : 'danger') . "'>" . 
                             ucfirst($row['status']) . "</span></td>";
                        echo "<td>" . ($row['admin_name'] ? htmlspecialchars($row['admin_name']) : 'N/A') . "</td>";
                        echo "</tr>";
                    }
                    
                    if(mysqli_num_rows($result) == 0) {
                        echo "<tr><td colspan='5' class='text-center'>No processed leave applications</td></tr>";
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include_once ('includes/footer.php'); ?>
  </div>
</div>