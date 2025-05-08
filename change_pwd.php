<<<<<<< HEAD
<?php
include('includes/dbconnection.php');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    $alert_message = ''; // Variable to store alert messages

    // Validate inputs
    if (empty($code)) {
        $alert_message = "Reset code is required";
    } elseif (empty($new_password)) {
        $alert_message = "New password is required";
    } elseif (strlen($new_password) < 8) {
        $alert_message = "Password must be at least 8 characters";
    } elseif ($new_password !== $confirm_password) {
        $alert_message = "Passwords do not match";
    } else {
        // Check if code exists and isn't expired
        $current_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("SELECT ul.id, ul.user_id, u.email 
                               FROM user_lo ul
                               JOIN users u ON ul.user_id = u.user_id
                               WHERE ul.rst_pwd = ? AND ul.rst_pwd_expiry > ?");
        $stmt->bind_param("ss", $code, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashed_password = $new_password;

            // Update password and clear reset code
            $update_stmt = $conn->prepare("UPDATE user_lo 
                                          SET pwd = ?, rst_pwd = NULL, rst_pwd_expiry = NULL 
                                          WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $row['id']);

            if ($update_stmt->execute()) {
                $alert_message = "Password updated successfully! You can now login with your new password.";
                echo "<script>alert('$alert_message'); window.location.href='index.php';</script>";
                exit();
            } else {
                $alert_message = "Failed to update password. Please try again.";
            }
        } else {
            $alert_message = "Invalid or expired reset code";
        }
    }

    // Display alert message
    if (!empty($alert_message)) {
        echo "<script>alert('$alert_message'); window.location.href='change_pwd.php?code=" . urlencode($code) . "';</script>";
        exit();
    }
}

// Check if code is provided in URL
$code = isset($_GET['code']) ? trim($_GET['code']) : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .full-width {
            width: 100%;
            padding: 20px;
        }

        .card {
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <?php include_once('includes/header.php'); ?>

    <div class="main-panel" style="width: 50%; margin: 0 auto;">
        <div class="content-wrapper">
            <div class="page-header">
                <h3 class="page-title"> Password Reset </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Reset Password</li>
                    </ol>
                </nav>
            </div>

            <!-- Full-width form -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Reset Your Password</h4>

                    <form class="forms-sample" method="POST" action="change_pwd.php">
                        <div class="form-group">
                            <label for="code">Reset Code</label>
                            <input type="text" class="form-control" id="code" name="code"
                                value="<?= htmlspecialchars($code) ?>" required>
                            <small class="form-text text-muted">Enter the 8-digit code you received by email</small>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="form-text text-muted">Must be at least 8 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                            <small id="password-match" class="form-text text-muted"></small>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2">Reset Password</button>
                        <a href="index.php" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
        <?php include_once('includes/footer.php'); ?>
    </div>


    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password match confirmation
        document.getElementById('confirm_password').addEventListener('input', function () {
            const confirm = this.value;
            const password = document.getElementById('new_password').value;
            const matchText = document.getElementById('password-match');

            if (confirm && password) {
                if (confirm === password) {
                    matchText.textContent = "Passwords match!";
                    matchText.style.color = "green";
                } else {
                    matchText.textContent = "Passwords don't match";
                    matchText.style.color = "red";
                }
            } else {
                matchText.textContent = "";
            }
        });
    </script>
</body>

=======
<?php
include('includes/dbconnection.php');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    $alert_message = ''; // Variable to store alert messages

    // Validate inputs
    if (empty($code)) {
        $alert_message = "Reset code is required";
    } elseif (empty($new_password)) {
        $alert_message = "New password is required";
    } elseif (strlen($new_password) < 8) {
        $alert_message = "Password must be at least 8 characters";
    } elseif ($new_password !== $confirm_password) {
        $alert_message = "Passwords do not match";
    } else {
        // Check if code exists and isn't expired
        $current_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("SELECT ul.id, ul.user_id, u.email 
                               FROM user_lo ul
                               JOIN users u ON ul.user_id = u.user_id
                               WHERE ul.rst_pwd = ? AND ul.rst_pwd_expiry > ?");
        $stmt->bind_param("ss", $code, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashed_password = $new_password;

            // Update password and clear reset code
            $update_stmt = $conn->prepare("UPDATE user_lo 
                                          SET pwd = ?, rst_pwd = NULL, rst_pwd_expiry = NULL 
                                          WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $row['id']);

            if ($update_stmt->execute()) {
                $alert_message = "Password updated successfully! You can now login with your new password.";
                echo "<script>alert('$alert_message'); window.location.href='index.php';</script>";
                exit();
            } else {
                $alert_message = "Failed to update password. Please try again.";
            }
        } else {
            $alert_message = "Invalid or expired reset code";
        }
    }

    // Display alert message
    if (!empty($alert_message)) {
        echo "<script>alert('$alert_message'); window.location.href='change_pwd.php?code=" . urlencode($code) . "';</script>";
        exit();
    }
}

// Check if code is provided in URL
$code = isset($_GET['code']) ? trim($_GET['code']) : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .full-width {
            width: 100%;
            padding: 20px;
        }

        .card {
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <?php include_once('includes/header.php'); ?>

    <div class="main-panel" style="width: 50%; margin: 0 auto;">
        <div class="content-wrapper">
            <div class="page-header">
                <h3 class="page-title"> Password Reset </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Reset Password</li>
                    </ol>
                </nav>
            </div>

            <!-- Full-width form -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Reset Your Password</h4>

                    <form class="forms-sample" method="POST" action="change_pwd.php">
                        <div class="form-group">
                            <label for="code">Reset Code</label>
                            <input type="text" class="form-control" id="code" name="code"
                                value="<?= htmlspecialchars($code) ?>" required>
                            <small class="form-text text-muted">Enter the 8-digit code you received by email</small>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="form-text text-muted">Must be at least 8 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                            <small id="password-match" class="form-text text-muted"></small>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2">Reset Password</button>
                        <a href="index.php" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
        <?php include_once('includes/footer.php'); ?>
    </div>


    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password match confirmation
        document.getElementById('confirm_password').addEventListener('input', function () {
            const confirm = this.value;
            const password = document.getElementById('new_password').value;
            const matchText = document.getElementById('password-match');

            if (confirm && password) {
                if (confirm === password) {
                    matchText.textContent = "Passwords match!";
                    matchText.style.color = "green";
                } else {
                    matchText.textContent = "Passwords don't match";
                    matchText.style.color = "red";
                }
            } else {
                matchText.textContent = "";
            }
        });
    </script>
</body>

>>>>>>> 61dd073d36e3915021be49bbeedffc2fcd8771f9
</html>