<?php
include ('includes/dbconnection.php');

header('Content-Type: application/json');

$user_id = intval($_GET['user_id']);
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

// Validate inputs
if(!$user_id || !$start_date || !$end_date) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

// Get user info
$user_sql = "SELECT first_name, last_name FROM users WHERE user_id = $user_id";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);

// Get all dates in range
$dates = [];
$current = new DateTime($start_date);
$end = new DateTime($end_date);

while($current <= $end) {
    $date_str = $current->format('Y-m-d');
    $day_of_week = $current->format('w'); // 0 (Sun) to 6 (Sat)
    $day_name = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$day_of_week];
    
    $dates[$date_str] = [
        'date' => $date_str,
        'day_name' => $day_name,
        'is_weekend' => ($day_of_week == 0 || $day_of_week == 6),
        'first_checkin' => null,
        'last_checkout' => null,
        'work_hours' => 0,
        'leave_hours' => 0
    ];
    
    $current->modify('+1 day');
}

// Get attendance data
$attendance_sql = "SELECT 
    DATE(timestamp) as date,
    MIN(CASE WHEN type = 'check_in' THEN TIME(timestamp) END) as first_checkin,
    MAX(CASE WHEN type = 'check_out' THEN TIME(timestamp) END) as last_checkout
    FROM attendance
    WHERE user_id = $user_id
    AND DATE(timestamp) BETWEEN '$start_date' AND '$end_date'
    GROUP BY DATE(timestamp)";

$attendance_result = mysqli_query($conn, $attendance_sql);

while($row = mysqli_fetch_assoc($attendance_result)) {
    if(isset($dates[$row['date']])) {
        $dates[$row['date']]['first_checkin'] = $row['first_checkin'];
        $dates[$row['date']]['last_checkout'] = $row['last_checkout'];
        
        // Calculate work hours if both check-in and check-out exist
        if($row['first_checkin'] && $row['last_checkout']) {
            $start = new DateTime($row['date'] . ' ' . $row['first_checkin']);
            $end = new DateTime($row['date'] . ' ' . $row['last_checkout']);
            $diff = $end->diff($start);
            $hours = $diff->h + ($diff->i / 60) + ($diff->s / 3600);
            $dates[$row['date']]['work_hours'] = $hours;
        }
    }
}

// Get leave data
$leave_sql = "SELECT 
    DATE(timestamp) as date,
    SUM(CASE WHEN action = 'leave' THEN 1 ELSE 0 END) as leave_count,
    SUM(CASE WHEN action = 'return' THEN 1 ELSE 0 END) as return_count
    FROM temp_leave
    WHERE user_id = $user_id
    AND DATE(timestamp) BETWEEN '$start_date' AND '$end_date'
    GROUP BY DATE(timestamp)";

$leave_result = mysqli_query($conn, $leave_sql);

while($row = mysqli_fetch_assoc($leave_result)) {
    if(isset($dates[$row['date']])) {
        // Calculate leave hours (assuming 30 minutes per leave/return pair)
        $dates[$row['date']]['leave_hours'] = min($row['leave_count'], $row['return_count']) * 0.5;
    }
}

// Prepare response data
$daily_data = array_values($dates);
$total_work = array_sum(array_column($dates, 'work_hours'));
$total_leave = array_sum(array_column($dates, 'leave_hours'));
$net_work = $total_work - $total_leave;
$days_worked = count(array_filter($dates, function($day) { 
    return $day['work_hours'] > 0; 
}));

// Return JSON response
echo json_encode([
    'user_id' => $user_id,
    'user_name' => $user['first_name'] . ' ' . $user['last_name'],
    'start_date' => $start_date,
    'end_date' => $end_date,
    'daily_data' => $daily_data,
    'total_work' => $total_work,
    'total_leave' => $total_leave,
    'net_work' => $net_work,
    'days_worked' => $days_worked
]);