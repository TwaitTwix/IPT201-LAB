<?php
$pageTitle = 'Encode Grades';
require_once __DIR__ . '/../includes/header.php';
$user = require_login(['teacher']);
$connection = get_db_connection();
$students = $connection->query('SELECT s.id, s.student_id, u.full_name FROM students s JOIN users u ON u.id = s.user_id ORDER BY u.full_name');
$message = '';
// fetch distinct subjects already recorded to populate the dropdown
$subjectResult = $connection->query('SELECT id, subject_code, name FROM subjects ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int) ($_POST['student_id'] ?? 0);
    // if user selected "Add new subject", use the provided new_subject field and persist it; otherwise use selected subject id
    $selected = $_POST['subject'] ?? '';
    $newSubject = trim($_POST['new_subject'] ?? '');
    $subjectId = null;
    $subjectName = '';
    if ($selected === '__new__' && $newSubject !== '') {
        $subjectName = $newSubject;
        // persist new subject into subjects table if it doesn't exist
        $check = $connection->prepare('SELECT id FROM subjects WHERE name = ? LIMIT 1');
        $check->bind_param('s', $newSubject);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows === 0) {
            $ins = $connection->prepare('INSERT INTO subjects (name) VALUES (?)');
            $ins->bind_param('s', $newSubject);
            $ins->execute();
            $subjectId = $ins->insert_id;
            $ins->close();
        } else {
            $row = $res->fetch_assoc();
            $subjectId = (int) $row['id'];
        }
        $check->close();
    } else {
        $subjectId = (int) $selected;
        if ($subjectId > 0) {
            $sRow = $connection->prepare('SELECT name FROM subjects WHERE id = ? LIMIT 1');
            $sRow->bind_param('i', $subjectId);
            $sRow->execute();
            $sres = $sRow->get_result();
            if ($r = $sres->fetch_assoc()) $subjectName = $r['name'];
            $sRow->close();
        }
    }
    $assignmentScore = (int) ($_POST['assignment_score'] ?? 0);
    $quizScore = (int) ($_POST['quiz_score'] ?? 0);
    $examScore = (int) ($_POST['exam_score'] ?? 0);
    $finalGrade = (int) round(($assignmentScore * 0.3) + ($quizScore * 0.3) + ($examScore * 0.4));

    $stmt = $connection->prepare('INSERT INTO grades (student_id, subject_id, subject, assignment_score, quiz_score, exam_score, final_grade, encoded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iisiiiii', $studentId, $subjectId, $subjectName, $assignmentScore, $quizScore, $examScore, $finalGrade, $user['id']);
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

$grades = $connection->query('SELECT g.*, COALESCE(sub.name, g.subject) AS subject_name, u.full_name FROM grades g LEFT JOIN subjects sub ON sub.id = g.subject_id JOIN students s ON s.id = g.student_id JOIN users u ON u.id = s.user_id ORDER BY g.encoded_at DESC LIMIT 10');
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

        <label for="subject_select">Subject</label>
        <select name="subject" id="subject_select" required>
            <option value="">Select subject</option>
            <?php if ($subjectResult && $subjectResult->num_rows > 0): ?>
                <?php while ($s = $subjectResult->fetch_assoc()): ?>
                    <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?><?php if (!empty($s['subject_code'])) echo ' (' . htmlspecialchars($s['subject_code']) . ')'; ?></option>
                <?php endwhile; ?>
            <?php endif; ?>
            <option value="__new__">-- Add new subject --</option>
        </select>

        <input type="text" name="new_subject" id="new_subject" placeholder="Enter new subject" style="display:none; margin-top:8px;">

        <input name="assignment_score" placeholder="Assignment score" type="number" min="0" max="100" required>
        <input name="quiz_score" placeholder="Quiz score" type="number" min="0" max="100" required>
        <input name="exam_score" placeholder="Exam score" type="number" min="0" max="100" required>
        <button class="btn" type="submit">Save grade</button>
    </form>

    <script>
        (function(){
            var sel = document.getElementById('subject_select');
            var newInput = document.getElementById('new_subject');
            if (!sel) return;
            sel.addEventListener('change', function(){
                if (sel.value === '__new__') {
                    newInput.style.display = 'block';
                    newInput.required = true;
                } else {
                    newInput.style.display = 'none';
                    newInput.required = false;
                }
            });
        })();
    </script>
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