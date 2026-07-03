<?php
$pageTitle = 'Student Analytics';
require_once __DIR__ . '/../includes/header.php';
$user = require_login(['student']);
$connection = get_db_connection();
$student = get_student_by_user_id($user['id']);

// Get attendance heatmap data
$attendanceData = get_attendance_heatmap_data($student['id'], 12);

// Get subject performance breakdown
$subjectPerformance = get_subject_performance_breakdown($student['id']);

// Calculate GPA
$gpa = calculate_student_gpa($student['id'], $connection);

// Check if at-risk
$atRisk = is_student_at_risk($student['id'], $connection);
?>

<div class="card">
    <h1>Academic Analytics</h1>
    <p class="text-muted">Detailed performance insights and attendance patterns</p>
</div>

<!-- Attendance Heatmap Section -->
<div class="card">
    <h2>Attendance Heatmap (Last 12 Weeks)</h2>
    <p class="text-muted">Visual representation of your attendance patterns</p>
    
    <?php if (empty($attendanceData)): ?>
        <div class="alert alert--info">
            No attendance records found for the selected period.
        </div>
    <?php else: ?>
        <div class="heatmap-container">
            <div class="heatmap-legend">
                <span class="legend-item"><span class="legend-color legend-present"></span> Present</span>
                <span class="legend-item"><span class="legend-color legend-late"></span> Late</span>
                <span class="legend-item"><span class="legend-color legend-absent"></span> Absent</span>
                <span class="legend-item"><span class="legend-color legend-excused"></span> Excused</span>
            </div>
            
            <div class="heatmap-grid">
                <?php
                // Group data by week and day
                $heatmapGrid = [];
                $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                
                foreach ($attendanceData as $record) {
                    $week = $record['week_number'];
                    $day = $record['day_of_week'] - 1; // Convert to 0-6
                    if (!isset($heatmapGrid[$week])) {
                        $heatmapGrid[$week] = array_fill(0, 7, null);
                    }
                    $heatmapGrid[$week][$day] = $record['status'];
                }
                
                // Get unique weeks sorted
                $weeks = array_keys($heatmapGrid);
                sort($weeks);
                
                // Display header with day names
                echo '<div class="heatmap-row">';
                echo '<div class="heatmap-cell heatmap-header"></div>';
                foreach ($dayNames as $dayName) {
                    echo '<div class="heatmap-cell heatmap-header">' . htmlspecialchars($dayName) . '</div>';
                }
                echo '</div>';
                
                // Display each week
                foreach ($weeks as $week) {
                    echo '<div class="heatmap-row">';
                    echo '<div class="heatmap-cell heatmap-header">Week ' . htmlspecialchars($week) . '</div>';
                    for ($day = 0; $day < 7; $day++) {
                        $status = $heatmapGrid[$week][$day] ?? null;
                        $class = 'heatmap-cell';
                        if ($status) {
                            $class .= ' heatmap-' . strtolower($status);
                        } else {
                            $class .= ' heatmap-empty';
                        }
                        echo '<div class="' . $class . '" title="' . htmlspecialchars($status ?? 'No record') . '"></div>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
            
            <div class="heatmap-stats">
                <div class="stat">
                    <h3><?php echo $student['attendance']; ?>%</h3>
                    <p>Overall Attendance</p>
                </div>
                <div class="stat">
                    <h3><?php echo count($attendanceData); ?></h3>
                    <p>Total Records</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Subject Performance Breakdown Section -->
<div class="card">
    <h2>Subject Performance Breakdown</h2>
    <p class="text-muted">Detailed analysis of your performance by subject</p>
    
    <?php if (empty($subjectPerformance)): ?>
        <div class="alert alert--info">
            No grade records found. Please ask your teacher to encode your grades.
        </div>
    <?php else: ?>
        <div class="subject-performance-container">
            <?php foreach ($subjectPerformance as $subject): ?>
                <div class="subject-card">
                    <h3><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                    <div class="subject-stats">
                        <div class="subject-stat">
                            <span class="stat-label">Assignments</span>
                            <span class="stat-value"><?php echo $subject['avg_assignment']; ?>%</span>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $subject['avg_assignment']; ?>%;"></div>
                            </div>
                        </div>
                        <div class="subject-stat">
                            <span class="stat-label">Quizzes</span>
                            <span class="stat-value"><?php echo $subject['avg_quiz']; ?>%</span>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $subject['avg_quiz']; ?>%;"></div>
                            </div>
                        </div>
                        <div class="subject-stat">
                            <span class="stat-label">Exams</span>
                            <span class="stat-value"><?php echo $subject['avg_exam']; ?>%</span>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $subject['avg_exam']; ?>%;"></div>
                            </div>
                        </div>
                        <div class="subject-stat">
                            <span class="stat-label">Final Grade</span>
                            <span class="stat-value stat-value--highlight"><?php echo $subject['avg_final']; ?>%</span>
                            <div class="progress-bar">
                                <div class="progress-fill progress-fill--highlight" style="width: <?php echo $subject['avg_final']; ?>%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="subject-meta">
                        <span class="meta-item"><?php echo $subject['grade_count']; ?> grade(s) recorded</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="performance-summary">
            <div class="summary-card">
                <h3>Overall GPA</h3>
                <div class="gpa-display">
                    <span class="gpa-value"><?php echo $gpa; ?></span>
                    <span class="gpa-scale">/ 4.0</span>
                </div>
            </div>
            
            <div class="summary-card">
                <h3>Academic Status</h3>
                <div class="status-display">
                    <?php if ($atRisk): ?>
                        <span class="status-badge status-badge--warning">At Risk</span>
                        <p class="text-muted small">Your predicted grade is below 70%. Consider increasing study hours and attendance.</p>
                    <?php else: ?>
                        <span class="status-badge status-badge--success">On Track</span>
                        <p class="text-muted small">You are maintaining good academic standing.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
