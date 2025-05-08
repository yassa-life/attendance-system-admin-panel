<<<<<<< HEAD
<?php include_once('includes/check_session.php'); ?>
<?php include('includes/dbconnection.php'); ?>
<?php include_once('includes/header.php'); ?>

<div class="container-fluid page-body-wrapper">
  <?php include_once('includes/sidebar.php'); ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="page-header">
        <h3 class="page-title">ID Card Management</h3>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">ID Management</li>
          </ol>
        </nav>
      </div>

      <?php
      // Assign ID to user
      if (isset($_POST['assign_id'])) {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $id_value = mysqli_real_escape_string($conn, $_POST['id_value']);
        // $id_type = mysqli_real_escape_string($conn, $_POST['id_type']);
      
        // Check if ID already exists
        $check_sql = "SELECT * FROM rfid WHERE card_id = '$id_value'";
        $result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($result) > 0) {
          echo '<div class="alert alert-danger">This ID is already assigned to another user!</div>';
        } else {
          if (true) {
            $sql = "INSERT INTO rfid (card_id, user_id, is_active) 
                          VALUES ('$id_value', '$user_id', 1)
                          ON DUPLICATE KEY UPDATE is_active = 1";
          }

          if (mysqli_query($conn, $sql)) {
            echo '<div class="alert alert-success">ID assigned successfully!</div>';
          } else {
            echo '<div class="alert alert-danger">Error assigning ID: ' . mysqli_error($conn) . '</div>';
          }
        }
      }

      // Activate ID
      if (isset($_POST['action']) && $_POST['action'] === 'activate') {
        $card_id = mysqli_real_escape_string($conn, $_POST['card_id']);
        $sql = "UPDATE rfid SET is_active = 1 WHERE card_id = '$card_id'";

        if (mysqli_query($conn, $sql)) {
          echo '<div class="alert alert-success">ID activated successfully!</div>';
        } else {
          echo '<div class="alert alert-danger">Error activating ID: ' . mysqli_error($conn) . '</div>';
        }
      }

      // Deactivate ID
      if (isset($_POST['action']) && $_POST['action'] === 'deactivate') {
        $card_id = mysqli_real_escape_string($conn, $_POST['card_id']);
        $sql = "UPDATE rfid SET is_active = 0, deactivated_date = NOW() WHERE card_id = '$card_id'";

        if (mysqli_query($conn, $sql)) {
          echo '<div class="alert alert-success">ID deactivated successfully!</div>';
        } else {
          echo '<div class="alert alert-danger">Error deactivating ID: ' . mysqli_error($conn) . '</div>';
        }
      }
      ?>

      <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Assign New ID</h4>
              <form class="forms-sample" method="post">
                <div class="form-group">
                  <label for="user_id">Select Employee</label>
                  <select class="form-control" id="user_id" name="user_id" required>
                    <option value="">Select Employee</option>
                    <?php
                    $sql = "SELECT user_id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE is_active = 1";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                      echo "<option value='" . $row['user_id'] . "'>" . $row['name'] . "</option>";
                    }
                    ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="id_value">ID Value</label>
                  <input type="text" class="form-control" id="id_value" name="id_value" placeholder="Scan or enter ID"
                    required>
                </div>
                <button type="submit" class="btn btn-primary mr-2" name="assign_id">Assign ID</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Assigned IDs</h4>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>User ID</th>
                      <th>Name</th>
                      <th>ID Type</th>
                      <th>ID Value</th>
                      <th>Status</th>
     
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT u.user_id, u.first_name, u.last_name, 
                           r.card_id, r.is_active
                           FROM users u
                           LEFT JOIN rfid r ON u.user_id = r.user_id
                           WHERE r.card_id IS NOT NULL
                           ORDER BY u.first_name";

                    $result = mysqli_query($conn, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                      $user_folder = 'database/' . $row['user_id'] . '_' . strtolower($row['first_name']);
                      $has_images = is_dir($user_folder) && count(glob($user_folder . "/*.{jpg,png,gif}", GLOB_BRACE)) > 0;

                      echo "<tr>";
                      echo "<td>" . $row['user_id'] . "</td>";
                      echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>";
                      echo "<td>RFID Card</td>";
                      echo "<td>" . htmlspecialchars($row['card_id']) . "</td>";
                      echo "<td><span class='badge badge-" . ($row['is_active'] ? 'success' : 'danger') . "'>" . ($row['is_active'] ? 'Active' : 'Inactive') . "</span></td>";
                      echo "<td>";

                      // Show only one button based on is_active value
                      if ($row['is_active']) {
                          echo "<form method='post' style='display:inline; margin-left:5px;'>
                                  <input type='hidden' name='card_id' value='" . htmlspecialchars($row['card_id']) . "'>
                                  <button type='submit' name='action' value='deactivate' class='btn btn-danger btn-sm'>Deactivate</button>
                                </form>";
                      } else {
                          echo "<form method='post' style='display:inline; margin-left:5px;'>
                                  <input type='hidden' name='card_id' value='" . htmlspecialchars($row['card_id']) . "'>
                                  <button type='submit' name='action' value='activate' class='btn btn-success btn-sm'>Activate</button>
                                </form>";
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
    </div>
    <?php include_once('includes/footer.php'); ?>
  </div>
</div>

<!-- Modal for viewing face images -->
<div class="modal fade" id="imagesModal" tabindex="-1" role="dialog" aria-labelledby="imagesModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imagesModalLabel">Face Recognition Images</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal-images-container">
        <!-- Images will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

=======
<?php include_once('includes/check_session.php'); ?>
<?php include('includes/dbconnection.php'); ?>
<?php include_once('includes/header.php'); ?>

<div class="container-fluid page-body-wrapper">
  <?php include_once('includes/sidebar.php'); ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="page-header">
        <h3 class="page-title">ID Card Management</h3>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">ID Management</li>
          </ol>
        </nav>
      </div>

      <?php
      // Assign ID to user
      if (isset($_POST['assign_id'])) {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $id_value = mysqli_real_escape_string($conn, $_POST['id_value']);
        // $id_type = mysqli_real_escape_string($conn, $_POST['id_type']);
      
        // Check if ID already exists
        $check_sql = "SELECT * FROM rfid WHERE card_id = '$id_value'";
        $result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($result) > 0) {
          echo '<div class="alert alert-danger">This ID is already assigned to another user!</div>';
        } else {
          if (true) {
            $sql = "INSERT INTO rfid (card_id, user_id, is_active) 
                          VALUES ('$id_value', '$user_id', 1)
                          ON DUPLICATE KEY UPDATE is_active = 1";
          }

          if (mysqli_query($conn, $sql)) {
            echo '<div class="alert alert-success">ID assigned successfully!</div>';
          } else {
            echo '<div class="alert alert-danger">Error assigning ID: ' . mysqli_error($conn) . '</div>';
          }
        }
      }

      // Activate ID
      if (isset($_POST['action']) && $_POST['action'] === 'activate') {
        $card_id = mysqli_real_escape_string($conn, $_POST['card_id']);
        $sql = "UPDATE rfid SET is_active = 1 WHERE card_id = '$card_id'";

        if (mysqli_query($conn, $sql)) {
          echo '<div class="alert alert-success">ID activated successfully!</div>';
        } else {
          echo '<div class="alert alert-danger">Error activating ID: ' . mysqli_error($conn) . '</div>';
        }
      }

      // Deactivate ID
      if (isset($_POST['action']) && $_POST['action'] === 'deactivate') {
        $card_id = mysqli_real_escape_string($conn, $_POST['card_id']);
        $sql = "UPDATE rfid SET is_active = 0, deactivated_date = NOW() WHERE card_id = '$card_id'";

        if (mysqli_query($conn, $sql)) {
          echo '<div class="alert alert-success">ID deactivated successfully!</div>';
        } else {
          echo '<div class="alert alert-danger">Error deactivating ID: ' . mysqli_error($conn) . '</div>';
        }
      }
      ?>

      <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Assign New ID</h4>
              <form class="forms-sample" method="post">
                <div class="form-group">
                  <label for="user_id">Select Employee</label>
                  <select class="form-control" id="user_id" name="user_id" required>
                    <option value="">Select Employee</option>
                    <?php
                    $sql = "SELECT user_id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE is_active = 1";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                      echo "<option value='" . $row['user_id'] . "'>" . $row['name'] . "</option>";
                    }
                    ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="id_value">ID Value</label>
                  <input type="text" class="form-control" id="id_value" name="id_value" placeholder="Scan or enter ID"
                    required>
                </div>
                <button type="submit" class="btn btn-primary mr-2" name="assign_id">Assign ID</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Assigned IDs</h4>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>User ID</th>
                      <th>Name</th>
                      <th>ID Type</th>
                      <th>ID Value</th>
                      <th>Status</th>
     
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT u.user_id, u.first_name, u.last_name, 
                           r.card_id, r.is_active
                           FROM users u
                           LEFT JOIN rfid r ON u.user_id = r.user_id
                           WHERE r.card_id IS NOT NULL
                           ORDER BY u.first_name";

                    $result = mysqli_query($conn, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                      $user_folder = 'database/' . $row['user_id'] . '_' . strtolower($row['first_name']);
                      $has_images = is_dir($user_folder) && count(glob($user_folder . "/*.{jpg,png,gif}", GLOB_BRACE)) > 0;

                      echo "<tr>";
                      echo "<td>" . $row['user_id'] . "</td>";
                      echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>";
                      echo "<td>RFID Card</td>";
                      echo "<td>" . htmlspecialchars($row['card_id']) . "</td>";
                      echo "<td><span class='badge badge-" . ($row['is_active'] ? 'success' : 'danger') . "'>" . ($row['is_active'] ? 'Active' : 'Inactive') . "</span></td>";
                      echo "<td>";

                      // Show only one button based on is_active value
                      if ($row['is_active']) {
                          echo "<form method='post' style='display:inline; margin-left:5px;'>
                                  <input type='hidden' name='card_id' value='" . htmlspecialchars($row['card_id']) . "'>
                                  <button type='submit' name='action' value='deactivate' class='btn btn-danger btn-sm'>Deactivate</button>
                                </form>";
                      } else {
                          echo "<form method='post' style='display:inline; margin-left:5px;'>
                                  <input type='hidden' name='card_id' value='" . htmlspecialchars($row['card_id']) . "'>
                                  <button type='submit' name='action' value='activate' class='btn btn-success btn-sm'>Activate</button>
                                </form>";
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
    </div>
    <?php include_once('includes/footer.php'); ?>
  </div>
</div>

<!-- Modal for viewing face images -->
<div class="modal fade" id="imagesModal" tabindex="-1" role="dialog" aria-labelledby="imagesModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imagesModalLabel">Face Recognition Images</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal-images-container">
        <!-- Images will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

>>>>>>> 61dd073d36e3915021be49bbeedffc2fcd8771f9
