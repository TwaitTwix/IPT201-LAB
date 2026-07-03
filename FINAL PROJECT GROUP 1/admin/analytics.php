<?php
$pageTitle = 'Analytics Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_login(['admin']);
$connection = get_db_connection();

// Get summary statistics
$summary = $connection->query('
    SELECT 
        COUNT(DISTINCT s.id) as total_students,
        AVG(s.predicted_grade) as avg_predicted_grade,
        AVG(s.final_grade) as avg_final_grade,
        AVG(s.attendance) as avg_attendance,
        COUNT(CASE WHEN s.predicted_grade < 70 THEN 1 END) as at_risk_count
    FROM students s
')->fetch_assoc();

// Get performance distribution
$distribution = [];
$ranges = [
    [90, 100, 'A (90-100%)'],
    [80, 89, 'B (80-89%)'],
    [70, 79, 'C (70-79%)'],
    [60, 69, 'D (60-69%)'],
    [0, 59, 'F (0-59%)']
];

foreach ($ranges as $range) {
    $result = $connection->query("
        SELECT COUNT(*) as count FROM students 
        WHERE final_grade >= {$range[0]} AND final_grade <= {$range[1]}
    ");
    $row = $result->fetch_assoc();
    $distribution[$range[2]] = $row['count'];
}

// Get subject performance
$subjects = $connection->query('
    SELECT 
        sub.name,
        COUNT(g.id) as total_grades,
        AVG(g.final_grade) as avg_grade,
        MIN(g.final_grade) as min_grade,
        MAX(g.final_grade) as max_grade
    FROM grades g
    JOIN subjects sub ON g.subject_id = sub.id
    GROUP BY sub.id, sub.name
    ORDER BY avg_grade DESC
');

// Get program performance
$programs = $connection->query('
    SELECT 
        s.program,
        COUNT(s.id) as student_count,
        AVG(s.predicted_grade) as avg_predicted,
        AVG(s.final_grade) as avg_final,
        AVG(s.attendance) as avg_attendance
    FROM students s
    GROUP BY s.program
    ORDER BY avg_final DESC
');

// Get at-risk students
$atRisk = get_at_risk_students($connection);

// Get top and bottom performers
$topPerformers = $connection->query('
    SELECT 
        u.full_name,
        s.student_id,
        s.predicted_grade,
        s.final_grade,
        AVG(g.final_grade) as avg_grade
    FROM students s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN grades g ON s.id = g.student_id
    GROUP BY s.id, u.full_name, s.student_id, s.predicted_grade, s.final_grade
    ORDER BY s.final_grade DESC
    LIMIT 5
');

$bottomPerformers = $connection->query('
    SELECT 
        u.full_name,
        s.student_id,
        s.predicted_grade,
        s.final_grade,
        AVG(g.final_grade) as avg_grade
    FROM students s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN grades g ON s.id = g.student_id
    WHERE s.final_grade > 0
    GROUP BY s.id, u.full_name, s.student_id, s.predicted_grade, s.final_grade
    ORDER BY s.final_grade ASC
    LIMIT 5
');

// Calculate pass rate
$passRate = $connection->query('
    SELECT 
        COUNT(CASE WHEN final_grade >= 70 THEN 1 END) as passing,
        COUNT(*) as total
    FROM students
    WHERE final_grade > 0
')->fetch_assoc();

$passPercentage = $passRate['total'] > 0 ? round(($passRate['passing'] / $passRate['total']) * 100, 1) : 0;
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Summary Cards -->
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h3 style="margin: 0 0 8px 0; font-size: 14px; opacity: 0.9;">Total Students</h3>
        <p style="margin: 0; font-size: 32px; font-weight: bold;"><?php echo $summary['total_students']; ?></p>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
        <h3 style="margin: 0 0 8px 0; font-size: 14px; opacity: 0.9;">Avg Predicted Grade</h3>
        <p style="margin: 0; font-size: 32px; font-weight: bold;"><?php echo round($summary['avg_predicted_grade'], 1); ?>%</p>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
        <h3 style="margin: 0 0 8px 0; font-size: 14px; opacity: 0.9;">Avg Final Grade</h3>
        <p style="margin: 0; font-size: 32px; font-weight: bold;"><?php echo round($summary['avg_final_grade'], 1); ?>%</p>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
        <h3 style="margin: 0 0 8px 0; font-size: 14px; opacity: 0.9;">Avg Attendance</h3>
        <p style="margin: 0; font-size: 32px; font-weight: bold;"><?php echo round($summary['avg_attendance'], 1); ?>%</p>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
        <h3 style="margin: 0 0 8px 0; font-size: 14px; opacity: 0.9;">At-Risk Students</h3>
        <p style="margin: 0; font-size: 32px; font-weight: bold;"><?php echo $summary['at_risk_count']; ?></p>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
        <h3 style="margin: 0 0 8px 0; font-size: 14px; opacity: 0.8;">Pass Rate</h3>
        <p style="margin: 0; font-size: 32px; font-weight: bold;"><?php echo $passPercentage; ?>%</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Performance Distribution -->
    <div class="card">
        <h2>Grade Distribution</h2>
        <div style="display: flex; align-items: flex-end; gap: 8px; padding: 16px 0; height: 250px;">
            <?php 
            $maxCount = max($distribution) ?: 1;
            foreach ($distribution as $grade => $count):
                $height = ($count / $maxCount) * 200;
            ?>
                <div style="display: flex; flex-direction: column; align-items: center; flex: 1;">
                    <div style="background: linear-gradient(to top, #667eea, #764ba2); width: 100%; height: <?php echo $height; ?>px; border-radius: 4px; margin-bottom: 8px;"></div>
                    <small><?php echo $count; ?></small>
                    <small style="font-size: 11px; text-align: center; margin-top: 4px;"><?php echo substr($grade, 0, 1); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap;">
            <?php foreach ($distribution as $grade => $count): ?>
                <small style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px;"><?php echo $grade; ?>: <?php echo $count; ?></small>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Subject Performance -->
    <div class="card">
        <h2>Top Subjects by Performance</h2>
        <table style="width: 100%; font-size: 13px;">
            <tr style="background: #f5f5f5;">
                <th style="text-align: left; padding: 8px;">Subject</th>
                <th style="text-align: center;">Grades</th>
                <th style="text-align: center;">Avg</th>
                <th style="text-align: center;">Range</th>
            </tr>
            <?php while ($row = $subjects->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 8px;"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td style="text-align: center;"><?php echo $row['total_grades']; ?></td>
                    <td style="text-align: center;"><strong><?php echo round($row['avg_grade'], 1); ?>%</strong></td>
                    <td style="text-align: center; font-size: 12px;"><?php echo round($row['min_grade'], 0); ?>–<?php echo round($row['max_grade'], 0); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Program Performance -->
    <div class="card">
        <h2>Performance by Program</h2>
        <table style="width: 100%; font-size: 13px;">
            <tr style="background: #f5f5f5;">
                <th style="text-align: left; padding: 8px;">Program</th>
                <th style="text-align: center;">Students</th>
                <th style="text-align: center;">Avg Final</th>
                <th style="text-align: center;">Attendance</th>
            </tr>
            <?php while ($row = $programs->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 8px;"><?php echo htmlspecialchars($row['program']); ?></td>
                    <td style="text-align: center;"><?php echo $row['student_count']; ?></td>
                    <td style="text-align: center;"><strong><?php echo round($row['avg_final'], 1); ?>%</strong></td>
                    <td style="text-align: center;"><?php echo round($row['avg_attendance'], 0); ?>%</td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    
    <!-- Top Performers -->
    <div class="card">
        <h2>Top 5 Performers</h2>
        <table style="width: 100%; font-size: 13px;">
            <tr style="background: #f5f5f5;">
                <th style="text-align: left; padding: 8px;">Student</th>
                <th style="text-align: center;">Final Grade</th>
                <th style="text-align: center;">Predicted</th>
            </tr>
            <?php while ($row = $topPerformers->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 8px;">
                        <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                        <br><small style="color: #888;"><?php echo htmlspecialchars($row['student_id']); ?></small>
                    </td>
                    <td style="text-align: center;"><strong><?php echo round($row['final_grade'], 0); ?>%</strong></td>
                    <td style="text-align: center;"><?php echo round($row['predicted_grade'], 0); ?>%</td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- At-Risk Students Alert -->
<?php if (!empty($atRisk)): ?>
<div class="card" style="border-left: 4px solid #f5576c;">
    <h2 style="color: #f5576c;">⚠️ At-Risk Students (<?php echo count($atRisk); ?>)</h2>
    <table style="width: 100%; font-size: 13px;">
        <tr style="background: #fff5f5;">
            <th style="text-align: left; padding: 8px;">Student</th>
            <th style="text-align: center;">Predicted</th>
            <th style="text-align: center;">Attendance</th>
            <th style="text-align: center;">Action</th>
        </tr>
        <?php foreach (array_slice($atRisk, 0, 10) as $student): ?>
            <tr style="border-bottom: 1px solid #ffe0e0;">
                <td style="padding: 8px;">
                    <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>
                    <br><small style="color: #888;"><?php echo htmlspecialchars($student['student_id']); ?></small>
                </td>
                <td style="text-align: center;">
                    <span style="background: #f5576c; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold;">
                        <?php echo round($student['predicted_grade'], 0); ?>%
                    </span>
                </td>
                <td style="text-align: center;"><?php echo isset($student['avg_attendance']) ? round($student['avg_attendance'], 0) : 'N/A'; ?>%</td>
                <td style="text-align: center;">
                    <a href="../student/profile.php?student_id=<?php echo $student['id']; ?>" style="color: #667eea; text-decoration: none; font-size: 12px;">View Profile</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php if (count($atRisk) > 10): ?>
        <p style="margin: 12px 0 0 0; font-size: 12px; color: #888;">Showing 10 of <?php echo count($atRisk); ?> at-risk students</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
