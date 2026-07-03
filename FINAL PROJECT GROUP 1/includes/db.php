<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'student_academic_db');
}

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp.gmail.com');
    define('SMTP_PORT', 465);
    define('SMTP_USERNAME', 'leonardolllmacalinaoisap1@gmail.com');
    define('SMTP_PASSWORD', 'ebdp xlit dlqr vcks');
    define('SMTP_FROM_EMAIL', SMTP_USERNAME);
    define('SMTP_FROM_NAME', 'AI Student Predictor');
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
    apply_pending_schema_updates($connection);

    $result = $connection->query('SELECT COUNT(*) AS total FROM users');
    $row = $result->fetch_assoc();
    if ((int) $row['total'] === 0) {
        import_demo_data($connection);
    }

    return $connection;
}

function apply_pending_schema_updates($connection)
{
    $indexResult = $connection->query("SHOW INDEX FROM users WHERE Column_name = 'email'");
    $hasEmailUnique = false;
    while ($row = $indexResult->fetch_assoc()) {
        if ((int) $row['Non_unique'] === 0) {
            $hasEmailUnique = true;
            break;
        }
    }

    if (!$hasEmailUnique) {
        $connection->query('ALTER TABLE users ADD UNIQUE INDEX email_unique (email)');
    }

    $columns = [
        'is_email_verified' => 'TINYINT(1) DEFAULT 0',
    ];

    foreach ($columns as $name => $definition) {
        $columnResult = $connection->query("SHOW COLUMNS FROM users LIKE '{$name}'");
        if ($columnResult->num_rows === 0) {
            $connection->query("ALTER TABLE users ADD COLUMN {$name} {$definition}");
        }
    }

    // Ensure grades.subject_id exists and migrate existing subject strings into subjects table
    $colRes = $connection->query("SHOW COLUMNS FROM grades LIKE 'subject_id'");
    if ($colRes->num_rows === 0) {
        $connection->query("ALTER TABLE grades ADD COLUMN subject_id INT DEFAULT NULL");
        // add foreign key if subjects table exists
        $hasSubjects = $connection->query("SHOW TABLES LIKE 'subjects'")->num_rows > 0;
        if ($hasSubjects) {
            // migrate distinct subject names into subjects table
            $distinct = $connection->query("SELECT DISTINCT subject FROM grades WHERE subject IS NOT NULL AND subject != ''");
            if ($distinct) {
                while ($r = $distinct->fetch_assoc()) {
                    $sname = $r['subject'];
                    if (!$sname) continue;
                    $check = $connection->prepare('SELECT id FROM subjects WHERE name = ? LIMIT 1');
                    $check->bind_param('s', $sname);
                    $check->execute();
                    $cres = $check->get_result();
                    if ($cres->num_rows === 0) {
                        $ins = $connection->prepare('INSERT INTO subjects (name) VALUES (?)');
                        $ins->bind_param('s', $sname);
                        $ins->execute();
                        $newId = $ins->insert_id;
                        $ins->close();
                    } else {
                        $row = $cres->fetch_assoc();
                        $newId = $row['id'];
                    }
                    $check->close();

                    // update grades
                    $upd = $connection->prepare('UPDATE grades SET subject_id = ? WHERE subject = ?');
                    $upd->bind_param('is', $newId, $sname);
                    $upd->execute();
                    $upd->close();
                }
            }
        }
        // attempt to add FK; ignore errors
        try {
            $connection->query("ALTER TABLE grades ADD CONSTRAINT fk_grades_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL");
        } catch (Exception $e) {
            // ignore if FK already exists or subjects table absent
        }
    }
}

function import_demo_data($connection)
{
    $defaultUsers = [
        ['admin', 'Admin User', 'admin@school.edu', 'admin', 'password123'],
        ['teacher', 'Teacher User', 'teacher@school.edu', 'teacher', 'password123'],
        ['student', 'Student User', 'student@school.edu', 'student', 'password123'],
    ];

    foreach ($defaultUsers as $user) {
        $hash = password_hash($user[4], PASSWORD_DEFAULT);
        $stmt = $connection->prepare('INSERT INTO users (username, password_hash, role, full_name, email, is_email_verified) VALUES (?, ?, ?, ?, ?, 1)');
        $stmt->bind_param('sssss', $user[0], $hash, $user[3], $user[1], $user[2]);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        if ($user[3] === 'teacher') {
            $teacherStmt = $connection->prepare('INSERT INTO teachers (user_id, department, phone) VALUES (?, ?, ?)');
            $department = 'General Sciences';
            $phone = '09170000001';
            $teacherStmt->bind_param('iss', $userId, $department, $phone);
            $teacherStmt->execute();
            $teacherStmt->close();
        }

        if ($user[3] === 'student') {
            $studentId = 'STU000';
            $program = 'Bachelor of Science';
            $phone = '09170000002';
            $guardianName = 'Guardian';
            $attendance = 90;
            $studyHours = 10;
            $assignments = 88;
            $quizScore = 90;
            $predictedGrade = 89;
            $finalGrade = 90;
            $status = 'Active';
            $studentStmt = $connection->prepare('INSERT INTO students (user_id, student_id, program, phone, guardian_name, attendance, study_hours, assignments, quiz_score, predicted_grade, final_grade, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $studentStmt->bind_param('issssiiiiiii', $userId, $studentId, $program, $phone, $guardianName, $attendance, $studyHours, $assignments, $quizScore, $predictedGrade, $finalGrade, $status);
            $studentStmt->execute();
            $studentStmt->close();
        }
    }

    $csvPath = __DIR__ . '/../data/student_performance.csv';
    if (!file_exists($csvPath)) {
        return;
    }

    // seed some common subjects for selection in teacher grade entry
    $defaultSubjects = [
        ['MATH101', 'Mathematics'],
        ['PHYS101', 'Physics'],
        ['CS101', 'Computer Science'],
        ['ENG101', 'English Composition'],
        ['PROG101', 'Introduction to Programming']
    ];
    foreach ($defaultSubjects as $sub) {
        $stmt = $connection->prepare('SELECT id FROM subjects WHERE name = ? LIMIT 1');
        $stmt->bind_param('s', $sub[1]);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $ins = $connection->prepare('INSERT INTO subjects (subject_code, name) VALUES (?, ?)');
            $ins->bind_param('ss', $sub[0], $sub[1]);
            $ins->execute();
            $ins->close();
        }
        $stmt->close();
    }

    $handle = fopen($csvPath, 'r');
    $header = fgetcsv($handle);
    $importCount = 0;
    while (($row = fgetcsv($handle)) !== false && $importCount < 300) {
        $studentId = $row[0];
        $name = $row[1];
        $attendance = (int) $row[2];
        $studyHours = (int) $row[3];
        $assignments = (int) $row[4];
        $quizScore = (int) $row[5];
        $predictedGrade = (int) $row[6];
        $finalGrade = (int) $row[7];

        $username = 'stu' . strtolower(substr($studentId, -4)) . rand(1, 99);
        $email = strtolower($studentId) . '@university.edu';
        $password = password_hash('student123', PASSWORD_DEFAULT);
        $role = 'student';

        $userStmt = $connection->prepare('INSERT INTO users (username, password_hash, role, full_name, email, is_email_verified) VALUES (?, ?, ?, ?, ?, 1)');
        $userStmt->bind_param('sssss', $username, $password, $role, $name, $email);
        $userStmt->execute();
        $userId = $userStmt->insert_id;
        $userStmt->close();

        $program = 'Tertiary Programs';
        $phone = '0917' . str_pad((string) $importCount, 7, '0', STR_PAD_LEFT);
        $guardianName = 'Guardian';
        $status = 'Active';
        $studentStmt = $connection->prepare('INSERT INTO students (user_id, student_id, program, phone, guardian_name, attendance, study_hours, assignments, quiz_score, predicted_grade, final_grade, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $studentStmt->bind_param('issssiiiiiii', $userId, $studentId, $program, $phone, $guardianName, $attendance, $studyHours, $assignments, $quizScore, $predictedGrade, $finalGrade, $status);
        $studentStmt->execute();
        $studentStmt->close();
        $importCount++;
    }

    fclose($handle);
}

initialize_database();
?>