<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();

// Handle filters
$assignment_filter = isset($_GET['assignment']) ? $_GET['assignment'] : '';
$student_filter = isset($_GET['student']) ? $_GET['student'] : '';

// Build query
$query = "SELECT 
            s.submission_id,
            s.submitted_at,
            s.grade,
            a.title as assignment_title,
            a.assignment_id,
            a.due_date,
            c.title as course_title,
            u.name as student_name,
            u.user_id as student_id
          FROM submissions s
          INNER JOIN assignments a ON s.assignment_id = a.assignment_id
          INNER JOIN courses c ON a.course_id = c.course_id
          INNER JOIN users u ON s.student_id = u.user_id
          WHERE 1=1";

$params = [];

if (!empty($assignment_filter)) {
    $query .= " AND s.assignment_id = :assignment";
    $params[':assignment'] = $assignment_filter;
}

if (!empty($student_filter)) {
    $query .= " AND s.student_id = :student";
    $params[':student'] = $student_filter;
}

$query .= " ORDER BY s.submitted_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

// Get assignments for filter
$assignments_stmt = $conn->query("SELECT assignment_id, title FROM assignments ORDER BY title");
$assignments = $assignments_stmt->fetchAll();

// Get students for filter
$students_stmt = $conn->query("SELECT user_id, name FROM users WHERE role = 'student' ORDER BY name");
$students = $students_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions - LMS</title>
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

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            border: none;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .filter-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .grade-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1rem;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap"></i> LMS Platform
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php"><i class="fas fa-users"></i> Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php"><i class="fas fa-book-open"></i> Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php"><i class="fas fa-tags"></i> Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="enrollments.php"><i class="fas fa-clipboard-list"></i> Enrollments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sql_operations.php"><i class="fas fa-database"></i> SQL Demo</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 px-4">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0"><i class="fas fa-file-alt text-primary"></i> Assignment Submissions</h1>
                    <p class="text-muted mb-0">View and grade student submissions</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="add_submission.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Submission
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" action="submissions.php" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Filter by Assignment</label>
                    <select name="assignment" class="form-select">
                        <option value="">All Assignments</option>
                        <?php foreach ($assignments as $assignment): ?>
                            <option value="<?php echo $assignment['assignment_id']; ?>" <?php echo $assignment_filter == $assignment['assignment_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($assignment['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Filter by Student</label>
                    <select name="student" class="form-select">
                        <option value="">All Students</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['user_id']; ?>" <?php echo $student_filter == $student['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Submissions Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Assignment</th>
                            <th>Course</th>
                            <th>Submitted At</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($submissions) > 0): ?>
                            <?php foreach ($submissions as $submission):
                                $due_date = new DateTime($submission['due_date']);
                                $submitted = new DateTime($submission['submitted_at']);
                                $is_late = $submitted > $due_date;
                                ?>
                                <tr>
                                    <td><strong>#<?php echo $submission['submission_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($submission['assignment_title']); ?></strong>
                                    </td>
                                    <td><small><?php echo htmlspecialchars($submission['course_title']); ?></small></td>
                                    <td>
                                        <?php echo date('M d, Y g:i A', strtotime($submission['submitted_at'])); ?>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y g:i A', strtotime($submission['due_date'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($is_late): ?>
                                            <span class="badge bg-danger">Late</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">On Time</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($submission['grade'] !== null):
                                            $grade = $submission['grade'];
                                            $grade_class = 'success';
                                            if ($grade < 60)
                                                $grade_class = 'danger';
                                            elseif ($grade < 70)
                                                $grade_class = 'warning';
                                            elseif ($grade < 80)
                                                $grade_class = 'info';
                                            ?>
                                            <span class="badge bg-<?php echo $grade_class; ?> grade-badge">
                                                <?php echo number_format($grade, 1); ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Graded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="grade_submission.php?id=<?php echo $submission['submission_id']; ?>"
                                            class="btn btn-sm btn-outline-primary" title="Grade">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                        <a href="delete_submission.php?id=<?php echo $submission['submission_id']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this submission?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No submissions found.</p>
                                    <a href="submissions.php" class="btn btn-primary">Clear Filters</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mt-4 mb-4">
            <p class="text-muted">Showing <?php echo count($submissions); ?> submission(s)</p>
        </div>

        <!-- SQL Query Display -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-code"></i> SQL Query Used</h5>
            </div>
            <div class="card-body">
                <?php
                displaySQL($query, "Submissions Query with Multiple JOINs", "submissions_main_query");
                ?>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted py-4 mt-5">
        <p>&copy; 2024 Learning Management System</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>