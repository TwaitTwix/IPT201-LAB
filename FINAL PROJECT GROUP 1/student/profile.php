<?php
$pageTitle = 'Student Profile';
require_once __DIR__ . '/../includes/header.php';
$user = require_login(['student']);
$connection = get_db_connection();
$student = get_student_by_user_id($user['id']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $program = trim($_POST['program'] ?? '');
    $guardianName = trim($_POST['guardian_name'] ?? '');

    $userStmt = $connection->prepare('UPDATE users SET full_name = ?, email = ? WHERE id = ?');
    $userStmt->bind_param('ssi', $fullName, $email, $user['id']);
    $userStmt->execute();
    $userStmt->close();

    $studentStmt = $connection->prepare('UPDATE students SET phone = ?, program = ?, guardian_name = ? WHERE user_id = ?');
    $studentStmt->bind_param('sssi', $phone, $program, $guardianName, $user['id']);
    $studentStmt->execute();
    $studentStmt->close();

    $_SESSION['user']['full_name'] = $fullName;
    $_SESSION['user']['email'] = $email;
    $message = 'Profile updated.';
}
?>
<div class="card">
    <h1>Update profile</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="post">
        <input name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
        <input name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <input name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" placeholder="Phone number">
        <input name="program" value="<?php echo htmlspecialchars($student['program']); ?>" placeholder="Program">
        <input name="guardian_name" value="<?php echo htmlspecialchars($student['guardian_name']); ?>" placeholder="Guardian name">
        <button class="btn" type="submit">Save profile</button>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>