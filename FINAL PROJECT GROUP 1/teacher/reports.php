<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/../includes/header.php';
$user = require_login(['teacher']);
$connection = get_db_connection();
$students = $connection->query('SELECT s.id, s.student_id, u.full_name FROM students s JOIN users u ON u.id = s.user_id ORDER BY u.full_name');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int) ($_POST['student_id'] ?? 0);
    $student = $connection->query('SELECT s.*, u.full_name, u.email, u.is_email_verified FROM students s JOIN users u ON u.id = s.user_id WHERE s.id = ' . $studentId)->fetch_assoc();
    $summary = 'Student ' . $student['full_name'] . ' has attendance ' . $student['attendance'] . '%, predicted grade ' . $student['predicted_grade'] . '%, and final grade ' . $student['final_grade'] . '%.';
    $stmt = $connection->prepare('INSERT INTO reports (student_id, generated_by, summary) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $studentId, $user['id'], $summary);
    $stmt->execute();
    $stmt->close();
    create_notification($user['id'], 'Report generated', $summary);
    create_notification($student['user_id'], 'Report generated', $summary);
    
    // Send email to student if verified
    if ($student['is_email_verified'] && $student['email']) {
        $emailSubject = 'Your Academic Report';
        $emailBody = "Dear " . htmlspecialchars($student['full_name']) . ",\n\n";
        $emailBody .= "Your academic report has been generated:\n\n";
        $emailBody .= $summary . "\n\n";
        $emailBody .= "Please log in to your account to view more details.\n\n";
        $emailBody .= "Best regards,\nAI Student Predictor System";
        send_smtp_email($student['email'], $emailSubject, $emailBody);
    }
    
    $message = 'Report generated.';
}

$reports = $connection->query('SELECT r.*, u.full_name FROM reports r JOIN students s ON s.id = r.student_id JOIN users u ON u.id = s.user_id ORDER BY r.created_at DESC LIMIT 10');
?>
<div class="card">
    <h1>Generate report</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="post">
        <select name="student_id" required>
            <option value="">Select student</option>
            <?php while ($row = $students->fetch_assoc()): ?>
                <option value="<?php echo (int) $row['id']; ?>"><?php echo htmlspecialchars($row['full_name'] . ' (' . $row['student_id'] . ')'); ?></option>
            <?php endwhile; ?>
        </select>
        <button class="btn" type="submit">Generate report</button>
    </form>
</div>
<div class="card" style="margin-top:16px;">
    <h2>Recent reports</h2>
    <table>
        <tr><th>Student</th><th>Summary</th></tr>
        <?php while ($row = $reports->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row['full_name']); ?></td><td><?php echo htmlspecialchars($row['summary']); ?></td></tr>
        <?php endwhile; ?>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>