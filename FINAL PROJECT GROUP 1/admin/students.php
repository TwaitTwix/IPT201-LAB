<?php
$pageTitle = 'Student Roster';
require_once __DIR__ . '/../includes/header.php';
require_login(['admin']);
$connection = get_db_connection();
$students = $connection->query('SELECT s.student_id, u.full_name, s.program, s.attendance, s.predicted_grade, s.final_grade FROM students s JOIN users u ON u.id = s.user_id ORDER BY s.id');
?>
<div class="card">
    <h1>Student roster</h1>
    <table>
        <tr><th>Student ID</th><th>Name</th><th>Program</th><th>Attendance</th><th>Predicted</th><th>Final</th></tr>
        <?php while ($row = $students->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row['student_id']); ?></td><td><?php echo htmlspecialchars($row['full_name']); ?></td><td><?php echo htmlspecialchars($row['program']); ?></td><td><?php echo htmlspecialchars($row['attendance']); ?>%</td><td><?php echo htmlspecialchars($row['predicted_grade']); ?>%</td><td><?php echo htmlspecialchars($row['final_grade']); ?>%</td></tr>
        <?php endwhile; ?>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>