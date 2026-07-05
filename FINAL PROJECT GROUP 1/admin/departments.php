<?php
$pageTitle = 'Departments';
require_once __DIR__ . '/../includes/header.php';
require_login(['admin']);
$connection = get_db_connection();
$message = '';

// Handle department addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['add_department'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if ($name) {
        $stmt = $connection->prepare('INSERT INTO departments (name, description) VALUES (?, ?)');
        $stmt->bind_param('ss', $name, $description);
        $stmt->execute();
        $stmt->close();
        $message = 'Department added successfully.';
    }
}

// Handle department update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_department'])) {
    $deptId = (int) $_POST['department_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    $stmt = $connection->prepare('UPDATE departments SET name = ?, description = ? WHERE id = ?');
    $stmt->bind_param('ssi', $name, $description, $deptId);
    $stmt->execute();
    $stmt->close();
    $message = 'Department updated successfully.';
}

// Handle department deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_department'])) {
    $deptId = (int) $_POST['delete_department'];
    $stmt = $connection->prepare('DELETE FROM departments WHERE id = ?');
    $stmt->bind_param('i', $deptId);
    $stmt->execute();
    $stmt->close();
    $message = 'Department removed successfully.';
}

$departments = $connection->query('SELECT * FROM departments ORDER BY name');
?>
<div class="card">
    <h1>Departments</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    
    <!-- Add Department Form -->
    <div style="margin-bottom: 24px;">
        <h3>Add New Department</h3>
        <form method="post">
            <input type="hidden" name="add_department" value="1">
            <label>Department Name</label>
            <input type="text" name="name" required>
            <label>Description</label>
            <textarea name="description" rows="3"></textarea>
            <button class="btn" type="submit">Add Department</button>
        </form>
    </div>
    
    <!-- Departments Table -->
    <table>
        <tr><th>Name</th><th>Description</th><th>Actions</th></tr>
        <?php while ($row = $departments->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                <td>
                    <div class="form-actions">
                        <button class="btn secondary" onclick="editDepartment(<?php echo (int)$row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['description'] ?? ''); ?>')">Edit</button>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="delete_department" value="<?php echo (int)$row['id']; ?>">
                            <button class="btn danger" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Edit Department Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h2>Edit Department</h2>
        <form method="post">
            <input type="hidden" name="department_id" id="edit_department_id">
            <input type="hidden" name="update_department" value="1">
            
            <label>Department Name</label>
            <input type="text" name="name" id="edit_name" required>
            
            <label>Description</label>
            <textarea name="description" id="edit_description" rows="3"></textarea>
            
            <div class="form-actions">
                <button class="btn" type="submit">Update</button>
                <button class="btn secondary" type="button" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editDepartment(id, name, description) {
    document.getElementById('edit_department_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
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
