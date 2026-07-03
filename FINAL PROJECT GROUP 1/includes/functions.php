<?php
function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function get_log_file_path() {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    return $logDir . '/app.log';
}

function write_log($level, $message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $entry = sprintf('[%s] %s: %s', $timestamp, strtoupper($level), $message);
    if (!empty($context)) {
        $entry .= ' | ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    $entry .= PHP_EOL;
    file_put_contents(get_log_file_path(), $entry, FILE_APPEND | LOCK_EX);
}

function log_info($message, $context = []) {
    write_log('info', $message, $context);
}

function log_warning($message, $context = []) {
    write_log('warning', $message, $context);
}

function log_error($message, $context = []) {
    write_log('error', $message, $context);
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
    $stmt = $connection->prepare('SELECT email, is_email_verified FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $emailResult = $stmt->get_result();
    $user = $emailResult->fetch_assoc();
    $stmt->close();

    if (!empty($user['is_email_verified']) && !empty($user['email'])) {
        $status = send_smtp_email($user['email'], $subject, $message) ? 'sent' : 'failed';
    } else {
        $status = 'pending';
    }

    $insert = $connection->prepare('INSERT INTO notifications (user_id, subject, message, status) VALUES (?, ?, ?, ?)');
    $insert->bind_param('isss', $userId, $subject, $message, $status);
    $insert->execute();
    $insert->close();
}

function predict_grade($attendance, $studyHours, $assignments, $quizScore) {
    $score = ($attendance * 0.25) + ($studyHours * 2.5) + ($assignments * 0.25) + ($quizScore * 0.25);
    return (int) round(max(0, min(100, $score)));
}

function send_smtp_email($to, $subject, $body, $fromEmail = null, $fromName = null) {
    if (!$to || !$subject || !$body) {
        return false;
    }

    $fromEmail = $fromEmail ?: SMTP_FROM_EMAIL;
    $fromName = $fromName ?: SMTP_FROM_NAME;
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $username = SMTP_USERNAME;
    $password = SMTP_PASSWORD;

    if (empty($host) || empty($username) || empty($password)) {
        $headers = "From: {$fromName} <{$fromEmail}>\r\n";
        return mail($to, $subject, $body, $headers);
    }

    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $socket = stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        log_error('SMTP connection failed', ['host' => $host, 'port' => $port, 'errno' => $errno, 'errstr' => $errstr]);
        return false;
    }

    $read = function () use ($socket) {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (preg_match('/^[0-9]{3}\s/', $line)) {
                break;
            }
        }
        return $response;
    };

    $write = function ($command) use ($socket) {
        fwrite($socket, $command . "\r\n");
    };

    $read();
    $write("EHLO localhost");
    $read();
    $write('AUTH LOGIN');
    $read();
    $write(base64_encode($username));
    $read();
    $write(base64_encode($password));
    $authResponse = $read();
    if (strpos($authResponse, '235') !== 0) {
        log_error('SMTP authentication failed', ['response' => $authResponse, 'host' => $host, 'port' => $port, 'username' => $username]);
        fclose($socket);
        return false;
    }

    $write("MAIL FROM:<{$fromEmail}>");
    $read();
    $write("RCPT TO:<{$to}>");
    $read();
    $write('DATA');
    $read();

    $headers = [];
    $headers[] = "From: {$fromName} <{$fromEmail}>";
    $headers[] = "To: {$to}";
    $headers[] = "Subject: {$subject}";
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = '';

    $message = implode("\r\n", $headers) . "\r\n" . $body . "\r\n.\r\n";
    $write($message);
    $dataResponse = $read();
    $write('QUIT');
    $read();
    fclose($socket);

    $success = strpos($dataResponse, '250') === 0;
    if ($success) {
        log_info('SMTP email sent', ['to' => $to, 'subject' => $subject]);
    } else {
        log_error('SMTP email send failed', ['to' => $to, 'subject' => $subject, 'response' => $dataResponse]);
    }

    return $success;
}

function approve_user($userId, $approvedBy = null) {
    $connection = get_db_connection();
    $update = $connection->prepare('UPDATE users SET is_email_verified = 1 WHERE id = ?');
    $update->bind_param('i', $userId);
    $update->execute();
    $update->close();

    $stmt = $connection->prepare('SELECT email, full_name FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && !empty($user['email'])) {
        create_notification($userId, 'Account approved', "Hello {$user['full_name']},\n\nYour account has been approved by an administrator and is now active.\n\nThank you,\nAI Student Predictor Team");
    }

    return true;
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

function get_user_by_email($email) {
    $connection = get_db_connection();
    $stmt = $connection->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
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
