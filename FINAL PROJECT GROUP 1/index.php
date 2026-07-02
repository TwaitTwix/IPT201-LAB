<?php
$pageTitle = 'AI Student Performance Predictor';
require_once __DIR__ . '/includes/header.php';
?>
<div class="hero">
    <section class="card">
        <h1>AI-powered student academic performance predictor</h1>
        <p>This web app helps schools monitor attendance, manage student profiles, encode grades, and estimate future academic performance using attendance, study hours, assignment score, and quiz score trends.</p>
        <a class="btn" href="register.php">Create an account</a>
        <a class="btn secondary" href="login.php">Login</a>
    </section>
    <section class="card">
        <h2>Included features</h2>
        <ul>
            <li>Student profile management</li>
            <li>Teacher and admin grade encoding</li>
            <li>Attendance monitoring</li>
            <li>Performance prediction</li>
            <li>Dashboard views for roles</li>
            <li>Report generation and notifications</li>
        </ul>
    </section>
</div>
<div class="card">
    <h2>Dataset source</h2>
    <p>The sample records are based on the public UCI Student Performance dataset by Paulo Cortez and A. Silva, adapted into this system for attendance, study hours, assignments, quiz scores, and predicted outcomes.</p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>