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

        $checkStmt = $connection->prepare('SELECT id, username, email, is_email_verified FROM users WHERE username = ? OR email = ? LIMIT 1');
        $checkStmt->bind_param('ss', $username, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($existingUser = $checkResult->fetch_assoc()) {
            if (empty($existingUser['is_email_verified'])) {
                $message = 'This username or email is already registered and pending admin approval. Please wait for admin verification or log in later.';
            } else {
                $message = 'This username or email is already registered. Please choose a different one or log in.';
            }
            $checkStmt->close();
        } else {
            $checkStmt->close();
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $connection->prepare('INSERT INTO users (username, password_hash, role, full_name, email, is_email_verified) VALUES (?, ?, ?, ?, ?, 0)');
            $stmt->bind_param('sssss', $username, $hash, $role, $fullName, $email);
            $stmt->execute();
            $userId = $stmt->insert_id;
            $stmt->close();

            if ($role === 'teacher') {
                $teacherStmt = $connection->prepare('INSERT INTO teachers (user_id, department, phone) VALUES (?, ?, ?)');
                $department = 'General';
                $phone = '000';
                $teacherStmt->bind_param('iss', $userId, $department, $phone);
                $teacherStmt->execute();
                $teacherStmt->close();
            } else {
                $studentStmt = $connection->prepare('INSERT INTO students (user_id, student_id, program, phone, guardian_name, attendance, study_hours, assignments, quiz_score, predicted_grade, final_grade, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
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
                $studentStmt->bind_param('issssiiiiiii', $userId, $studentId, $program, $phone, $guardianName, $attendance, $studyHours, $assignments, $quizScore, $predictedGrade, $finalGrade, $status);
                $studentStmt->execute();
                $studentStmt->close();
            }

            $message = 'Your account has been submitted for admin approval. You will receive access once an administrator verifies your account.';
        }
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
    <p>After registration, your account requires admin approval before you can log in.</p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>