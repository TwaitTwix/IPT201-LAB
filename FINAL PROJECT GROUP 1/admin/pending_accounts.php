<?php
$pageTitle = 'Pending Approvals';
require_once __DIR__ . '/../includes/header.php';
require_login(['admin']);
$connection = get_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['approve_user'])) {
    $userId = (int) $_POST['approve_user'];
    if ($userId > 0) {
        approve_user($userId);
        $message = 'User account approved and notification sent.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reject_user'])) {
    $userId = (int) $_POST['reject_user'];
    if ($userId > 0) {
        reject_user($userId);
        $message = 'User account rejected and removed from the system.';
    }
}

$pendingUsers = $connection->query('SELECT id, username, full_name, email, role, created_at FROM users WHERE is_email_verified = 0 AND role != "admin" ORDER BY created_at DESC');
?>
<div class="card">
    <h1>Pending Account Approvals</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($pendingUsers->num_rows === 0): ?>
        <p>No pending accounts at the moment.</p>
    <?php else: ?>
        <table>
            <tr><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Requested</th><th>Action</th></tr>
            <?php while ($row = $pendingUsers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <div class="form-actions">
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="approve_user" value="<?php echo (int) $row['id']; ?>">
                                <button class="btn secondary" type="submit">Approve</button>
                            </form>
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="reject_user" value="<?php echo (int) $row['id']; ?>">
                                <button class="btn danger" type="submit">Reject</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>