<?php include_once('includes/check_session.php'); ?>
<?php include('includes/dbconnection.php'); ?>
<?php include_once('includes/header.php'); ?>

<div class="container-fluid page-body-wrapper">
  <?php include_once('includes/sidebar.php'); ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="page-header">
        <h3 class="page-title"> Admin Dashboard </h3>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
          </ol>
        </nav>
      </div>
      <div class="row">
        <div class="col-md-3 stretch-card grid-margin">
          <div class="card bg-gradient-danger card-img-holder text-dark">
            <div class="card-body">
              <h4 class="font-weight-normal mb-3">Pending Leave Requests <i
                  class="mdi mdi-clock-outline mdi-24px float-right"></i></h4>
              <h2 class="mb-5">
                <?php
                $sql = "SELECT COUNT(*) as count FROM leave_application WHERE status = 'pending'";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                echo $row['count'];
                ?>
              </h2>
              <a href="approve.php" class="text-dark">View All</a>
            </div>
          </div>
        </div>
        <div class="col-md-3 stretch-card grid-margin">
          <div class="card bg-gradient-info card-img-holder text-dark">
            <div class="card-body">
              <h4 class="font-weight-normal mb-3">Total Employees <i
                  class="mdi mdi-account-multiple mdi-24px float-right"></i></h4>
              <h2 class="mb-5">
                <?php
                $sql = "SELECT COUNT(*) as count FROM users WHERE is_active = 1";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                echo $row['count'];
                ?>
              </h2>
              <a href="manage.php" class="text-dark">View All</a>
            </div>
          </div>
        </div>
        <div class="col-md-3 stretch-card grid-margin">
          <div class="card bg-gradient-success card-img-holder text-dark">
            <div class="card-body">
              <h4 class="font-weight-normal mb-3">Today's Check-ins <i
                  class="mdi mdi-checkbox-marked-circle-outline mdi-24px float-right"></i></h4>
              <h2 class="mb-5">
                <?php
                $today = date('Y-m-d');
                $sql = "SELECT COUNT(DISTINCT user_id) as count FROM attendance 
                        WHERE DATE(timestamp) = '$today' AND type = 'check_in'";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                echo $row['count'];
                ?>
              </h2>
              <!-- <a href="attendance_report.php" class="text-dark">View Report</a> -->
            </div>
          </div>
        </div>
        <div class="col-md-3 stretch-card grid-margin">
          <div class="card bg-gradient-warning card-img-holder text-dark">
            <div class="card-body">
              <h4 class="font-weight-normal mb-3">Today's Late Arrivals <i
                  class="mdi mdi-alert-circle mdi-24px float-right"></i></h4>
              <h2 class="mb-5">
                <?php
                $sql = "SELECT COUNT(*) as count FROM (
                          SELECT a.user_id FROM attendance a
                          JOIN users u ON a.user_id = u.user_id
                          WHERE DATE(a.timestamp) = '$today' 
                          AND a.type = 'check_in'
                          AND TIME(a.timestamp) > u.working_hours_start
                        ) as late_arrivals";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                echo $row['count'];
                ?>
              </h2>
              <!-- <a href="attendance_report.php?filter=late" class="text-dark">View Details</a> -->
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Weekly Attendance Overview</h4>
              <canvas id="attendanceChart"></canvas>
              <?php
              // Get attendance data for the last 7 days
              $attendanceData = [];
              $totalEmployees = mysqli_fetch_assoc(mysqli_query(
                $conn,
                "SELECT COUNT(*) as count FROM users WHERE is_active = 1"
              ))['count'];

              for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $present = mysqli_fetch_assoc(mysqli_query(
                  $conn,
                  "SELECT COUNT(DISTINCT user_id) as count FROM attendance 
                       WHERE DATE(timestamp) = '$date' AND type = 'check_in'"
                ))['count'];
                $absent = $totalEmployees - $present;

                $attendanceData[] = [
                  'date' => date('D, M j', strtotime($date)),
                  'present' => $present,
                  'absent' => $absent
                ];
                // echo '<pre>' . print_r($attendanceData, true) . '</pre>';
              }
              ?>
              <script>
                var ctx = document.getElementById('attendanceChart').getContext('2d');
                var chart = new Chart(ctx, {
                  type: 'bar',
                  data: {
                    labels: [<?php echo "'" . implode("','", array_column($attendanceData, 'date')) . "'"; ?>],
                    datasets: [
                      {
                        label: 'Present',
                        backgroundColor: '#4CAF50',
                        data: [<?php echo implode(',', array_column($attendanceData, 'present')); ?>]
                      },
                      {
                        label: 'Absent',
                        backgroundColor: '#F44336',
                        data: [<?php echo implode(',', array_column($attendanceData, 'absent')); ?>]
                      }
                    ]
                  },
                  options: {
                    responsive: true,
                    scales: {
                      x: {
                        stacked: true,
                      },
                      y: {
                        stacked: true,
                        beginAtZero: true,
                        max: <?php echo $totalEmployees; ?>
                      }
                    }
                  }
                });
              </script>
            </div>
          </div>
        </div>

        <div class="col-md-6 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Today's Check-in Methods</h4>
              <canvas id="methodPieChart"></canvas>
              <?php
              $methods = [
                'faceid' => 0,
                'rfid' => 0
              ];

              $sql = "SELECT method, COUNT(*) as count FROM attendance 
                      WHERE DATE(timestamp) = '$today' AND type = 'check_in'
                      GROUP BY method";
              $result = mysqli_query($conn, $sql);
              while ($row = mysqli_fetch_assoc($result)) {
                $methods[$row['method']] = $row['count'];
              }
              ?>
              <script>
                var ctx = document.getElementById('methodPieChart').getContext('2d');
                var chart = new Chart(ctx, {
                  type: 'pie',
                  data: {
                    labels: ['Face ID', 'RFID'],
                    datasets: [{
                      data: [<?php echo "{$methods['faceid']}, {$methods['rfid']}"; ?>],
                      backgroundColor: [
                        '#FF6384',
                        '#36A2EB'
                      ]
                    }]
                  },
                  options: {
                    responsive: true
                  }
                });
              </script>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Recent Attendance Records</h4>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Employee</th>
                      <th>Time</th>
                      <th>Method</th>
                      <th>Type</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as name 
                            FROM attendance a
                            JOIN users u ON a.user_id = u.user_id
                            ORDER BY a.timestamp DESC LIMIT 7";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                      echo "<tr>";
                      echo "<td>" . $row['name'] . "</td>";
                      echo "<td>" . date('d M Y h:i A', strtotime($row['timestamp'])) . "</td>";
                      echo "<td>" . ucfirst($row['method']) . "</td>";
                      echo "<td><span class='badge badge-" . ($row['type'] == 'check_in' ? 'success' : 'primary') . "'>" . ucfirst(str_replace('_', ' ', $row['type'])) . "</span></td>";
                      echo "</tr>";
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Recent Leave Applications</h4>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Employee</th>
                      <th>Leave Type</th>
                      <th>Date</th>
                      <th>Status</th>

                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT l.*, CONCAT(u.first_name, ' ', u.last_name) as name 
                            FROM leave_application l 
                            JOIN users u ON l.user_id = u.user_id 
                            ORDER BY l.applied_at DESC LIMIT 5";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                      echo "<tr>";
                      echo "<td>" . $row['name'] . "</td>";
                      echo "<td>" . ucfirst($row['leave_type']) . "</td>";
                      echo "<td>" . date('d M Y', strtotime($row['leave_date'])) . "</td>";
                      echo "<td><label class='badge badge-" . ($row['status'] == 'approved' ? 'success' : ($row['status'] == 'rejected' ? 'danger' : 'warning')) . "'>" . ucfirst($row['status']) . "</label></td>";

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
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Employees with Most Check-ins (This Month)</h4>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Rank</th>
                      <th>Employee</th>
                      <th>Check-ins</th>
                      <th>Last Check-in</th>
                      <th>Preferred Method</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $currentMonth = date('Y-m');
                    $sql = "SELECT 
                              u.user_id,
                              CONCAT(u.first_name, ' ', u.last_name) as name,
                              COUNT(a.attendance_id) as checkin_count,
                              MAX(a.timestamp) as last_checkin,
                              (
                                SELECT a2.method FROM attendance a2 
                                WHERE a2.user_id = u.user_id 
                                GROUP BY a2.method 
                                ORDER BY COUNT(*) DESC 
                                LIMIT 1
                              ) as preferred_method
                            FROM users u
                            LEFT JOIN attendance a ON u.user_id = a.user_id 
                              AND DATE_FORMAT(a.timestamp, '%Y-%m') = '$currentMonth'
                              AND a.type = 'check_in'
                            WHERE u.is_active = 1
                            GROUP BY u.user_id, name
                            ORDER BY checkin_count DESC
                            LIMIT 5";
                    $result = mysqli_query($conn, $sql);
                    $rank = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                      echo "<tr>";
                      echo "<td>" . $rank++ . "</td>";
                      echo "<td>" . $row['name'] . "</td>";
                      echo "<td>" . $row['checkin_count'] . "</td>";
                      echo "<td>" . ($row['last_checkin'] ? date('d M H:i', strtotime($row['last_checkin'])) : 'N/A') . "</td>";
                      echo "<td>" . ucfirst($row['preferred_method'] ?? 'N/A') . "</td>";
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

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>