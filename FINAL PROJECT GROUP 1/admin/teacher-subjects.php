<?php
$pageTitle = 'Teacher Subject Assignments';
require_once __DIR__ . '/../includes/header.php';
require_login(['admin']);
$connection = get_db_connection();
$message = '';

// Handle subject assignment to teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['assign_subject'])) {
    $teacherId = (int) $_POST['teacher_id'];
    $subjectId = (int) $_POST['subject_id'];
    
    // Get teacher and subject info for email notification
    $infoStmt = $connection->prepare('SELECT u.email, u.full_name, s.name as subject_name FROM teachers t JOIN users u ON u.id = t.user_id JOIN subjects s ON s.id = ? WHERE t.id = ?');
    $infoStmt->bind_param('ii', $subjectId, $teacherId);
    $infoStmt->execute();
    $infoResult = $infoStmt->get_result();
    $info = $infoResult->fetch_assoc();
    $infoStmt->close();
    
    $stmt = $connection->prepare('INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $teacherId, $subjectId);
    $stmt->execute();
    $stmt->close();
    
    // Send email notification
    if ($info && !empty($info['email'])) {
        $emailBody = "Hello {$info['full_name']},\n\nYou have been assigned to teach the following subject:\n\nSubject: {$info['subject_name']}\n\nThis assignment has been made by an administrator. Please check your dashboard for more details.\n\nThank you,\nAI-powered student academic performance predictor Team";
        send_smtp_email($info['email'], 'New Subject Assignment', $emailBody);
    }
    
    $message = 'Subject assigned to teacher successfully. Email notification sent.';
}

// Handle subject removal from teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['remove_subject'])) {
    $assignmentId = (int) $_POST['remove_subject'];
    
    // Get teacher and subject info for email notification
    $infoStmt = $connection->prepare('SELECT u.email, u.full_name, s.name as subject_name FROM teacher_subjects ts JOIN teachers t ON t.id = ts.teacher_id JOIN users u ON u.id = t.user_id JOIN subjects s ON s.id = ts.subject_id WHERE ts.id = ?');
    $infoStmt->bind_param('i', $assignmentId);
    $infoStmt->execute();
    $infoResult = $infoStmt->get_result();
    $info = $infoResult->fetch_assoc();
    $infoStmt->close();
    
    $stmt = $connection->prepare('DELETE FROM teacher_subjects WHERE id = ?');
    $stmt->bind_param('i', $assignmentId);
    $stmt->execute();
    $stmt->close();
    
    // Send email notification
    if ($info && !empty($info['email'])) {
        $emailBody = "Hello {$info['full_name']},\n\nYou have been removed from teaching the following subject:\n\nSubject: {$info['subject_name']}\n\nThis change has been made by an administrator. Please check your dashboard for more details.\n\nThank you,\nAI-powered student academic performance predictor Team";
        send_smtp_email($info['email'], 'Subject Assignment Removed', $emailBody);
    }
    
    $message = 'Subject removed from teacher successfully. Email notification sent.';
}

$teachers = $connection->query('SELECT t.id, u.full_name, t.department FROM teachers t JOIN users u ON u.id = t.user_id ORDER BY u.full_name');
$subjects = $connection->query('SELECT id, name, subject_code FROM subjects ORDER BY name');
$assignments = $connection->query('SELECT ts.id, t.id as teacher_id, u.full_name as teacher_name, s.id as subject_id, s.name as subject_name, s.subject_code FROM teacher_subjects ts JOIN teachers t ON t.id = ts.teacher_id JOIN users u ON u.id = t.user_id JOIN subjects s ON s.id = ts.subject_id ORDER BY u.full_name, s.name');
?>
<div class="card">
    <h1>Teacher Subject Assignments</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    
    <!-- Assign Subject Form -->
    <div style="margin-bottom: 24px;">
        <h3>Assign Subject to Teacher</h3>
        <form method="post">
            <input type="hidden" name="assign_subject" value="1">
            <label>Teacher</label>
            <select name="teacher_id" required>
                <option value="">Select teacher</option>
                <?php while ($row = $teachers->fetch_assoc()): ?>
                    <option value="<?php echo (int)$row['id']; ?>"><?php echo htmlspecialchars($row['full_name']); ?> (<?php echo htmlspecialchars($row['department']); ?>)</option>
                <?php endwhile; ?>
            </select>
            <label>Subject</label>
            <select name="subject_id" required>
                <option value="">Select subject</option>
                <?php 
                $subjects->data_seek(0);
                while ($row = $subjects->fetch_assoc()): ?>
                    <option value="<?php echo (int)$row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?><?php if (!empty($row['subject_code'])) echo ' (' . htmlspecialchars($row['subject_code']) . ')'; ?></option>
                <?php endwhile; ?>
            </select>
            <button class="btn" type="submit">Assign Subject</button>
        </form>
    </div>
    
    <!-- Current Assignments Table -->
    <h3>Current Assignments</h3>
    <table>
        <tr><th>Teacher</th><th>Subject</th><th>Actions</th></tr>
        <?php if ($assignments->num_rows === 0): ?>
            <tr><td colspan="3">No subject assignments found.</td></tr>
        <?php else: ?>
            <?php while ($row = $assignments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject_name']); ?><?php if (!empty($row['subject_code'])) echo ' (' . htmlspecialchars($row['subject_code']) . ')'; ?></td>
                    <td>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="remove_subject" value="<?php echo (int)$row['id']; ?>">
                            <button class="btn danger" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
