<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$student_filter = isset($_GET['student']) ? $_GET['student'] : '';

$query = "SELECT 
            e.enrollment_id,
            e.enrolled_at,
            e.progress,
            u.name as student_name,
            u.user_id as student_id,
            u.email as student_email,
            c.course_id,
            c.title as course_title,
            c.price,
            inst.name as instructor_name,
            cat.name as category_name
          FROM enrollments e
          INNER JOIN users u ON e.student_id = u.user_id
          INNER JOIN courses c ON e.course_id = c.course_id
          INNER JOIN users inst ON c.instructor_id = inst.user_id
          INNER JOIN course_categories cat ON c.category_id = cat.category_id
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE :search OR c.title LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($course_filter)) {
    $query .= " AND e.course_id = :course";
    $params[':course'] = $course_filter;
}

if (!empty($student_filter)) {
    $query .= " AND e.student_id = :student";
    $params[':student'] = $student_filter;
}

$query .= " ORDER BY e.enrolled_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$enrollments = $stmt->fetchAll();

// Get courses for filter
$course_stmt = $conn->query("SELECT course_id, title FROM courses ORDER BY title");
$courses = $course_stmt->fetchAll();

// Get students for filter
$student_stmt = $conn->query("SELECT user_id, name FROM users WHERE role = 'student' ORDER BY name");
$students = $student_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments - LMS</title>
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

        .progress {
            height: 25px;
            border-radius: 12px;
        }

        .progress-bar {
            font-weight: 600;
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
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users"></i> Users</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="courses.php"><i class="fas fa-book-open"></i>
                            Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories.php"><i class="fas fa-tags"></i>
                            Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="assignments.php"><i class="fas fa-tasks"></i>
                            Assignments</a></li>
                    <li class="nav-item"><a class="nav-link active" href="enrollments.php"><i
                                class="fas fa-clipboard-list"></i> Enrollments</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="sql_operations.php"><i class="fas fa-database"></i> SQL Demo</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 px-4">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0"><i class="fas fa-clipboard-list text-primary"></i> Student Enrollments</h1>
                    <p class="text-muted mb-0">Track student course enrollments and progress</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="add_enrollment.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Enrollment
                    </a>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="enrollments.php" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by student or course..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Course</label>
                    <select name="course" class="form-select">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>" <?php echo $course_filter == $course['course_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
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

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Category</th>
                            <th>Instructor</th>
                            <th>Price</th>
                            <th>Enrolled Date</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($enrollments) > 0): ?>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <tr>
                                    <td><strong>#<?php echo $enrollment['enrollment_id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($enrollment['student_name']); ?></strong><br>
                                        <small
                                            class="text-muted"><?php echo htmlspecialchars($enrollment['student_email']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($enrollment['course_title']); ?></strong>
                                    </td>
                                    <td><span
                                            class="badge bg-secondary"><?php echo htmlspecialchars($enrollment['category_name']); ?></span>
                                    </td>
                                    <td><small><?php echo htmlspecialchars($enrollment['instructor_name']); ?></small></td>
                                    <td><strong
                                            class="text-success">$<?php echo number_format($enrollment['price'], 2); ?></strong>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                                    <td style="min-width: 150px;">
                                        <div class="progress">
                                            <?php
                                            $progress = $enrollment['progress'];
                                            $progress_class = 'bg-danger';
                                            if ($progress >= 80)
                                                $progress_class = 'bg-success';
                                            elseif ($progress >= 50)
                                                $progress_class = 'bg-info';
                                            elseif ($progress >= 25)
                                                $progress_class = 'bg-warning';
                                            ?>
                                            <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar"
                                                style="width: <?php echo $progress; ?>%"
                                                aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $progress; ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="view_enrollment.php?id=<?php echo $enrollment['enrollment_id']; ?>"
                                            class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_enrollment.php?id=<?php echo $enrollment['enrollment_id']; ?>"
                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_enrollment.php?id=<?php echo $enrollment['enrollment_id']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this enrollment?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No enrollments found.</p>
                                    <a href="enrollments.php" class="btn btn-primary">Clear Filters</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mt-4 mb-4">
            <p class="text-muted">Showing <?php echo count($enrollments); ?> enrollment(s)</p>
        </div>

        <!-- SQL Query Display -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="fas fa-code"></i> SQL Query Used</h5>
            </div>
            <div class="card-body">
                <?php
                displaySQL($query, "Enrollments Query with Multiple JOINs", "enrollments_main_query");
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