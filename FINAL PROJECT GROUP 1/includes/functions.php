<?php
function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function require_login($allowedRoles = []) {
    start_session();
    if (empty($_SESSION['user'])) {
        header('Location: ../login.php');
        exit;
    }

    if ($allowedRoles && !in_array($_SESSION['user']['role'], $allowedRoles, true)) {
        header('Location: ../dashboard.php');
        exit;
    }

    return $_SESSION['user'];
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function create_notification($userId, $subject, $message, $status = 'sent') {
    $connection = get_db_connection();
    $emailResult = $connection->query('SELECT email FROM users WHERE id = ' . (int) $userId);
    $emailRow = $emailResult->fetch_assoc();
    $email = $emailRow['email'] ?? '';

    $stmt = $connection->prepare('INSERT INTO notifications (user_id, subject, message, status) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $userId, $subject, $message, $status);
    $stmt->execute();
    $stmt->close();

    if ($email) {
        @mail($email, $subject, $message);
    }
}

function predict_grade($attendance, $studyHours, $assignments, $quizScore) {
    $score = ($attendance * 0.35) + ($studyHours * 3.2) + ($assignments * 0.35) + ($quizScore * 0.30);
    $score = round($score, 1);
    return max(0, min(100, $score));
}

function get_student_summary($studentId) {
    $connection = get_db_connection();
    $stmt = $connection->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_user_by_username($username) {
    $connection = get_db_connection();
    $stmt = $connection->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_student_by_user_id($userId) {
    $connection = get_db_connection();
    $stmt = $connection->prepare('SELECT * FROM students WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
