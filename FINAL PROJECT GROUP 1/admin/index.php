<?php
$pageTitle = 'Admin Panel';
require_once __DIR__ . '/../includes/header.php';
require_login(['admin']);
$connection = get_db_connection();
$students = $connection->query('SELECT s.id, s.student_id, u.full_name, s.program, s.final_grade FROM students s JOIN users u ON u.id = s.user_id ORDER BY s.id LIMIT 10');
$teachers = $connection->query('SELECT t.id, u.full_name, t.department FROM teachers t JOIN users u ON u.id = t.user_id ORDER BY t.id LIMIT 10');
?>
<div class="card">
    <h1>Admin panel</h1>
    <p>Use this section to monitor active students, teachers, and generated summaries.</p>
</div>
<div class="grid" style="margin-top:16px;">
    <div class="card"><h2>Student roster</h2><a class="btn" href="students.php">Open roster</a></div>
    <div class="card"><h2>Teacher roster</h2><a class="btn secondary" href="teachers.php">Open roster</a></div>
    <div class="card"><h2>Pending approvals</h2><a class="btn secondary" href="pending_accounts.php">Review pending accounts</a></div>
</div>
<div class="card" style="margin-top:16px;">
    <h2>Latest students</h2>
    <table>
        <tr><th>Student ID</th><th>Name</th><th>Program</th><th>Final grade</th></tr>
        <?php while ($row = $students->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row['student_id']); ?></td><td><?php echo htmlspecialchars($row['full_name']); ?></td><td><?php echo htmlspecialchars($row['program']); ?></td><td><?php echo htmlspecialchars($row['final_grade']); ?></td></tr>
        <?php endwhile; ?>
    </table>
</div>
<div class="card" style="margin-top:16px;">
    <h2>Latest teachers</h2>
    <table>
        <tr><th>Name</th><th>Department</th></tr>
        <?php while ($row = $teachers->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row['full_name']); ?></td><td><?php echo htmlspecialchars($row['department']); ?></td></tr>
        <?php endwhile; ?>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>