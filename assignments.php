<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();

// Handle filters
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT 
            a.assignment_id,
            a.title,
            a.description,
            a.due_date,
            c.title as course_title,
            c.course_id,
            u.name as instructor_name,
            COUNT(DISTINCT s.submission_id) as submission_count,
            AVG(s.grade) as avg_grade
          FROM assignments a
          INNER JOIN courses c ON a.course_id = c.course_id
          INNER JOIN users u ON c.instructor_id = u.user_id
          LEFT JOIN submissions s ON a.assignment_id = s.assignment_id
          WHERE 1=1";

$params = [];

if (!empty($course_filter)) {
    $query .= " AND a.course_id = :course";
    $params[':course'] = $course_filter;
}

if (!empty($search)) {
    $query .= " AND (a.title LIKE :search OR a.description LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " GROUP BY a.assignment_id ORDER BY a.due_date ASC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$assignments = $stmt->fetchAll();

// Get courses for filter
$courses_stmt = $conn->query("SELECT course_id, title FROM courses ORDER BY title");
$courses = $courses_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - LMS</title>
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

        .assignment-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .assignment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .assignment-card.overdue {
            border-left-color: #ef4444;
        }

        .assignment-card.upcoming {
            border-left-color: #f59e0b;
        }

        .filter-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
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
                        <a class="nav-link active" href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a>
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
                    <h1 class="mb-0"><i class="fas fa-tasks text-primary"></i> Assignments</h1>
                    <p class="text-muted mb-0">Manage course assignments and submissions</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="add_assignment.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Assignment
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" action="assignments.php" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Search Assignments</label>
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by title or description..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-5">
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
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Assignments List -->
        <div class="row">
            <?php if (count($assignments) > 0): ?>
                <?php
                $now = new DateTime();
                foreach ($assignments as $assignment):
                    $due_date = new DateTime($assignment['due_date']);
                    $is_overdue = $due_date < $now;
                    $days_until = $now->diff($due_date)->days;
                    $is_upcoming = $days_until <= 7 && !$is_overdue;

                    $card_class = '';
                    if ($is_overdue)
                        $card_class = 'overdue';
                    elseif ($is_upcoming)
                        $card_class = 'upcoming';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="assignment-card <?php echo $card_class; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="mb-0"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                                <?php if ($is_overdue): ?>
                                    <span class="badge bg-danger">Overdue</span>
                                <?php elseif ($is_upcoming): ?>
                                    <span class="badge bg-warning">Due Soon</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </div>

                            <p class="text-muted small mb-2">
                                <i class="fas fa-book text-primary"></i>
                                <strong><?php echo htmlspecialchars($assignment['course_title']); ?></strong>
                            </p>

                            <p class="text-muted small mb-3">
                                <?php echo htmlspecialchars(substr($assignment['description'], 0, 100)) . '...'; ?>
                            </p>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Due Date</small>
                                    <div>
                                        <strong><?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></strong>
                                    </div>
                                    <small
                                        class="text-muted"><?php echo date('g:i A', strtotime($assignment['due_date'])); ?></small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Submissions</small>
                                    <div>
                                        <strong class="text-primary"><?php echo $assignment['submission_count']; ?></strong>
                                    </div>
                                    <?php if ($assignment['avg_grade']): ?>
                                        <small class="text-success">Avg:
                                            <?php echo number_format($assignment['avg_grade'], 1); ?>%</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="view_assignment.php?id=<?php echo $assignment['assignment_id']; ?>"
                                    class="btn btn-sm btn-info flex-fill">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="submissions.php?assignment=<?php echo $assignment['assignment_id']; ?>"
                                    class="btn btn-sm btn-primary flex-fill">
                                    <i class="fas fa-file-alt"></i> Submissions
                                </a>
                                <a href="edit_assignment.php?id=<?php echo $assignment['assignment_id']; ?>"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_assignment.php?id=<?php echo $assignment['assignment_id']; ?>"
                                    class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this assignment?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                        <h3 class="text-muted">No Assignments Found</h3>
                        <p class="text-muted">Create your first assignment to get started.</p>
                        <a href="add_assignment.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Assignment
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4 mb-4">
            <p class="text-muted">Showing <?php echo count($assignments); ?> assignment(s)</p>
        </div>

        <!-- SQL Query Display -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-code"></i> SQL Query Used</h5>
            </div>
            <div class="card-body">
                <?php
                // Build the actual executed query for display
                $display_query = "SELECT 
            a.assignment_id,
            a.title,
            a.description,
            a.due_date,
            c.title as course_title,
            c.course_id,
            u.name as instructor_name,
            COUNT(DISTINCT s.submission_id) as submission_count,
            AVG(s.grade) as avg_grade
          FROM assignments a
          INNER JOIN courses c ON a.course_id = c.course_id
          INNER JOIN users u ON c.instructor_id = u.user_id
          LEFT JOIN submissions s ON a.assignment_id = s.assignment_id";

                $where_conditions = [];

                if (!empty($course_filter)) {
                    $where_conditions[] = "a.course_id = " . htmlspecialchars($course_filter);
                }

                if (!empty($search)) {
                    $where_conditions[] = "(a.title LIKE '%" . htmlspecialchars($search) . "%' OR a.description LIKE '%" . htmlspecialchars($search) . "%')";
                }

                if (count($where_conditions) > 0) {
                    $display_query .= "\n          WHERE " . implode(" AND ", $where_conditions);
                }

                $display_query .= "\n          GROUP BY a.assignment_id\n          ORDER BY a.due_date ASC";

                displaySQL($display_query, "Assignments Query with JOIN and Aggregates", "assignments_main_query");
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