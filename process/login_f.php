<?php
session_start();
include '../includes/dbconnection.php';

// Get the POST data
$username = $_POST['user'];
$password = $_POST['pwd'];

// Check in administration table
$stmt = $conn->prepare("SELECT * FROM administration WHERE username = ? AND pwd = ?");
$stmt->bind_param("ss", $username, $password);

if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        $_SESSION['admin_id'] = $user['admin_id'];
        $_SESSION['role'] = 'admin';
        echo "SUCCESS";
    } else {
        // Check in users table
        $stmt->close();
        $stmt = $conn->prepare("SELECT * FROM user_lo WHERE username = ? AND pwd = ?");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = 'user';
                echo "SUCCESS";
            } else {
                // Authentication failed
                echo "FAILURE";
            }
        } else {
            // Execution failed
            echo "ERROR: " . $stmt->error;
        }
    }

    $stmt->close();
} else {
    // Execution failed
    echo "ERROR: " . $stmt->error;
}

$conn->close();
?>