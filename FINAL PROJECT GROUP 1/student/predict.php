<?php
$pageTitle = 'Prediction';
require_once __DIR__ . '/../includes/header.php';
$user = require_login(['student']);
$connection = get_db_connection();
$student = get_student_by_user_id($user['id']);
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance = (int) ($_POST['attendance'] ?? 0);
    $studyHours = (int) ($_POST['study_hours'] ?? 0);
    $assignments = (int) ($_POST['assignments'] ?? 0);
    $quizScore = (int) ($_POST['quiz_score'] ?? 0);
    $result = predict_grade($attendance, $studyHours, $assignments, $quizScore);
}
?>
<div class="card">
    <h1>Performance prediction</h1>
    <p>This predictor uses attendance, study hours, assignment scores, and quiz scores to estimate a likely final grade.</p>
    <form method="post">
        <input name="attendance" type="number" min="0" max="100" placeholder="Attendance (%)" required>
        <input name="study_hours" type="number" min="0" max="20" placeholder="Study hours" required>
        <input name="assignments" type="number" min="0" max="100" placeholder="Assignments (%)" required>
        <input name="quiz_score" type="number" min="0" max="100" placeholder="Quiz score (%)" required>
        <button class="btn" type="submit">Predict</button>
    </form>
    <?php if ($result !== null): ?>
        <div class="card" style="margin-top:16px;">
            <h2>Prediction result</h2>
            <p>Your estimated performance is <strong><?php echo htmlspecialchars($result); ?>%</strong>.</p>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>