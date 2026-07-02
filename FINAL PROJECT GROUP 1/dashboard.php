<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
$user = require_login();
$connection = get_db_connection();

if ($user['role'] === 'admin') {
    $studentCount = $connection->query('SELECT COUNT(*) AS total FROM students')->fetch_assoc()['total'];
    $teacherCount = $connection->query('SELECT COUNT(*) AS total FROM teachers')->fetch_assoc()['total'];
    $reportCount = $connection->query('SELECT COUNT(*) AS total FROM reports')->fetch_assoc()['total'];
    $notificationCount = $connection->query('SELECT COUNT(*) AS total FROM notifications')->fetch_assoc()['total'];
} elseif ($user['role'] === 'teacher') {
    $studentCount = $connection->query('SELECT COUNT(*) AS total FROM students')->fetch_assoc()['total'];
    $gradeCount = $connection->query('SELECT COUNT(*) AS total FROM grades')->fetch_assoc()['total'];
    $attendanceCount = $connection->query('SELECT COUNT(*) AS total FROM attendance_records')->fetch_assoc()['total'];
} else {
    $student = get_student_by_user_id($user['id']);
    $notificationCount = $connection->query('SELECT COUNT(*) AS total FROM notifications WHERE user_id=' . (int) $user['id'])->fetch_assoc()['total'];
    $gradeCount = $connection->query('SELECT COUNT(*) AS total FROM grades WHERE student_id=' . (int) $student['id'])->fetch_assoc()['total'];
}
?>
<div class="card">
    <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h1>
    <p>You are logged in as <strong><?php echo htmlspecialchars($user['role']); ?></strong>.</p>
</div>
<div class="grid">
    <?php if ($user['role'] === 'admin'): ?>
        <div class="stat"><h3><?php echo $studentCount; ?></h3><p>Students</p></div>
        <div class="stat"><h3><?php echo $teacherCount; ?></h3><p>Teachers</p></div>
        <div class="stat"><h3><?php echo $reportCount; ?></h3><p>Reports</p></div>
        <div class="stat"><h3><?php echo $notificationCount; ?></h3><p>Notifications</p></div>
    <?php elseif ($user['role'] === 'teacher'): ?>
        <div class="stat"><h3><?php echo $studentCount; ?></h3><p>Students tracked</p></div>
        <div class="stat"><h3><?php echo $gradeCount; ?></h3><p>Grades encoded</p></div>
        <div class="stat"><h3><?php echo $attendanceCount; ?></h3><p>Attendance records</p></div>
    <?php else: ?>
        <div class="stat"><h3><?php echo $student['predicted_grade']; ?>%</h3><p>Predicted grade</p></div>
        <div class="stat"><h3><?php echo $student['attendance']; ?>%</h3><p>Attendance</p></div>
        <div class="stat"><h3><?php echo $gradeCount; ?></h3><p>Recorded grades</p></div>
        <div class="stat"><h3><?php echo $notificationCount; ?></h3><p>Notifications</p></div>
    <?php endif; ?>
</div>
<div class="grid" style="margin-top:16px;">
    <?php if ($user['role'] === 'admin'): ?>
        <div class="card"><h2>Admin tools</h2><a class="btn" href="admin/index.php">Manage system</a></div>
        <div class="card"><h2>Student roster</h2><a class="btn secondary" href="admin/students.php">View students</a></div>
        <div class="card"><h2>Teacher roster</h2><a class="btn secondary" href="admin/teachers.php">View teachers</a></div>
    <?php elseif ($user['role'] === 'teacher'): ?>
        <div class="card"><h2>Grade entry</h2><a class="btn" href="teacher/encode-grades.php">Encode grades</a></div>
        <div class="card"><h2>Attendance tracking</h2><a class="btn secondary" href="teacher/attendance.php">Record attendance</a></div>
        <div class="card"><h2>Reporting</h2><a class="btn secondary" href="teacher/reports.php">Generate report</a></div>
    <?php else: ?>
        <div class="card"><h2>My profile</h2><a class="btn" href="student/profile.php">Update profile</a></div>
        <div class="card"><h2>Prediction</h2><a class="btn secondary" href="student/predict.php">Try predictor</a></div>
        <div class="card"><h2>Reports</h2><a class="btn secondary" href="student/reports.php">View reports</a></div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>