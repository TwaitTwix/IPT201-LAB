<?php
$pageTitle = 'Admin Approval Required';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h1>Admin Approval Required</h1>
    <p>The system no longer uses OTP email verification. New accounts must be approved by an administrator before they can access the system.</p>
    <p>If you have already registered, please wait for admin approval and then log in using your credentials.</p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>