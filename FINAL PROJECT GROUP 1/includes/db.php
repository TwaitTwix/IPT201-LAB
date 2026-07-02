<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'student_academic_db');
}

function get_db_connection()
{
    static $connection = null;
    if ($connection !== null) {
        return $connection;
    }

    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $connection->set_charset('utf8mb4');
    return $connection;
}

function initialize_database()
{
    static $initialized = false;
    if ($initialized) {
        return get_db_connection();
    }

    $initialized = true;
    $server = new mysqli(DB_HOST, DB_USER, DB_PASS);
    $server->query('CREATE DATABASE IF NOT EXISTS ' . DB_NAME);
    $server->select_db(DB_NAME);

    $schemaPath = __DIR__ . '/../database/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception('Schema file not found.');
    }

    $schema = file_get_contents($schemaPath);
    $server->multi_query($schema);
    do {
        if ($result = $server->store_result()) {
            $result->free();
        }
    } while ($server->more_results() && $server->next_result());

    $connection = get_db_connection();
    $result = $connection->query('SELECT COUNT(*) AS total FROM users');
    $row = $result->fetch_assoc();
    if ((int) $row['total'] === 0) {
        import_demo_data($connection);
    }

    return $connection;
}

function import_demo_data($connection)
{
    $defaultUsers = [
        ['admin', 'Admin User', 'admin@school.edu', 'admin', 'password123'],
        ['teacher', 'Teacher User', 'teacher@school.edu', 'teacher', 'password123'],
        ['student', 'Student User', 'student@school.edu', 'student', 'password123'],
    ];

    foreach ($defaultUsers as $user) {
        $stmt = $connection->prepare('INSERT INTO users (username, password_hash, role, full_name, email) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $user[0], $hash, $user[3], $user[1], $user[2]);
        $hash = password_hash($user[4], PASSWORD_DEFAULT);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        if ($user[3] === 'teacher') {
            $teacherStmt = $connection->prepare('INSERT INTO teachers (user_id, department, phone) VALUES (?, ?, ?)');
            $department = 'Mathematics';
            $phone = '09170000001';
            $teacherStmt->bind_param('iss', $userId, $department, $phone);
            $teacherStmt->execute();
            $teacherStmt->close();
        }

        if ($user[3] === 'student') {
            $studentStmt = $connection->prepare('INSERT INTO students (user_id, student_id, program, phone, guardian_name, attendance, study_hours, assignments, quiz_score, predicted_grade, final_grade, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $studentStmt->bind_param('issssiiiiiii', $userId, $studentId, $program, $phone, $guardianName, $attendance, $studyHours, $assignments, $quizScore, $predictedGrade, $finalGrade, $status);
            $studentId = 'STU000';
            $program = 'Computer Science';
            $phone = '09170000002';
            $guardianName = 'Student Guardian';
            $attendance = 90;
            $studyHours = 8;
            $assignments = 88;
            $quizScore = 90;
            $predictedGrade = 89;
            $finalGrade = 90;
            $status = 'Active';
            $studentStmt->execute();
            $studentStmt->close();
        }
    }

    $csvPath = __DIR__ . '/../data/student_performance.csv';
    if (!file_exists($csvPath)) {
        return;
    }

    $handle = fopen($csvPath, 'r');
    fgetcsv($handle);
    while (($row = fgetcsv($handle)) !== false) {
        $studentId = $row[0];
        $name = $row[1];
        $attendance = (int) $row[2];
        $studyHours = (int) $row[3];
        $assignments = (int) $row[4];
        $quizScore = (int) $row[5];
        $finalGrade = (int) $row[6];

        $username = strtolower(str_replace(' ', '', $name)) . rand(10, 99);
        $email = strtolower(str_replace(' ', '', $name)) . '@school.edu';
        $password = password_hash('student123', PASSWORD_DEFAULT);

        $userStmt = $connection->prepare('INSERT INTO users (username, password_hash, role, full_name, email) VALUES (?, ?, ?, ?, ?)');
        $userStmt->bind_param('sssss', $username, $password, $role, $name, $email);
        $role = 'student';
        $userStmt->execute();
        $userId = $userStmt->insert_id;
        $userStmt->close();

        $studentStmt = $connection->prepare('INSERT INTO students (user_id, student_id, program, phone, guardian_name, attendance, study_hours, assignments, quiz_score, predicted_grade, final_grade, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $studentStmt->bind_param('issssiiiiiii', $userId, $studentId, $program, $phone, $guardianName, $attendance, $studyHours, $assignments, $quizScore, $predictedGrade, $finalGrade, $status);
        $program = 'Computer Science';
        $phone = '0917000000' . rand(10, 99);
        $guardianName = 'Guardian';
        $predictedGrade = (int) round(($attendance * 0.4) + ($studyHours * 2) + ($assignments * 0.3) + ($quizScore * 0.3));
        $status = 'Active';
        $studentStmt->execute();
        $studentStmt->close();
    }

    fclose($handle);
}

initialize_database();
?>