<?php
$pageTitle = 'Student Dashboard';
require_once __DIR__ . '/../includes/header.php';
$user = require_login(['student']);
$connection = get_db_connection();
$student = get_student_by_user_id($user['id']);
$grades = $connection->query('SELECT * FROM grades WHERE student_id = ' . (int) $student['id'] . ' ORDER BY encoded_at DESC LIMIT 5');
$notifications = $connection->query('SELECT * FROM notifications WHERE user_id = ' . (int) $user['id'] . ' ORDER BY created_at DESC LIMIT 5');
$reports = $connection->query('SELECT * FROM reports WHERE student_id = ' . (int) $student['id'] . ' ORDER BY created_at DESC LIMIT 5');
?>
<div class="card">
    <h1>Student dashboard</h1>
    <p>Welcome <?php echo htmlspecialchars($user['full_name']); ?>. Your current predicted academic outcome is <strong><?php echo (int) $student['predicted_grade']; ?>%</strong>.</p>
</div>
<div class="grid" style="margin-top:16px;">
    <div class="stat"><h3><?php echo (int) $student['attendance']; ?>%</h3><p>Attendance</p></div>
    <div class="stat"><h3><?php echo (int) $student['study_hours']; ?> hrs</h3><p>Study hours</p></div>
    <div class="stat"><h3><?php echo (int) $student['assignments']; ?>%</h3><p>Assignments</p></div>
    <div class="stat"><h3><?php echo (int) $student['quiz_score']; ?>%</h3><p>Quiz score</p></div>
</div>
<div class="grid" style="margin-top:16px;">
    <div class="card"><h2>My profile</h2><a class="btn" href="profile.php">Update profile</a></div>
    <div class="card"><h2>Predictor</h2><a class="btn secondary" href="predict.php">Try the AI model</a></div>
    <div class="card"><h2>Reports</h2><a class="btn secondary" href="reports.php">View reports</a></div>
</div>
<div class="card" style="margin-top:16px;">
    <h2>Recent grades</h2>
    <table>
        <tr><th>Subject</th><th>Assignment</th><th>Quiz</th><th>Exam</th><th>Final</th></tr>
        <?php while ($row = $grades->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row['subject']); ?></td><td><?php echo htmlspecialchars($row['assignment_score']); ?></td><td><?php echo htmlspecialchars($row['quiz_score']); ?></td><td><?php echo htmlspecialchars($row['exam_score']); ?></td><td><?php echo htmlspecialchars($row['final_grade']); ?></td></tr>
        <?php endwhile; ?>
    </table>
</div>
<div class="card" style="margin-top:16px;">
    <h2>Notifications</h2>
    <ul>
        <?php while ($row = $notifications->fetch_assoc()): ?>
            <li><strong><?php echo htmlspecialchars($row['subject']); ?></strong>: <?php echo htmlspecialchars($row['message']); ?></li>
        <?php endwhile; ?>
    </ul>
</div>
<div class="card" style="margin-top:16px;">
    <h2>Reports</h2>
    <ul>
        <?php while ($row = $reports->fetch_assoc()): ?>
            <li><?php echo htmlspecialchars($row['summary']); ?></li>
        <?php endwhile; ?>
    </ul>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>