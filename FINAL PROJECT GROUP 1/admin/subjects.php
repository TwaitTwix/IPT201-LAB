<?php
$pageTitle = 'Subjects';
require_once __DIR__ . '/../includes/header.php';
require_login(['admin']);
$connection = get_db_connection();
$message = '';

// Handle subject addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['add_subject'])) {
    $subjectCode = trim($_POST['subject_code']);
    $name = trim($_POST['name']);
    
    if ($name) {
        $stmt = $connection->prepare('INSERT INTO subjects (subject_code, name) VALUES (?, ?)');
        $stmt->bind_param('ss', $subjectCode, $name);
        $stmt->execute();
        $stmt->close();
        $message = 'Subject added successfully.';
    }
}

// Handle subject update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_subject'])) {
    $subjectId = (int) $_POST['subject_id'];
    $subjectCode = trim($_POST['subject_code']);
    $name = trim($_POST['name']);
    
    $stmt = $connection->prepare('UPDATE subjects SET subject_code = ?, name = ? WHERE id = ?');
    $stmt->bind_param('ssi', $subjectCode, $name, $subjectId);
    $stmt->execute();
    $stmt->close();
    $message = 'Subject updated successfully.';
}

// Handle subject deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_subject'])) {
    $subjectId = (int) $_POST['delete_subject'];
    $stmt = $connection->prepare('DELETE FROM subjects WHERE id = ?');
    $stmt->bind_param('i', $subjectId);
    $stmt->execute();
    $stmt->close();
    $message = 'Subject removed successfully.';
}

$subjects = $connection->query('SELECT * FROM subjects ORDER BY name');
?>
<div class="card">
    <h1>Subjects</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    
    <!-- Add Subject Form -->
    <div style="margin-bottom: 24px;">
        <h3>Add New Subject</h3>
        <form method="post">
            <input type="hidden" name="add_subject" value="1">
            <label>Subject Code (Optional)</label>
            <input type="text" name="subject_code">
            <label>Subject Name</label>
            <input type="text" name="name" required>
            <button class="btn" type="submit">Add Subject</button>
        </form>
    </div>
    
    <!-- Subjects Table -->
    <table>
        <tr><th>Code</th><th>Name</th><th>Actions</th></tr>
        <?php while ($row = $subjects->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['subject_code'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td>
                    <div class="form-actions">
                        <button class="btn secondary" onclick="editSubject(<?php echo (int)$row['id']; ?>, '<?php echo htmlspecialchars($row['subject_code'] ?? ''); ?>', '<?php echo htmlspecialchars($row['name']); ?>')">Edit</button>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="delete_subject" value="<?php echo (int)$row['id']; ?>">
                            <button class="btn danger" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Edit Subject Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h2>Edit Subject</h2>
        <form method="post">
            <input type="hidden" name="subject_id" id="edit_subject_id">
            <input type="hidden" name="update_subject" value="1">
            
            <label>Subject Code (Optional)</label>
            <input type="text" name="subject_code" id="edit_subject_code">
            
            <label>Subject Name</label>
            <input type="text" name="name" id="edit_name" required>
            
            <div class="form-actions">
                <button class="btn" type="submit">Update</button>
                <button class="btn secondary" type="button" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSubject(id, subjectCode, name) {
    document.getElementById('edit_subject_id').value = id;
    document.getElementById('edit_subject_code').value = subjectCode;
    document.getElementById('edit_name').value = name;
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
