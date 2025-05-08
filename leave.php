<?php
ob_start(); // Start output buffering
include_once('includes/check_session.php');
include('includes/dbconnection.php');
include_once('includes/header.php');

// After redirect (at the top of the page)
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']); // Clear the message
}
?>

<div class="container-fluid page-body-wrapper">
  <?php include_once('includes/sidebar.php'); ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="page-header">
        <h3 class="page-title"> Leave Application </h3>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Leave Application</li>
          </ol>
        </nav>
      </div>
      
      <?php
      // Handle form submission
      if (isset($_POST['submit'])) {
          if (isset($_SESSION['admin_id'])) {
              $admin_id = $_SESSION['admin_id'];
              $user_query = "SELECT user_id FROM administration WHERE admin_id = '$admin_id' LIMIT 1";
              $user_result = mysqli_query($conn, $user_query);

              if ($user_result && mysqli_num_rows($user_result) > 0) {
                  $user_row = mysqli_fetch_assoc($user_result);
                  $user_id = $user_row['user_id'];
              } else {
                  echo '<div class="alert alert-danger">Error: Unable to fetch user ID for the admin.</div>';
                  exit;
              }
          } else {
              $user_id = $_SESSION['user_id'];
          }

          $leave_type = mysqli_real_escape_string($conn, $_POST['leaveType']);
          $leave_date = mysqli_real_escape_string($conn, $_POST['leaveDate']);
          $reason = mysqli_real_escape_string($conn, $_POST['reason']);
          $applied_at = date('Y-m-d');
          $status = 'pending';

          if (strtotime($leave_date) < strtotime(date('Y-m-d'))) {
              echo '<div class="alert alert-danger">Leave date cannot be in the past.</div>';
          } else {
              $sql = "INSERT INTO leave_application 
                      (user_id, leave_type, leave_date, reason, status, applied_at) 
                      VALUES 
                      ('$user_id', '$leave_type', '$leave_date', '$reason', '$status', '$applied_at')";

              if (mysqli_query($conn, $sql)) {
                  // Before redirect
                  $_SESSION['success_message'] = "Leave application submitted successfully!";
                  header("Location: " . $_SERVER['PHP_SELF']);
                  exit;
              } else {
                  echo '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
              }
          }
      }
      ?>
      
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Apply for Leave</h4>
              <form class="forms-sample" method="post" action="" id="leaveForm">
                <div class="form-group">
                  <label for="userName">Employee Name</label>
                  <input type="text" class="form-control" id="userName" name="userName" 
                         value="<?php 
                         if (isset($_SESSION['admin_id'])) {
                           echo htmlspecialchars($_SESSION['admin_id'] . ' ' . $_SESSION['username']);
                         } else {
                           echo htmlspecialchars($_SESSION['user_id'] . ' ' . $_SESSION['username']);
                         } ?>" readonly>
                </div>
                <div class="form-group">
                  <label for="leaveType">Leave Type</label>
                  <select class="form-control" id="leaveType" name="leaveType" required>
                    <option value="">Select Leave Type</option>
                    <option value="sick">Sick Leave</option>
                    <option value="casual">Casual Leave</option>
                    <option value="annual">Annual Leave</option>
                    <option value="maternity">Maternity Leave</option>
                    <option value="paternity">Paternity Leave</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="leaveDate">Leave Date</label>
                  <input type="date" class="form-control" id="leaveDate" name="leaveDate" 
                         min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                  <label for="reason">Reason</label>
                  <textarea class="form-control" id="reason" name="reason" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary mr-2" name="submit">Submit</button>
                <button type="reset" class="btn btn-light">Cancel</button>
              </form>
            </div>
          </div>
        </div>
        
        <!-- Display pending leave applications -->
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
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    // Fetch pending leave applications
                    $logged_in_user_id = null;

                    // Check if the logged-in user is an admin
                    if (isset($_SESSION['admin_id'])) {
                        $admin_id = $_SESSION['admin_id'];
                        $user_query = "SELECT user_id FROM administration WHERE admin_id = '$admin_id' LIMIT 1";
                        $user_result = mysqli_query($conn, $user_query);

                        if ($user_result && mysqli_num_rows($user_result) > 0) {
                            $user_row = mysqli_fetch_assoc($user_result);
                            $logged_in_user_id = $user_row['user_id'];
                        } else {
                            echo '<div class="alert alert-danger">Error: Unable to fetch user ID for the admin.</div>';
                            exit;
                        }
                    } else {
                        // If not an admin, use the logged-in user's ID
                        $logged_in_user_id = $_SESSION['user_id'];
                    }

                    // Fetch pending leave applications for the logged-in user
                    $pending_query = "SELECT la.*, u.first_name, u.last_name 
                                      FROM leave_application la
                                      JOIN users u ON la.user_id = u.user_id
                                      WHERE la.status = 'pending' AND la.user_id = '$logged_in_user_id'
                                      ORDER BY la.leave_date";
                    $pending_result = mysqli_query($conn, $pending_query);

                    while ($leave = mysqli_fetch_assoc($pending_result)) {
                        echo '<tr>
                                <td>' . htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']) . '</td>
                                <td>' . ucfirst($leave['leave_type']) . '</td>
                                <td>' . $leave['leave_date'] . '</td>
                                <td>' . nl2br(htmlspecialchars($leave['reason'])) . '</td>
                                <td>' . $leave['applied_at'] . '</td>
                                <td><span class="badge badge-warning">' . ucfirst($leave['status']) . '</span></td>
                              </tr>';
                    }

                    if (mysqli_num_rows($pending_result) == 0) {
                        echo '<tr><td colspan="6" class="text-center">No pending leave applications</td></tr>';
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
    <?php include_once('includes/footer.php'); ?>
    <?php
    include_once('includes/footer.php');
    ?>
  </div>
</div>

<script>
// JavaScript to prevent past dates and weekends
document.getElementById('leaveDate').addEventListener('change', function() {
    var today = new Date().toISOString().split('T')[0];
    var selectedDate = new Date(this.value);
    var dayOfWeek = selectedDate.getUTCDay();
    
    if(this.value < today) {
        alert('Leave date cannot be in the past');
        this.value = today;
    } else if(dayOfWeek === 0 || dayOfWeek === 6) {
        alert('Weekend dates are not allowed for leave');
        this.value = today;
    }
});
</script>