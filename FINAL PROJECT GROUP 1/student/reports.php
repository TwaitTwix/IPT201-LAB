<?php
$pageTitle = 'Student Reports';
require_once __DIR__ . '/../includes/header.php';
$user = require_login(['student']);
$connection = get_db_connection();
$student = get_student_by_user_id($user['id']);
$reports = $connection->query('SELECT * FROM reports WHERE student_id = ' . (int) $student['id'] . ' ORDER BY created_at DESC');
?>
<div class="card">
    <h1>My reports</h1>
    <ul>
        <?php while ($row = $reports->fetch_assoc()): ?>
            <li><?php echo htmlspecialchars($row['summary']); ?></li>
        <?php endwhile; ?>
    </ul>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>