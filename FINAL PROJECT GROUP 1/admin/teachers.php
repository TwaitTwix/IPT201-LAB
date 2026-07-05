<?php
$pageTitle = 'Teacher Roster';
require_once __DIR__ . '/../includes/header.php';
require_login(['admin']);
$connection = get_db_connection();
$message = '';

// Handle teacher deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_teacher'])) {
    $teacherId = (int) $_POST['delete_teacher'];
    $stmt = $connection->prepare('DELETE FROM teachers WHERE id = ?');
    $stmt->bind_param('i', $teacherId);
    $stmt->execute();
    $stmt->close();
    $message = 'Teacher removed successfully.';
}

// Handle teacher update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_teacher'])) {
    $teacherId = (int) $_POST['teacher_id'];
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);
    
    // Get teacher user info for email notification
    $userStmt = $connection->prepare('SELECT u.email, u.full_name FROM teachers t JOIN users u ON u.id = t.user_id WHERE t.id = ?');
    $userStmt->bind_param('i', $teacherId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();
    $userStmt->close();
    
    $stmt = $connection->prepare('UPDATE teachers SET department = ?, phone = ? WHERE id = ?');
    $stmt->bind_param('ssi', $department, $phone, $teacherId);
    $stmt->execute();
    $stmt->close();
    
    // Send email notification
    if ($user && !empty($user['email'])) {
        $emailBody = "Hello {$user['full_name']},\n\nYour account details have been updated by an administrator.\n\nUpdated Information:\n- Department: {$department}\n- Phone: {$phone}\n\nIf you did not request these changes, please contact the administration immediately.\n\nThank you,\nAI-powered student academic performance predictor Team";
        send_smtp_email($user['email'], 'Account Details Updated', $emailBody);
    }
    
    $message = 'Teacher updated successfully. Email notification sent.';
}

$teachers = $connection->query('SELECT t.id, u.full_name, t.department, u.email, t.phone FROM teachers t JOIN users u ON u.id = t.user_id ORDER BY u.full_name');
$departmentsResult = $connection->query('SELECT id, name FROM departments ORDER BY name');
$departments = [];
while ($dept = $departmentsResult->fetch_assoc()) {
    $departments[] = $dept;
}
?>
<div class="card">
    <h1>Teacher roster</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <table>
        <tr><th>Name</th><th>Department</th><th>Email</th><th>Phone</th><th>Actions</th></tr>
        <?php while ($row = $teachers->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['department']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td>
                    <div class="form-actions">
                        <button class="btn secondary" onclick="editTeacher(<?php echo (int)$row['id']; ?>, '<?php echo htmlspecialchars($row['department']); ?>', '<?php echo htmlspecialchars($row['phone']); ?>')">Edit</button>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="delete_teacher" value="<?php echo (int)$row['id']; ?>">
                            <button class="btn danger" type="submit">Remove</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Edit Teacher Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h2>Edit Teacher</h2>
        <form method="post">
            <input type="hidden" name="teacher_id" id="edit_teacher_id">
            <input type="hidden" name="update_teacher" value="1">
            
            <label>Department</label>
            <select name="department" id="edit_department" required>
                <option value="">Select department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept['name']); ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                <?php endforeach; ?>
            </select>
            
            <label>Phone</label>
            <input type="text" name="phone" id="edit_phone">
            
            <div class="form-actions">
                <button class="btn" type="submit">Update</button>
                <button class="btn secondary" type="button" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editTeacher(id, department, phone) {
    document.getElementById('edit_teacher_id').value = id;
    document.getElementById('edit_department').value = department;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 24px;
    border-radius: 12px;
    max-width: 400px;
    width: 90%;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>