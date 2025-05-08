<?php
include_once ('includes/check_session.php');
include ('includes/dbconnection.php');

// Directory where images are stored
$image_dir = "E:/Java Ins/project/test3/deepface/database/";

// Fetch all users for the dropdown
$user_query = mysqli_query($conn, "SELECT user_id, CONCAT(first_name, ' ', last_name) AS full_name FROM users ORDER BY first_name ASC");
$users = [];
while ($row = mysqli_fetch_assoc($user_query)) {
    $users[] = $row;
}

// Initialize selected user and images
$selected_user_id = null;
$user_images = [];

// Check if a user is selected
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $selected_user_id = intval($_GET['user_id']);

    // Look for subfolders matching the pattern "user_id_*"
    if (file_exists($image_dir)) {
        $subfolders = glob($image_dir . "{$selected_user_id}_*", GLOB_ONLYDIR);
        if (!empty($subfolders)) {
            foreach ($subfolders as $subfolder) {
                // Fetch images from the subfolder
                $files = glob($subfolder . "/*.{jpg,jpeg,png}", GLOB_BRACE);
                foreach ($files as $file) {
                    $user_images[] = $file;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Images Gallery</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .gallery-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .image-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .image-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .image-info {
            padding: 10px;
            background-color: #f8f9fa;
        }
        .no-images {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
<?php include_once ('includes/header.php'); ?>

<div class="container-fluid page-body-wrapper">
    <?php include_once ('includes/sidebar.php'); ?>

    <div class="main-panel">
        <div class="content-wrapper">
            <div class="page-header">
                <h3 class="page-title"> Face Images Gallery </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Face Images</li>
                    </ol>
                </nav>
            </div>
            
            <div class="row">
                <div class="col-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Select User</h4>
                            <form method="get" action="">
                                <div class="form-group">
                                    <label for="user_id">User</label>
                                    <select class="form-control" id="user_id" name="user_id" onchange="this.form.submit()">
                                        <option value="">-- Select User --</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['user_id']; ?>" <?php echo ($selected_user_id == $user['user_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['full_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($selected_user_id): ?>
            <div class="row">
                <div class="col-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Registered Face Images</h4>
                            <p class="card-description">These images are used for facial recognition</p>
                            
                            <?php if (empty($user_images)): ?>
                                <div class="no-images">
                                    <p>No face images found for the selected user.</p>
                                </div>
                            <?php else: ?>
                                <div class="gallery-container">
                                    <?php foreach ($user_images as $image): ?>
                                        <?php 
                                            // Convert local file path to web-accessible URL
                                            $web_path = str_replace("E:/Java Ins/project/test3/deepface/database/", "/face_images/", $image);
                                        ?>
                                        <div class="image-card">
                                            <img src="<?php echo htmlspecialchars($web_path); ?>" 
                                                 alt="Face Image" 
                                                 class="image-preview"
                                                 onerror="this.style.display='none'">
                                            <div class="image-info">
                                                <small><?php echo htmlspecialchars(basename($image)); ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-3">
                                    <p class="text-muted">Found <?php echo count($user_images); ?> registered image(s)</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php include_once ('includes/footer.php'); ?>
    </div>
</div>

<!-- <script src="assets/js/bootstrap.bundle.min.js"></script> -->
</body>
</html>
