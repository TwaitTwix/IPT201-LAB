<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
start_session();
initialize_database();

$user = $_SESSION['user'] ?? null;
$pageTitle = $pageTitle ?? 'AI Student Academic Predictor';

$currentDir = trim(dirname($_SERVER['PHP_SELF']), '/');
$assetPrefix = '';
if ($currentDir !== '') {
    $assetPrefix = str_repeat('../', substr_count($currentDir, '/') + 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo $assetPrefix; ?>assets/styles.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" aria-label="Primary navigation">
        <div class="sidebar-header">
            <div class="brandmark">AI</div>
            <div class="brand-text">
                <span>AI Student Predictor</span>
                <small>Fluent academic insights</small>
            </div>
        </div>
        <nav class="sidebar-nav" aria-label="Main navigation">
            <a class="sidebar-link" href="<?php echo $assetPrefix; ?>dashboard.php">Dashboard</a>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>admin/index.php">Admin Console</a>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>admin/students.php">Student Roster</a>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>admin/teachers.php">Teacher Roster</a>
            <?php elseif ($user && $user['role'] === 'teacher'): ?>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>teacher/encode-grades.php">Encode Grades</a>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>teacher/attendance.php">Attendance</a>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>teacher/reports.php">Reports</a>
            <?php elseif ($user && $user['role'] === 'student'): ?>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>student/profile.php">Profile</a>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>student/predict.php">Predict</a>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>student/analytics.php">Analytics</a>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>student/reports.php">Reports</a>
            <?php endif; ?>
            <?php if ($user): ?>
                <a class="sidebar-link sidebar-link--logout" href="<?php echo $assetPrefix; ?>logout.php">Sign Out</a>
            <?php else: ?>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>login.php">Login</a>
                <a class="sidebar-link" href="<?php echo $assetPrefix; ?>register.php">Register</a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <?php if ($user): ?>
                <div class="status-pill status-pill--info"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></div>
            <?php endif; ?>
            <p class="sidebar-note">Modern analytics for student success.</p>
        </div>
    </aside>
    <div class="main-shell">
        <header class="topbar">
            <button class="sidebar-toggle" type="button" aria-label="Toggle navigation">☰</button>
            <div class="topbar-title">
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            </div>
            <div class="topbar-actions">
                <?php if ($user): ?>
                    <div class="user-chip"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <?php endif; ?>
            </div>
        </header>
        <main class="container">
