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

function predict_grade($attendance, $studyHours, $assignments, $quizScore, $consistency = 0.85, $trend = 1.0) {
    // Enhanced prediction formula with consistency and trend factors
    // Base score from primary indicators
    $attendanceWeight = 0.2;      // Attendance reliability
    $studyWeight = 0.25;          // Study effort
    $assignmentWeight = 0.25;     // Homework completion and quality
    $quizWeight = 0.3;            // Quiz performance (best indicator of understanding)
    
    $baseScore = ($attendance * $attendanceWeight) + 
                 ($studyHours * 2.0 * $studyWeight) +  // Study hours have exponential effect (capped at 20hrs)
                 ($assignments * $assignmentWeight) + 
                 ($quizScore * $quizWeight);
    
    // Apply consistency factor (how stable the student is)
    $baseScore = $baseScore * (0.95 + ($consistency * 0.05));
    
    // Apply trend factor (improvement trajectory)
    $baseScore = $baseScore * (0.98 + ($trend * 0.02));
    
    // Cap study hours impact at 20 hours/week (diminishing returns)
    $cappedStudyHours = min($studyHours, 20);
    $studyBonus = (max(0, $studyHours - 20) * 0.01);
    
    $finalScore = $baseScore + $studyBonus;
    
    return (int) round(max(0, min(100, $finalScore)));
}

function calculate_student_gpa($studentId, $connection) {
    // Calculate GPA from grades (assuming 4.0 scale)
    $stmt = $connection->prepare('
        SELECT AVG(
            CASE 
                WHEN final_grade >= 90 THEN 4.0
                WHEN final_grade >= 85 THEN 3.75
                WHEN final_grade >= 80 THEN 3.5
                WHEN final_grade >= 75 THEN 3.25
                WHEN final_grade >= 70 THEN 3.0
                WHEN final_grade >= 65 THEN 2.75
                WHEN final_grade >= 60 THEN 2.5
                WHEN final_grade >= 55 THEN 2.25
                WHEN final_grade >= 50 THEN 2.0
                ELSE 0.0
            END
        ) as gpa FROM grades WHERE student_id = ?
    ');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return round($row['gpa'] ?? 0, 2);
}

function get_student_consistency_factor($studentId, $connection) {
    // Calculate how consistent student's performance is (lower std deviation = higher consistency)
    $stmt = $connection->prepare('
        SELECT 
            STDDEV(final_grade) as std_dev,
            COUNT(*) as grade_count
        FROM grades 
        WHERE student_id = ?
    ');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if (!$row['grade_count'] || $row['grade_count'] < 2) {
        return 0.85;  // Default consistency for new students
    }
    
    // Convert std deviation to consistency score (0-1)
    // Lower deviation = higher consistency
    $stdDev = $row['std_dev'] ?? 0;
    $consistency = max(0.5, 1.0 - ($stdDev / 50));  // 50 point std dev = 0.5 consistency
    
    return round($consistency, 2);
}

function get_student_trend_factor($studentId, $connection) {
    // Calculate if student is improving or declining
    $stmt = $connection->prepare('
        SELECT final_grade 
        FROM grades 
        WHERE student_id = ? 
        ORDER BY encoded_at DESC 
        LIMIT 10
    ');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = (int)$row['final_grade'];
    }
    $stmt->close();
    
    if (count($grades) < 2) {
        return 1.0;  // Neutral trend for new students
    }
    
    // Reverse array to get chronological order
    $grades = array_reverse($grades);
    
    // Calculate trend: if recent grades > earlier grades, trend > 1.0
    $firstHalf = array_slice($grades, 0, ceil(count($grades) / 2));
    $secondHalf = array_slice($grades, ceil(count($grades) / 2));
    
    $avgFirst = array_sum($firstHalf) / count($firstHalf);
    $avgSecond = array_sum($secondHalf) / count($secondHalf);
    
    // Trend factor between 0.85 and 1.15
    $trend = 1.0 + (($avgSecond - $avgFirst) / 100);
    
    return round(max(0.85, min(1.15, $trend)), 2);
}

function is_student_at_risk($studentId, $connection) {
    // Determine if student is at risk (predicted grade < 70 or multiple low grades)
    $stmt = $connection->prepare('
        SELECT 
            s.predicted_grade,
            COUNT(CASE WHEN g.final_grade < 70 THEN 1 END) as low_grades,
            COUNT(*) as total_grades
        FROM students s
        LEFT JOIN grades g ON s.id = g.student_id
        WHERE s.id = ?
        GROUP BY s.id
    ');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if (!$row) {
        return false;
    }
    
    // At-risk if: predicted < 70 OR more than 30% of grades are below 70
    $riskThreshold = 70;
    $lowGradeRatio = $row['total_grades'] > 0 ? ($row['low_grades'] / $row['total_grades']) : 0;
    
    return ($row['predicted_grade'] < $riskThreshold) || ($lowGradeRatio > 0.3);
}

function get_at_risk_students($connection) {
    // Get all at-risk students for alerts
    $stmt = $connection->prepare('
        SELECT 
            s.id,
            s.student_id,
            u.full_name,
            u.email,
            s.predicted_grade,
            COUNT(CASE WHEN g.final_grade < 70 THEN 1 END) as low_grades,
            COUNT(g.id) as total_grades
        FROM students s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN grades g ON s.id = g.student_id
        WHERE s.predicted_grade < 70 OR s.attendance < 80
        GROUP BY s.id, s.student_id, u.full_name, u.email, s.predicted_grade
        ORDER BY s.predicted_grade ASC
    ');
    $stmt->execute();
    $result = $stmt->get_result();
    $atRiskStudents = [];
    while ($row = $result->fetch_assoc()) {
        $atRiskStudents[] = $row;
    }
    $stmt->close();
    
    return $atRiskStudents;
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

function get_attendance_heatmap_data($studentId, $weeks = 12) {
    $connection = get_db_connection();
    $stmt = $connection->prepare('
        SELECT 
            record_date,
            status,
            DAYOFWEEK(record_date) as day_of_week,
            WEEK(record_date) as week_number
        FROM attendance_records 
        WHERE student_id = ? 
        AND record_date >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
        ORDER BY record_date ASC
    ');
    $stmt->bind_param('ii', $studentId, $weeks);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $heatmapData = [];
    while ($row = $result->fetch_assoc()) {
        $heatmapData[] = [
            'date' => $row['record_date'],
            'status' => $row['status'],
            'day_of_week' => (int)$row['day_of_week'],
            'week_number' => (int)$row['week_number']
        ];
    }
    $stmt->close();
    
    return $heatmapData;
}

function get_subject_performance_breakdown($studentId) {
    $connection = get_db_connection();
    $stmt = $connection->prepare('
        SELECT 
            COALESCE(s.name, g.subject) as subject_name,
            g.assignment_score,
            g.quiz_score,
            g.exam_score,
            g.final_grade,
            g.encoded_at
        FROM grades g
        LEFT JOIN subjects s ON g.subject_id = s.id
        WHERE g.student_id = ?
        ORDER BY g.encoded_at DESC
    ');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subjectData = [];
    while ($row = $result->fetch_assoc()) {
        $subjectName = $row['subject_name'];
        if (!isset($subjectData[$subjectName])) {
            $subjectData[$subjectName] = [
                'subject_name' => $subjectName,
                'grades' => [],
                'avg_assignment' => 0,
                'avg_quiz' => 0,
                'avg_exam' => 0,
                'avg_final' => 0,
                'grade_count' => 0
            ];
        }
        
        $subjectData[$subjectName]['grades'][] = [
            'assignment_score' => (int)$row['assignment_score'],
            'quiz_score' => (int)$row['quiz_score'],
            'exam_score' => (int)$row['exam_score'],
            'final_grade' => (int)$row['final_grade'],
            'encoded_at' => $row['encoded_at']
        ];
        
        $subjectData[$subjectName]['grade_count']++;
    }
    $stmt->close();
    
    // Calculate averages for each subject
    foreach ($subjectData as &$subject) {
        if ($subject['grade_count'] > 0) {
            $totalAssignment = 0;
            $totalQuiz = 0;
            $totalExam = 0;
            $totalFinal = 0;
            
            foreach ($subject['grades'] as $grade) {
                $totalAssignment += $grade['assignment_score'];
                $totalQuiz += $grade['quiz_score'];
                $totalExam += $grade['exam_score'];
                $totalFinal += $grade['final_grade'];
            }
            
            $subject['avg_assignment'] = round($totalAssignment / $subject['grade_count'], 1);
            $subject['avg_quiz'] = round($totalQuiz / $subject['grade_count'], 1);
            $subject['avg_exam'] = round($totalExam / $subject['grade_count'], 1);
            $subject['avg_final'] = round($totalFinal / $subject['grade_count'], 1);
        }
    }
    
    return array_values($subjectData);
}
