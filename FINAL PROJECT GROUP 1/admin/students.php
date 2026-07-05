<?php
$pageTitle = 'Student Roster';
require_once __DIR__ . '/../includes/header.php';
require_login(['admin']);
$connection = get_db_connection();
$message = '';

// Handle student deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_student'])) {
    $studentId = (int) $_POST['delete_student'];
    $stmt = $connection->prepare('DELETE FROM students WHERE id = ?');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $stmt->close();
    $message = 'Student removed successfully.';
}

// Handle student update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_student'])) {
    $studentId = (int) $_POST['student_id'];
    $studentIdNum = trim($_POST['student_id_num']);
    $program = trim($_POST['program']);
    $phone = trim($_POST['phone']);
    $guardianName = trim($_POST['guardian_name']);
    
    // Get student user info for email notification
    $userStmt = $connection->prepare('SELECT u.email, u.full_name FROM students s JOIN users u ON u.id = s.user_id WHERE s.id = ?');
    $userStmt->bind_param('i', $studentId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();
    $userStmt->close();
    
    $stmt = $connection->prepare('UPDATE students SET student_id = ?, program = ?, phone = ?, guardian_name = ? WHERE id = ?');
    $stmt->bind_param('ssssi', $studentIdNum, $program, $phone, $guardianName, $studentId);
    $stmt->execute();
    $stmt->close();
    
    // Send email notification
    if ($user && !empty($user['email'])) {
        $emailBody = "Hello {$user['full_name']},\n\nYour account details have been updated by an administrator.\n\nUpdated Information:\n- Student ID: {$studentIdNum}\n- Program: {$program}\n- Phone: {$phone}\n- Guardian Name: {$guardianName}\n\nIf you did not request these changes, please contact the administration immediately.\n\nThank you,\nAI-powered student academic performance predictor Team";
        send_smtp_email($user['email'], 'Account Details Updated', $emailBody);
    }
    
    $message = 'Student updated successfully. Email notification sent.';
}

$students = $connection->query('SELECT s.id, s.student_id, u.full_name, s.program, s.attendance, s.predicted_grade, s.final_grade, s.phone, s.guardian_name FROM students s JOIN users u ON u.id = s.user_id ORDER BY s.id');
?>
<div class="card">
    <h1>Student roster</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <table>
        <tr><th>Student ID</th><th>Name</th><th>Program</th><th>Attendance</th><th>Predicted</th><th>Final</th><th>Actions</th></tr>
        <?php while ($row = $students->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['program']); ?></td>
                <td><?php echo htmlspecialchars($row['attendance']); ?>%</td>
                <td><?php echo htmlspecialchars($row['predicted_grade']); ?>%</td>
                <td><?php echo htmlspecialchars($row['final_grade']); ?>%</td>
                <td>
                    <div class="form-actions">
                        <button class="btn secondary" onclick="editStudent(<?php echo (int)$row['id']; ?>, '<?php echo htmlspecialchars($row['student_id']); ?>', '<?php echo htmlspecialchars($row['program']); ?>', '<?php echo htmlspecialchars($row['phone']); ?>', '<?php echo htmlspecialchars($row['guardian_name']); ?>')">Edit</button>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="delete_student" value="<?php echo (int)$row['id']; ?>">
                            <button class="btn danger" type="submit">Remove</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Edit Student Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h2>Edit Student</h2>
        <form method="post">
            <input type="hidden" name="student_id" id="edit_student_id">
            <input type="hidden" name="update_student" value="1">
            
            <label>Student ID</label>
            <input type="text" name="student_id_num" id="edit_student_id_num" required>
            
            <label>Program</label>
            <input type="text" name="program" id="edit_program" required>
            
            <label>Phone</label>
            <input type="text" name="phone" id="edit_phone">
            
            <label>Guardian Name</label>
            <input type="text" name="guardian_name" id="edit_guardian_name">
            
            <div class="form-actions">
                <button class="btn" type="submit">Update</button>
                <button class="btn secondary" type="button" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editStudent(id, studentIdNum, program, phone, guardianName) {
    document.getElementById('edit_student_id').value = id;
    document.getElementById('edit_student_id_num').value = studentIdNum;
    document.getElementById('edit_program').value = program;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_guardian_name').value = guardianName;
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