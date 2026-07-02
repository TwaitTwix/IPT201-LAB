<?php
$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'student';

    if ($username && $password && $fullName && $email) {
        $connection = get_db_connection();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $connection->prepare('INSERT INTO users (username, password_hash, role, full_name, email) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $username, $hash, $role, $fullName, $email);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        if ($role === 'teacher') {
            $teacherStmt = $connection->prepare('INSERT INTO teachers (user_id, department, phone) VALUES (?, ?, ?)');
            $teacherStmt->bind_param('iss', $userId, $department, $phone);
            $department = 'General';
            $phone = '000';
            $teacherStmt->execute();
            $teacherStmt->close();
        } else {
            $studentStmt = $connection->prepare('INSERT INTO students (user_id, student_id, program, phone, guardian_name, attendance, study_hours, assignments, quiz_score, predicted_grade, final_grade, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $studentStmt->bind_param('issssiiiiiii', $userId, $studentId, $program, $phone, $guardianName, $attendance, $studyHours, $assignments, $quizScore, $predictedGrade, $finalGrade, $status);
            $studentId = 'STU' . str_pad((string) $userId, 3, '0', STR_PAD_LEFT);
            $program = 'General Studies';
            $phone = '';
            $guardianName = '';
            $attendance = 0;
            $studyHours = 0;
            $assignments = 0;
            $quizScore = 0;
            $predictedGrade = 0;
            $finalGrade = 0;
            $status = 'Active';
            $studentStmt->execute();
            $studentStmt->close();
        }

        create_notification($userId, 'Welcome', 'Your account has been created successfully.');
        $message = 'Account created. Please log in.';
    } else {
        $message = 'Please complete every field.';
    }
}
?>
<div class="card">
    <h1>Register</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="post">
        <input name="full_name" placeholder="Full name" required>
        <input name="email" placeholder="Email" type="email" required>
        <input name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role">
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select>
        <button class="btn" type="submit">Create account</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>