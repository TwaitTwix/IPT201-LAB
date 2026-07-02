<?php
$pageTitle = 'Attendance';
require_once __DIR__ . '/../includes/header.php';
$user = require_login(['teacher']);
$connection = get_db_connection();
$students = $connection->query('SELECT s.id, s.student_id, u.full_name FROM students s JOIN users u ON u.id = s.user_id ORDER BY u.full_name');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int) ($_POST['student_id'] ?? 0);
    $recordDate = trim($_POST['record_date'] ?? date('Y-m-d'));
    $status = trim($_POST['status'] ?? 'Present');
    $remarks = trim($_POST['remarks'] ?? '');

    $stmt = $connection->prepare('INSERT INTO attendance_records (student_id, record_date, status, remarks) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $studentId, $recordDate, $status, $remarks);
    $stmt->execute();
    $stmt->close();

    $message = 'Attendance saved.';
}

$records = $connection->query('SELECT ar.*, u.full_name FROM attendance_records ar JOIN students s ON s.id = ar.student_id JOIN users u ON u.id = s.user_id ORDER BY ar.record_date DESC LIMIT 10');
?>
<div class="card">
    <h1>Attendance monitoring</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="post">
        <select name="student_id" required>
            <option value="">Select student</option>
            <?php while ($row = $students->fetch_assoc()): ?>
                <option value="<?php echo (int) $row['id']; ?>"><?php echo htmlspecialchars($row['full_name'] . ' (' . $row['student_id'] . ')'); ?></option>
            <?php endwhile; ?>
        </select>
        <input type="date" name="record_date" value="<?php echo date('Y-m-d'); ?>" required>
        <select name="status">
            <option value="Present">Present</option>
            <option value="Late">Late</option>
            <option value="Absent">Absent</option>
        </select>
        <textarea name="remarks" placeholder="Remarks"></textarea>
        <button class="btn" type="submit">Save attendance</button>
    </form>
</div>
<div class="card" style="margin-top:16px;">
    <h2>Recent attendance</h2>
    <table>
        <tr><th>Student</th><th>Date</th><th>Status</th><th>Remarks</th></tr>
        <?php while ($row = $records->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row['full_name']); ?></td><td><?php echo htmlspecialchars($row['record_date']); ?></td><td><?php echo htmlspecialchars($row['status']); ?></td><td><?php echo htmlspecialchars($row['remarks']); ?></td></tr>
        <?php endwhile; ?>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>