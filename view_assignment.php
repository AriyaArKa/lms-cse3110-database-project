<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();

$assignment_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get assignment details
$assignment_sql = "SELECT 
            a.*,
            c.title as course_title,
            c.course_id,
            u.name as instructor_name,
            u.user_id as instructor_id
        FROM assignments a
        INNER JOIN courses c ON a.course_id = c.course_id
        INNER JOIN users u ON c.instructor_id = u.user_id
        WHERE a.assignment_id = :id";
$stmt = $conn->prepare($assignment_sql);
$stmt->execute([':id' => $assignment_id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    header('Location: assignments.php');
    exit;
}

// Get submission statistics
$submission_stats_sql = "SELECT 
            COUNT(*) as total_submissions,
            AVG(grade) as avg_grade,
            MAX(grade) as max_grade,
            MIN(grade) as min_grade,
            SUM(CASE WHEN grade IS NULL THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN grade >= 90 THEN 1 ELSE 0 END) as a_grade,
            SUM(CASE WHEN grade >= 80 AND grade < 90 THEN 1 ELSE 0 END) as b_grade,
            SUM(CASE WHEN grade >= 70 AND grade < 80 THEN 1 ELSE 0 END) as c_grade,
            SUM(CASE WHEN grade < 70 AND grade IS NOT NULL THEN 1 ELSE 0 END) as below_c
        FROM submissions
        WHERE assignment_id = :id";
$stmt = $conn->prepare($submission_stats_sql);
$stmt->execute([':id' => $assignment_id]);
$stats = $stmt->fetch();

// Get all submissions
$submissions_sql = "SELECT 
            s.submission_id,
            s.submitted_at,
            s.grade,
            u.user_id,
            u.name as student_name,
            u.email as student_email
        FROM submissions s
        INNER JOIN users u ON s.student_id = u.user_id
        WHERE s.assignment_id = :id
        ORDER BY s.submitted_at DESC";
$stmt = $conn->prepare($submissions_sql);
$stmt->execute([':id' => $assignment_id]);
$submissions = $stmt->fetchAll();

$due_date = new DateTime($assignment['due_date']);
$now = new DateTime();
$is_overdue = $due_date < $now;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignment - <?php echo htmlspecialchars($assignment['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .assignment-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-box {
            text-align: center;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="fas fa-graduation-cap"></i> LMS Platform</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item"><a class="nav-link active" href="assignments.php"><i class="fas fa-tasks"></i>
                            Assignments</a></li>
                    <li class="nav-item"><a class="nav-link" href="sql_operations.php"><i class="fas fa-database"></i>
                            SQL Demo</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="mb-3">
            <a href="assignments.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left"></i> Back to
                Assignments</a>
        </div>

        <!-- Assignment Header -->
        <div class="assignment-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <?php if ($is_overdue): ?>
                        <span class="badge bg-danger mb-2">OVERDUE</span>
                    <?php else: ?>
                        <span class="badge bg-success mb-2">ACTIVE</span>
                    <?php endif; ?>
                    <h1 class="mb-3"><?php echo htmlspecialchars($assignment['title']); ?></h1>
                    <p class="lead mb-2"><?php echo htmlspecialchars($assignment['description']); ?></p>
                    <p class="mb-0">
                        <i class="fas fa-book"></i> Course: <strong><a
                                href="view_course.php?id=<?php echo $assignment['course_id']; ?>"
                                class="text-white"><?php echo htmlspecialchars($assignment['course_title']); ?></a></strong>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-user"></i> Instructor: <strong><a
                                href="view_user.php?id=<?php echo $assignment['instructor_id']; ?>"
                                class="text-white"><?php echo htmlspecialchars($assignment['instructor_name']); ?></a></strong>
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="bg-white text-dark p-3 rounded">
                        <h5>Due Date</h5>
                        <h3 class="mb-0 <?php echo $is_overdue ? 'text-danger' : 'text-success'; ?>">
                            <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?>
                        </h3>
                        <small><?php echo date('g:i A', strtotime($assignment['due_date'])); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0"><?php echo $stats['total_submissions']; ?></h3>
                    <small class="text-muted">Total Submissions</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                    <h3 class="mb-0">
                        <?php echo $stats['avg_grade'] ? number_format($stats['avg_grade'], 1) . '%' : 'N/A'; ?>
                    </h3>
                    <small class="text-muted">Average Grade</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0"><?php echo $stats['pending_count']; ?></h3>
                    <small class="text-muted">Pending Grading</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-trophy fa-2x text-info mb-2"></i>
                    <h3 class="mb-0"><?php echo $stats['a_grade']; ?></h3>
                    <small class="text-muted">A Grades (90%+)</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Submissions List -->
                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-list text-primary"></i> All Submissions</h4>
                    <?php if (count($submissions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Submitted Date</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $submission): ?>
                                        <tr>
                                            <td>
                                                <a
                                                    href="view_user.php?id=<?php echo $submission['user_id']; ?>"><?php echo htmlspecialchars($submission['student_name']); ?></a><br>
                                                <small
                                                    class="text-muted"><?php echo htmlspecialchars($submission['student_email']); ?></small>
                                            </td>
                                            <td><?php echo date('M d, Y g:i A', strtotime($submission['submitted_at'])); ?></td>
                                            <td>
                                                <?php if ($submission['grade'] !== null): ?>
                                                    <strong
                                                        class="text-success"><?php echo number_format($submission['grade'], 1); ?>%</strong>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($submission['grade'] !== null): ?>
                                                    <span class="badge bg-success">Graded</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">No submissions yet.</p>
                    <?php endif; ?>
                    <?php displaySQL($submissions_sql, "SQL - All Submissions", "assignment_submissions"); ?>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Grade Distribution -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-chart-pie text-info"></i> Grade Distribution</h5>
                    <?php if ($stats['total_submissions'] > 0): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>A (90-100%)</span>
                                <strong class="text-success"><?php echo $stats['a_grade']; ?></strong>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success"
                                    style="width: <?php echo ($stats['a_grade'] / $stats['total_submissions']) * 100; ?>%">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mb-1">
                                <span>B (80-89%)</span>
                                <strong class="text-info"><?php echo $stats['b_grade']; ?></strong>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-info"
                                    style="width: <?php echo ($stats['b_grade'] / $stats['total_submissions']) * 100; ?>%">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mb-1">
                                <span>C (70-79%)</span>
                                <strong class="text-warning"><?php echo $stats['c_grade']; ?></strong>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-warning"
                                    style="width: <?php echo ($stats['c_grade'] / $stats['total_submissions']) * 100; ?>%">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mb-1">
                                <span>Below C (&lt;70%)</span>
                                <strong class="text-danger"><?php echo $stats['below_c']; ?></strong>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-danger"
                                    style="width: <?php echo ($stats['below_c'] / $stats['total_submissions']) * 100; ?>%">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mb-1">
                                <span>Pending</span>
                                <strong class="text-secondary"><?php echo $stats['pending_count']; ?></strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-secondary"
                                    style="width: <?php echo ($stats['pending_count'] / $stats['total_submissions']) * 100; ?>%">
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No submissions to display</p>
                    <?php endif; ?>
                    <?php displaySQL($submission_stats_sql, "SQL - Submission Statistics", "submission_stats"); ?>
                </div>

                <!-- Assignment Details -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Assignment Details</h5>
                    <p><strong>Assignment ID:</strong> #<?php echo $assignment['assignment_id']; ?></p>
                    <p><strong>Course:</strong> <a
                            href="view_course.php?id=<?php echo $assignment['course_id']; ?>"><?php echo htmlspecialchars($assignment['course_title']); ?></a>
                    </p>
                    <p><strong>Instructor:</strong> <a
                            href="view_user.php?id=<?php echo $assignment['instructor_id']; ?>"><?php echo htmlspecialchars($assignment['instructor_name']); ?></a>
                    </p>
                    <p><strong>Due Date:</strong> <span
                            class="<?php echo $is_overdue ? 'text-danger' : 'text-success'; ?>"><?php echo date('F d, Y g:i A', strtotime($assignment['due_date'])); ?></span>
                    </p>
                    <?php if (isset($assignment['created_at']) && $assignment['created_at']): ?>
                        <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($assignment['created_at'])); ?></p>
                    <?php endif; ?>
                    <?php displaySQL($assignment_sql, "SQL - Assignment Details", "assignment_details"); ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted py-4 mt-5">
        <p>&copy; 2024 Learning Management System</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>