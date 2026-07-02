<?php
$pageTitle = 'Teacher Roster';
require_once __DIR__ . '/../includes/header.php';
require_login(['admin']);
$connection = get_db_connection();
$teachers = $connection->query('SELECT u.full_name, t.department, u.email FROM teachers t JOIN users u ON u.id = t.user_id ORDER BY u.full_name');
?>
<div class="card">
    <h1>Teacher roster</h1>
    <table>
        <tr><th>Name</th><th>Department</th><th>Email</th></tr>
        <?php while ($row = $teachers->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row['full_name']); ?></td><td><?php echo htmlspecialchars($row['department']); ?></td><td><?php echo htmlspecialchars($row['email']); ?></td></tr>
        <?php endwhile; ?>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>