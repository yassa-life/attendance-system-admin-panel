<?php include_once ('includes/check_session.php'); ?>
<?php include ('includes/dbconnection.php'); ?>
<?php include_once ('includes/header.php'); ?>

<div class="container-fluid page-body-wrapper">
  <?php include_once ('includes/sidebar.php'); ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="page-header">
        <h3 class="page-title">Employee Work Time Analysis</h3>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Work Analysis</li>
          </ol>
        </nav>
      </div>

      <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Select Employee</h4>
              <div class="form-group">
                <select class="form-control" id="employee-select">
                  <option value="">Select Employee</option>
                  <?php
                  $sql = "SELECT user_id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE is_active = 1";
                  $result = mysqli_query($conn, $sql);
                  while($row = mysqli_fetch_assoc($result)) {
                      echo "<option value='".$row['user_id']."'>".$row['name']."</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="form-group mt-3">
                <select class="form-control" id="time-period" style="width: 200px;">
                  <option value="day">Today</option>
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="year">This Year</option>
                  <option value="custom">Custom Range</option>
                </select>
              </div>
              <div class="form-group" id="custom-range" style="display: none;">
                <div class="row">
                  <div class="col-md-6">
                    <label>Start Date</label>
                    <input type="date" class="form-control" id="start-date">
                  </div>
                  <div class="col-md-6">
                    <label>End Date</label>
                    <input type="date" class="form-control" id="end-date">
                  </div>
                </div>
              </div>
              <button id="generate-btn" class="btn btn-primary">Generate Report</button>
            </div>
          </div>
        </div>
      </div>

      <div class="row" id="analysis-results" style="display: none;">
        <div class="col-md-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Work Time Analysis - <span id="report-period"></span></h4>
              <div class="chart-container" style="height: 400px;">
                <canvas id="workTimeChart"></canvas>
              </div>
              
              <div class="table-responsive mt-4">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Day</th>
                      <th>First Check-in</th>
                      <th>Last Check-out</th>
                      <th>Work Hours</th>
                      <th>Leave Hours</th>
                      <th>Net Hours</th>
                    </tr>
                  </thead>
                  <tbody id="daily-data">
                    <!-- Filled by JavaScript -->
                  </tbody>
                </table>
              </div>
              
              <div class="mt-4 p-3 bg-light rounded">
                <div class="row">
                  <div class="col-md-3">
                    <h5>Total Work: <span id="total-work-time">0</span>h</h5>
                  </div>
                  <div class="col-md-3">
                    <h5>Total Leave: <span id="total-leave-time">0</span>h</h5>
                  </div>
                  <div class="col-md-3">
                    <h5>Net Work: <span id="net-work-time">0</span>h</h5>
                  </div>
                  <div class="col-md-3">
                    <h5>Days Worked: <span id="days-worked">0</span></h5>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include_once ('includes/footer.php'); ?>
  </div>
</div>

<!-- Include required libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize date pickers
    const today = new Date();
    const lastMonth = new Date();
    lastMonth.setMonth(today.getMonth() - 1);
    
    $('#start-date').val(formatDate(lastMonth));
    $('#end-date').val(formatDate(today));
    
    // Show/hide custom date range
    $('#time-period').change(function() {
        if($(this).val() === 'custom') {
            $('#custom-range').show();
        } else {
            $('#custom-range').hide();
        }
    });
    
    // Generate report button click
    $('#generate-btn').click(function() {
        const userId = $('#employee-select').val();
        const period = $('#time-period').val();
        
        if(!userId) {
            alert('Please select an employee');
            return;
        }
        
        let startDate, endDate = new Date();
        
        if(period === 'custom') {
            startDate = new Date($('#start-date').val());
            endDate = new Date($('#end-date').val());
            
            if(!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }
        } else {
            startDate = getStartDate(period);
        }
        
        // Update report period text
        $('#report-period').text(getPeriodText(period, startDate, endDate));
        
        // Fetch data via AJAX
        $.ajax({
            url: 'fetch_work_data.php',
            type: 'GET',
            data: { 
                user_id: userId,
                start_date: formatDate(startDate),
                end_date: formatDate(endDate)
            },
            dataType: 'json',
            success: function(data) {
                updateChart(data);
                updateTable(data);
                updateSummary(data);
                $('#analysis-results').show();
            },
            error: function(xhr, status, error) {
                console.error("Error fetching work data:", error);
                alert('Error loading data. Please try again.');
            }
        });
    });
    
    // Helper functions
    function getStartDate(period) {
        const date = new Date();
        
        switch(period) {
            case 'day': date.setHours(0, 0, 0, 0); break;
            case 'week': date.setDate(date.getDate() - 6); break;
            case 'month': date.setDate(1); break;
            case 'year': date.setMonth(0, 1); break;
        }
        
        return date;
    }
    
    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }
    
    function getPeriodText(period, startDate, endDate) {
        switch(period) {
            case 'day': return 'Today - ' + formatDate(endDate);
            case 'week': return 'This Week - ' + formatDate(startDate) + ' to ' + formatDate(endDate);
            case 'month': return 'This Month - ' + formatDate(startDate) + ' to ' + formatDate(endDate);
            case 'year': return 'This Year - ' + formatDate(startDate) + ' to ' + formatDate(endDate);
            case 'custom': return 'Custom Period - ' + formatDate(startDate) + ' to ' + formatDate(endDate);
            default: return '';
        }
    }
    
    function updateChart(data) {
        const ctx = document.getElementById('workTimeChart').getContext('2d');
        
        // Check if the chart instance exists and destroy it
        if (window.workTimeChart && typeof window.workTimeChart.destroy === 'function') {
            window.workTimeChart.destroy();
        }
        
        // Prepare chart data
        const labels = data.daily_data.map(day => day.date);
        const workHours = data.daily_data.map(day => day.work_hours);
        const leaveHours = data.daily_data.map(day => day.leave_hours);
        const dayNames = data.daily_data.map(day => day.day_name);
        
        // Create a new chart instance
        window.workTimeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Work Hours',
                        data: workHours,
                        backgroundColor: 'rgba(232, 25, 25, 0.86)', // Blue
                        borderColor: 'rgba(232, 25, 25, 0.86)', // Blue
                        borderWidth: 1
                    },
                    {
                        label: 'Leave Hours',
                        data: leaveHours,
                        backgroundColor: 'rgba(35, 32, 215, 0.96)', // Red
                        borderColor: 'rgba(35, 32, 215, 0.96)', // Red
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            callback: function(value, index) {
                                return dayNames[index] + '\n' + labels[index].split('-')[2];
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const day = data.daily_data[context.dataIndex];
                                return `Check-in: ${day.first_checkin || 'N/A'}\nCheck-out: ${day.last_checkout || 'N/A'}`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    function updateTable(data) {
        const tbody = $('#daily-data');
        tbody.empty();
        
        data.daily_data.forEach(day => {
            const row = `
                <tr class="${day.is_weekend ? 'table-secondary' : ''}">
                    <td>${day.date}</td>
                    <td>${day.day_name}</td>
                    <td>${day.first_checkin || 'N/A'}</td>
                    <td>${day.last_checkout || 'N/A'}</td>
                    <td>${day.work_hours.toFixed(2)}</td>
                    <td>${day.leave_hours.toFixed(2)}</td>
                    <td>${(day.work_hours - day.leave_hours).toFixed(2)}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function updateSummary(data) {
        $('#total-work-time').text(data.total_work.toFixed(2));
        $('#total-leave-time').text(data.total_leave.toFixed(2));
        $('#net-work-time').text(data.net_work.toFixed(2));
        $('#days-worked').text(data.days_worked);
    }
});
</script>