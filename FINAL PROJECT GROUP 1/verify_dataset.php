<?php
/**
 * Dataset Verification and Statistics
 * Run this file after installation to verify the dataset is loaded correctly
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$connection = get_db_connection();

// Verify database tables exist
$tables = ['users', 'students', 'teachers', 'grades', 'attendance_records', 'notifications', 'reports'];
echo "<h1>Database Verification</h1>";
echo "<p>Checking tables...</p>";
foreach ($tables as $table) {
    $result = $connection->query("SHOW TABLES LIKE '$table'");
    $status = $result->num_rows > 0 ? '<span style="color:green;">✓ Exists</span>' : '<span style="color:red;">✗ Missing</span>';
    echo "<p>Table <code>$table</code>: $status</p>";
}

// Count records
echo "<h1>Dataset Statistics</h1>";

$userCount = $connection->query('SELECT COUNT(*) AS total FROM users')->fetch_assoc()['total'];
$studentCount = $connection->query('SELECT COUNT(*) AS total FROM students')->fetch_assoc()['total'];
$teacherCount = $connection->query('SELECT COUNT(*) AS total FROM teachers')->fetch_assoc()['total'];
$gradeCount = $connection->query('SELECT COUNT(*) AS total FROM grades')->fetch_assoc()['total'];

echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
echo "<tr><th>Entity</th><th>Count</th></tr>";
echo "<tr><td>Total Users</td><td>$userCount</td></tr>";
echo "<tr><td>Students</td><td>$studentCount</td></tr>";
echo "<tr><td>Teachers</td><td>$teacherCount</td></tr>";
echo "<tr><td>Grades Recorded</td><td>$gradeCount</td></tr>";
echo "</table>";

echo "<h2>Student Performance Distribution</h2>";
$stats = $connection->query('SELECT 
    MIN(attendance) as min_attendance,
    MAX(attendance) as max_attendance,
    AVG(attendance) as avg_attendance,
    MIN(study_hours) as min_hours,
    MAX(study_hours) as max_hours,
    AVG(study_hours) as avg_hours,
    MIN(final_grade) as min_grade,
    MAX(final_grade) as max_grade,
    AVG(final_grade) as avg_grade,
    MIN(predicted_grade) as min_predicted,
    MAX(predicted_grade) as max_predicted,
    AVG(predicted_grade) as avg_predicted
FROM students')->fetch_assoc();

echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
echo "<tr><th>Metric</th><th>Min</th><th>Max</th><th>Average</th></tr>";
echo "<tr><td>Attendance (%)</td><td>".$stats['min_attendance']."</td><td>".$stats['max_attendance']."</td><td>".round($stats['avg_attendance'],2)."</td></tr>";
echo "<tr><td>Study Hours</td><td>".$stats['min_hours']."</td><td>".$stats['max_hours']."</td><td>".round($stats['avg_hours'],2)."</td></tr>";
echo "<tr><td>Final Grade (%)</td><td>".$stats['min_grade']."</td><td>".$stats['max_grade']."</td><td>".round($stats['avg_grade'],2)."</td></tr>";
echo "<tr><td>Predicted Grade (%)</td><td>".$stats['min_predicted']."</td><td>".$stats['max_predicted']."</td><td>".round($stats['avg_predicted'],2)."</td></tr>";
echo "</table>";

echo "<h2>Sample Students</h2>";
$students = $connection->query('SELECT u.full_name, s.student_id, s.attendance, s.study_hours, s.final_grade, s.predicted_grade FROM students s JOIN users u ON u.id = s.user_id LIMIT 10');
echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
echo "<tr><th>Name</th><th>Student ID</th><th>Attendance</th><th>Study Hours</th><th>Final Grade</th><th>Predicted</th></tr>";
while ($row = $students->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row['full_name']."</td>";
    echo "<td>".$row['student_id']."</td>";
    echo "<td>".$row['attendance']."%</td>";
    echo "<td>".$row['study_hours']." hrs</td>";
    echo "<td>".$row['final_grade']."%</td>";
    echo "<td>".$row['predicted_grade']."%</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h1>✓ System Ready</h1>";
echo "<p><a href='login.php'>Go to Login</a> | <a href='index.php'>Go to Home</a></p>";
?>
