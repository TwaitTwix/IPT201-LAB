<?php
$pageTitle = 'Encode Grades';
require_once __DIR__ . '/../includes/header.php';
$user = require_login(['teacher']);
$connection = get_db_connection();
$students = $connection->query('SELECT s.id, s.student_id, u.full_name FROM students s JOIN users u ON u.id = s.user_id ORDER BY u.full_name');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int) ($_POST['student_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $assignmentScore = (int) ($_POST['assignment_score'] ?? 0);
    $quizScore = (int) ($_POST['quiz_score'] ?? 0);
    $examScore = (int) ($_POST['exam_score'] ?? 0);
    $finalGrade = (int) round(($assignmentScore * 0.3) + ($quizScore * 0.3) + ($examScore * 0.4));

    $stmt = $connection->prepare('INSERT INTO grades (student_id, subject, assignment_score, quiz_score, exam_score, final_grade, encoded_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isiiiii', $studentId, $subject, $assignmentScore, $quizScore, $examScore, $finalGrade, $user['id']);
    $stmt->execute();
    $stmt->close();

    $update = $connection->prepare('UPDATE students SET assignments = ?, quiz_score = ?, final_grade = ?, predicted_grade = ? WHERE id = ?');
    $predictedScore = predict_grade(95, 8, $assignmentScore, $quizScore);
    $update->bind_param('iiiii', $assignmentScore, $quizScore, $finalGrade, $predictedScore, $studentId);
    $update->execute();
    $update->close();

    $student = $connection->query('SELECT u.id AS user_id, u.full_name FROM students s JOIN users u ON u.id = s.user_id WHERE s.id = ' . $studentId)->fetch_assoc();
    create_notification($user['id'], 'Grade encoded', 'You stored a new grade for ' . $student['full_name']);
    create_notification($student['user_id'], 'New grade posted', 'A new grade has been recorded for you.');
    $message = 'Grade saved successfully.';
}

$grades = $connection->query('SELECT g.*, u.full_name FROM grades g JOIN students s ON s.id = g.student_id JOIN users u ON u.id = s.user_id ORDER BY g.encoded_at DESC LIMIT 10');
?>
<div class="card">
    <h1>Encode grades</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="post">
        <select name="student_id" required>
            <option value="">Select student</option>
            <?php while ($row = $students->fetch_assoc()): ?>
                <option value="<?php echo (int) $row['id']; ?>"><?php echo htmlspecialchars($row['full_name'] . ' (' . $row['student_id'] . ')'); ?></option>
            <?php endwhile; ?>
        </select>
        <input name="subject" placeholder="Subject" required>
        <input name="assignment_score" placeholder="Assignment score" type="number" min="0" max="100" required>
        <input name="quiz_score" placeholder="Quiz score" type="number" min="0" max="100" required>
        <input name="exam_score" placeholder="Exam score" type="number" min="0" max="100" required>
        <button class="btn" type="submit">Save grade</button>
    </form>
</div>
<div class="card" style="margin-top:16px;">
    <h2>Recent encodings</h2>
    <table>
        <tr><th>Student</th><th>Subject</th><th>Assignment</th><th>Quiz</th><th>Exam</th><th>Final</th></tr>
        <?php while ($row = $grades->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row['full_name']); ?></td><td><?php echo htmlspecialchars($row['subject']); ?></td><td><?php echo htmlspecialchars($row['assignment_score']); ?></td><td><?php echo htmlspecialchars($row['quiz_score']); ?></td><td><?php echo htmlspecialchars($row['exam_score']); ?></td><td><?php echo htmlspecialchars($row['final_grade']); ?></td></tr>
        <?php endwhile; ?>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>