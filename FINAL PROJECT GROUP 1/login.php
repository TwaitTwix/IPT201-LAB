<?php
$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = get_user_by_username($username);

    if ($user && password_verify($password, $user['password_hash'])) {
        if (empty($user['is_email_verified'])) {
            $message = 'Your account is pending admin approval. Please wait for an administrator to verify your account.';
        } else {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'full_name' => $user['full_name'],
                'email' => $user['email']
            ];
            redirect('dashboard.php');
        }
    } else {
        $message = 'Invalid username or password.';
    }
}
?>
<div class="card">
    <h1>Login</h1>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="post">
        <input name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button class="btn" type="submit">Login</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>