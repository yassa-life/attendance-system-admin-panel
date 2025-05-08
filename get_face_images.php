<<<<<<< HEAD
<?php
include('includes/dbconnection.php');

// Validate and sanitize user_id
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo '<div class="alert alert-danger">Invalid user ID.</div>';
    exit;
}

$user_id = intval($_GET['user_id']); // Sanitize user_id

// Get user info
$sql = "SELECT first_name, last_name FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<div class="alert alert-danger">User not found.</div>';
    exit;
}

$user = mysqli_fetch_assoc($result);

// Define user folder path
$user_folder = 'E:/Java Ins/project/test3/deepface/database/' . $user_id . '_*';
$user_folder = glob($user_folder, GLOB_ONLYDIR);
$user_folder = !empty($user_folder) ? $user_folder[0] : null;

echo '<h4>Face images for ' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</h4>';
echo '<div class="row">';

if ($user_folder && is_dir($user_folder)) {
    $images = glob($user_folder . "/*.{jpg,png,gif}", GLOB_BRACE);

    if (count($images) > 0) {
        foreach ($images as $image) {
            $image_url = str_replace('\\', '/', $image); // Ensure URL compatibility
            echo '<div class="col-md-4 mb-3">';
            echo '<img src="' . htmlspecialchars($image_url) . '" class="img-fluid img-thumbnail">';
            echo '</div>';
        }
    } else {
        echo '<div class="col-12"><p>No face images found.</p></div>';
    }
} else {
    echo '<div class="col-12"><p>User folder not found.</p></div>';
}

echo '</div>';
=======
<?php
include('includes/dbconnection.php');

// Validate and sanitize user_id
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo '<div class="alert alert-danger">Invalid user ID.</div>';
    exit;
}

$user_id = intval($_GET['user_id']); // Sanitize user_id

// Get user info
$sql = "SELECT first_name, last_name FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<div class="alert alert-danger">User not found.</div>';
    exit;
}

$user = mysqli_fetch_assoc($result);

// Define user folder path
$user_folder = 'E:/Java Ins/project/test3/deepface/database/' . $user_id . '_*';
$user_folder = glob($user_folder, GLOB_ONLYDIR);
$user_folder = !empty($user_folder) ? $user_folder[0] : null;

echo '<h4>Face images for ' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</h4>';
echo '<div class="row">';

if ($user_folder && is_dir($user_folder)) {
    $images = glob($user_folder . "/*.{jpg,png,gif}", GLOB_BRACE);

    if (count($images) > 0) {
        foreach ($images as $image) {
            $image_url = str_replace('\\', '/', $image); // Ensure URL compatibility
            echo '<div class="col-md-4 mb-3">';
            echo '<img src="' . htmlspecialchars($image_url) . '" class="img-fluid img-thumbnail">';
            echo '</div>';
        }
    } else {
        echo '<div class="col-12"><p>No face images found.</p></div>';
    }
} else {
    echo '<div class="col-12"><p>User folder not found.</p></div>';
}

echo '</div>';
>>>>>>> 61dd073d36e3915021be49bbeedffc2fcd8771f9
?>