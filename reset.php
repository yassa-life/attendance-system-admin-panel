<?php 
include_once ('includes/check_session.php');
include ('includes/dbconnection.php');
include_once ('includes/header.php');

// Function to generate 8-character alphanumeric code
function generateResetCode() {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $code = '';
    for ($i = 0; $i < 8; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}

// Fetch all users for the dropdown
$users_query = "SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) AS full_name, u.email, ul.username 
                FROM users u 
                JOIN user_lo ul ON u.user_id = ul.user_id 
                ORDER BY u.first_name ASC";
$users_result = $conn->query($users_query);
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[] = $row;
}

// Process password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);

    // Verify user exists
    $stmt = $conn->prepare("SELECT u.user_id, u.email, ul.id, ul.username 
                           FROM users u 
                           JOIN user_lo ul ON u.user_id = ul.user_id 
                           WHERE u.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Generate 8-character code
        $token = generateResetCode();
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Update database
        $update_stmt = $conn->prepare("UPDATE user_lo 
                                      SET rst_pwd = ?, rst_pwd_expiry = ? 
                                      WHERE id = ?");
        $update_stmt->bind_param("ssi", $token, $expiry, $user['id']);
        
        if ($update_stmt->execute()) {
            // Prepare data for mail.php
            $_SESSION['mail_data'] = [
                'to' => $user['email'],
                'subject' => 'Password Reset Code',
                'message' => "Hello " . htmlspecialchars($user['username']) . ",\n\n" .
                            "Here is your password reset code:  " . $token . "\n\n" .
                            "This code will expire on " . date('M j, Y g:i A', strtotime($expiry)) . ".\n\n" .
                            "You can reset your password using the following link:\n" .
                            "http://localhost/db/attendace/change_pwd.php",
                'headers' => 'From: no-reply@yourdomain.com'
            ];

            // Redirect to mail handler
            header("Location: vendor/mail.php");
            exit();
        } else {
            $_SESSION['error'] = "Database error: Could not update reset code";
        }
    } else {
        $_SESSION['error'] = "No account found for the selected user.";
    }
    header("Location: reset.php");
    exit();
}
?>

<div class="container-fluid page-body-wrapper">
  <?php include_once ('includes/sidebar.php'); ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="page-header">
        <h3 class="page-title"> Password Reset </h3>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Password Reset</li>
          </ol>
        </nav>
      </div>

      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Reset User Password</h4>
              
              <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
              <?php endif; ?>

              <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
              <?php endif; ?>

              <form class="forms-sample" method="POST">
                <div class="form-group">
                  <label for="user_id">Select User</label>
                  <select class="form-control" id="user_id" name="user_id" required>
                    <option value="">-- Select User --</option>
                    <?php foreach ($users as $user): ?>
                      <option value="<?= $user['user_id']; ?>">
                        <?= htmlspecialchars($user['full_name']) . " (" . htmlspecialchars($user['email']) . ")" ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                
                <div class="mt-4">
                  <button type="submit" class="btn btn-primary mr-2">Generate Reset Code</button>
                  <button type="button" class="btn btn-light" onclick="window.location.href='dashboard.php'">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Reset Code Requests Table -->
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Recent Reset Code Requests</h4>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Email</th>
                      <th>Code Generated</th>
                      <th>Expires At</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) as name, 
                            u.email, ul.rst_pwd, ul.rst_pwd_expiry,
                            CASE 
                              WHEN ul.rst_pwd IS NULL THEN 'No Request'
                              WHEN ul.rst_pwd_expiry > NOW() THEN 'Active'
                              ELSE 'Expired'
                            END as status
                            FROM users u
                            JOIN user_lo ul ON u.user_id = ul.user_id
                            ORDER BY ul.rst_pwd_expiry DESC
                            LIMIT 10";
                    $result = mysqli_query($conn, $sql);
                    while($row = mysqli_fetch_assoc($result)) {
                      echo "<tr>";
                      echo "<td>".htmlspecialchars($row['name'])."</td>";
                      echo "<td>".htmlspecialchars($row['email'])."</td>";
                      echo "<td>".($row['rst_pwd'] ? htmlspecialchars($row['rst_pwd']) : '-')."</td>";
                      echo "<td>".($row['rst_pwd_expiry'] ? date('M j, Y g:i A', strtotime($row['rst_pwd_expiry'])) : '-')."</td>";
                      echo "<td><span class='badge badge-".
                           ($row['status']=='Active'?'success':
                           ($row['status']=='Expired'?'warning':'secondary'))."'>".
                           $row['status']."</span></td>";
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
    </div>
    <?php include_once ('includes/footer.php'); ?>
  </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>