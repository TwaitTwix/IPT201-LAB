<?php
$pageTitle = 'Confirm Logout';
require_once __DIR__ . '/includes/header.php';
$user = require_login();
?>

<div class="card">
    <h1>Confirm Logout</h1>
    <p>Are you sure you want to log out of your account?</p>
    <div class="form-actions">
        <form method="post" action="logout.php" style="display: inline;">
            <button class="btn danger" type="submit">Yes, Log Out</button>
        </form>
        <a href="<?php echo $assetPrefix; ?>dashboard.php" class="btn secondary">Cancel</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
