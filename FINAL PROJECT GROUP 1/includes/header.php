<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
start_session();
initialize_database();

$user = $_SESSION['user'] ?? null;
$pageTitle = $pageTitle ?? 'AI Student Performance Predictor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <header class="topbar">
        <div class="brand">AI Student Academic Predictor</div>
        <nav>
            <?php if ($user): ?>
                <a href="../dashboard.php">Dashboard</a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="../admin/index.php">Admin</a>
                <?php elseif ($user['role'] === 'teacher'): ?>
                    <a href="../teacher/index.php">Teacher</a>
                <?php else: ?>
                    <a href="../student/index.php">Student</a>
                <?php endif; ?>
                <a href="../logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="container">
