<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get statistics
$stats = [];

// Total Users by Role
$student_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$stmt = $conn->query($student_sql);
$stats['total_students'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'instructor'");
$stats['total_instructors'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$stats['total_admins'] = $stmt->fetch()['total'];

// Total Active Courses
$stmt = $conn->query("SELECT COUNT(*) as total FROM courses");
$stats['total_courses'] = $stmt->fetch()['total'];

// Total Enrollments
$stmt = $conn->query("SELECT COUNT(*) as total FROM enrollments");
$stats['total_enrollments'] = $stmt->fetch()['total'];

// Total Assignments
$stmt = $conn->query("SELECT COUNT(*) as total FROM assignments");
$stats['total_assignments'] = $stmt->fetch()['total'];

// Average Course Rating
$stmt = $conn->query("SELECT AVG(rating) as avg_rating FROM reviews");
$avg_rating = $stmt->fetch()['avg_rating'];
$stats['avg_rating'] = $avg_rating ? number_format($avg_rating, 1) : '0.0';

// Total Revenue
$stmt = $conn->query("
    SELECT SUM(c.price) as total_revenue
    FROM enrollments e
    INNER JOIN courses c ON e.course_id = c.course_id
");
$revenue = $stmt->fetch()['total_revenue'];
$stats['total_revenue'] = $revenue ? number_format($revenue, 2) : '0.00';

// Recent Users
$stmt = $conn->query("
    SELECT user_id, name, email, role, created_at
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 6
");
$recent_users = $stmt->fetchAll();

// Top Rated Courses
$stmt = $conn->query("
    SELECT 
        c.course_id,
        c.title,
        c.price,
        u.name as instructor_name,
        AVG(r.rating) as avg_rating,
        COUNT(DISTINCT e.enrollment_id) as total_enrollments
    FROM courses c
    INNER JOIN users u ON c.instructor_id = u.user_id
    LEFT JOIN reviews r ON c.course_id = r.course_id
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    GROUP BY c.course_id
    HAVING avg_rating IS NOT NULL
    ORDER BY avg_rating DESC, total_enrollments DESC
    LIMIT 5
");
$top_courses = $stmt->fetchAll();

// Category Statistics
$stmt = $conn->query("
    SELECT 
        cc.name as category_name,
        COUNT(DISTINCT c.course_id) AS total_courses,
        COUNT(DISTINCT e.enrollment_id) AS total_enrollments,
        AVG(c.price) as avg_price
    FROM course_categories cc
    LEFT JOIN courses c ON cc.category_id = c.category_id
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    GROUP BY cc.category_id
    ORDER BY total_enrollments DESC
");
$category_stats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Management System - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --accent-color: #ec4899;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            overflow: visible;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-card .card-body {
            padding: 1.5rem;
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.9;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .table-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .table-card .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1rem 1.5rem;
            border: none;
        }

        .table thead th {
            border-bottom: 2px solid var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .badge-custom {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        }

        .rating-stars {
            color: #fbbf24;
        }

        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
        }

        /* SQL Tooltip Styles */
        .sql-tooltip {
            position: relative;
            display: inline-block;
        }

        .sql-tooltip .tooltip-trigger {
            cursor: help;
            color: inherit;
        }

        .sql-tooltip .sql-tooltip-text {
            visibility: hidden;
            width: 450px;
            background-color: #1e293b;
            color: #e2e8f0;
            text-align: left;
            border-radius: 8px;
            padding: 15px;
            position: fixed;
            z-index: 99999;
            opacity: 0;
            transition: opacity 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
            pointer-events: none;
            border: 1px solid #334155;
        }

        .sql-tooltip:hover .sql-tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        .sql-keyword {
            color: #60a5fa;
            font-weight: bold;
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
                        <a class="nav-link active" href="index.php"><i class="fas fa-home"></i> Dashboard</a>
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
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h1><i class="fas fa-rocket"></i> Welcome to Learning Management System</h1>
            <p class="mb-0 fs-5">Manage online courses, students, instructors, assignments, and track performance all in
                one place.</p>
            <div class="mt-3">
                <a href="sql_operations.php" class="btn btn-light btn-lg">
                    <i class="fas fa-database"></i> View SQL Operations Demo
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-user-graduate stat-icon"></i>
                        <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                        <div class="stat-label text-white-50">Total Students</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-book-open stat-icon"></i>
                        <div class="stat-value"><?php echo $stats['total_courses']; ?></div>
                        <div class="stat-label text-white-50">Total Courses</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-teacher stat-icon"></i>
                        <div class="stat-value"><?php echo $stats['total_instructors']; ?></div>
                        <div class="stat-label text-white-50">Instructors</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-clipboard-list stat-icon"></i>
                        <div class="stat-value"><?php echo $stats['total_enrollments']; ?></div>
                        <div class="stat-label text-white-50">Enrollments</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-tasks stat-icon"></i>
                        <div class="stat-value"><?php echo $stats['total_assignments']; ?></div>
                        <div class="stat-label text-white-50">Assignments</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card bg-dark text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-star stat-icon"></i>
                        <div class="stat-value"><?php echo $stats['avg_rating']; ?></div>
                        <div class="stat-label text-white-50">Avg Course Rating</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body text-center text-white">
                        <i class="fas fa-dollar-sign stat-icon"></i>
                        <div class="stat-value">$<?php echo $stats['total_revenue']; ?></div>
                        <div class="stat-label text-white-50">Total Revenue</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="card-body text-center text-white">
                        <i class="fas fa-user-shield stat-icon"></i>
                        <div class="stat-value"><?php echo $stats['total_admins']; ?></div>
                        <div class="stat-label text-white-50">Administrators</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SQL Queries for Statistics -->
        <div class="mb-4">
            <h4 class="mb-3"><i class="fas fa-code"></i> SQL Queries for Dashboard Statistics</h4>
            <?php
            // Display SQL for each statistic
            displaySQL("SELECT COUNT(*) as total FROM users WHERE role = 'student'", "Query - Total Students", "stat_students");
            displaySQL("SELECT COUNT(*) as total FROM courses", "Query - Total Courses", "stat_courses");
            displaySQL("SELECT COUNT(*) as total FROM users WHERE role = 'instructor'", "Query - Total Instructors", "stat_instructors");
            displaySQL("SELECT COUNT(*) as total FROM enrollments", "Query - Total Enrollments", "stat_enrollments");
            displaySQL("SELECT COUNT(*) as total FROM assignments", "Query - Total Assignments", "stat_assignments");
            displaySQL("SELECT AVG(rating) as avg_rating FROM reviews", "Query - Average Course Rating", "stat_avg_rating");
            displaySQL("SELECT SUM(c.price) as total_revenue
FROM enrollments e
INNER JOIN courses c ON e.course_id = c.course_id", "Query - Total Revenue", "stat_revenue");
            displaySQL("SELECT COUNT(*) as total FROM users WHERE role = 'admin'", "Query - Total Administrators", "stat_admins");
            ?>
        </div>

        <div class="row">
            <!-- Recent Users -->
            <div class="col-lg-6 mb-4">
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-plus"></i> Recent Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['user_id']; ?></td>
                                            <td>
                                                <div><?php echo htmlspecialchars($user['name']); ?></div>
                                                <small
                                                    class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $role_class = 'secondary';
                                                if ($user['role'] == 'instructor')
                                                    $role_class = 'info';
                                                if ($user['role'] == 'student')
                                                    $role_class = 'primary';
                                                if ($user['role'] == 'admin')
                                                    $role_class = 'danger';
                                                ?>
                                                <span class="badge bg-<?php echo $role_class; ?> role-badge">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><small><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="users.php" class="btn btn-primary"><i class="fas fa-eye"></i> View All Users</a>
                        </div>
                        <div class="mt-3">
                            <?php
                            $recent_users_sql = "SELECT user_id, name, email, role, created_at
FROM users 
ORDER BY created_at DESC 
LIMIT 6";
                            displaySQL($recent_users_sql, "SQL Query - Recent Users", "dash_recent_users");
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Rated Courses -->
            <div class="col-lg-6 mb-4">
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-trophy"></i> Top Rated Courses</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Instructor</th>
                                        <th>Rating</th>
                                        <th>Students</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_courses as $course): ?>
                                        <tr>
                                            <td>
                                                <div><strong><?php echo htmlspecialchars($course['title']); ?></strong>
                                                </div>
                                                <small
                                                    class="text-success fw-bold">$<?php echo number_format($course['price'], 2); ?></small>
                                            </td>
                                            <td><small><?php echo htmlspecialchars($course['instructor_name']); ?></small>
                                            </td>
                                            <td>
                                                <span class="rating-stars">
                                                    <?php
                                                    $rating = round($course['avg_rating'], 1);
                                                    echo str_repeat('★', floor($rating));
                                                    if ($rating - floor($rating) >= 0.5)
                                                        echo '½';
                                                    ?>
                                                </span>
                                                <small class="text-muted">(<?php echo number_format($rating, 1); ?>)</small>
                                            </td>
                                            <td><span
                                                    class="badge bg-primary badge-custom"><?php echo $course['total_enrollments']; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="courses.php" class="btn btn-success"><i class="fas fa-eye"></i> View All
                                Courses</a>
                        </div>
                        <div class="mt-3">
                            <?php
                            $top_courses_sql = "SELECT 
    c.course_id,
    c.title,
    c.price,
    u.name as instructor_name,
    AVG(r.rating) as avg_rating,
    COUNT(DISTINCT e.enrollment_id) as total_enrollments
FROM courses c
INNER JOIN users u ON c.instructor_id = u.user_id
LEFT JOIN reviews r ON c.course_id = r.course_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
GROUP BY c.course_id
HAVING avg_rating IS NOT NULL
ORDER BY avg_rating DESC, total_enrollments DESC
LIMIT 5";
                            displaySQL($top_courses_sql, "SQL Query - Top Rated Courses", "dash_top_courses");
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Statistics -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Course Categories Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Total Courses</th>
                                        <th>Total Enrollments</th>
                                        <th>Average Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($category_stats as $category): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($category['category_name']); ?></strong>
                                            </td>
                                            <td><span
                                                    class="badge bg-success badge-custom"><?php echo $category['total_courses']; ?></span>
                                            </td>
                                            <td><span
                                                    class="badge bg-info badge-custom"><?php echo $category['total_enrollments']; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($category['avg_price']): ?>
                                                    <span
                                                        class="text-success fw-bold">$<?php echo number_format($category['avg_price'], 2); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="categories.php" class="btn btn-primary"><i class="fas fa-eye"></i> View All
                                Categories</a>
                        </div>
                        <div class="mt-3">
                            <?php
                            $category_stats_sql = "SELECT 
    cc.name as category_name,
    COUNT(DISTINCT c.course_id) AS total_courses,
    COUNT(DISTINCT e.enrollment_id) AS total_enrollments,
    AVG(c.price) as avg_price
FROM course_categories cc
LEFT JOIN courses c ON cc.category_id = c.category_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
GROUP BY cc.category_id
ORDER BY total_enrollments DESC";
                            displaySQL($category_stats_sql, "SQL Query - Category Statistics", "dash_category_stats");
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card table-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="add_user.php" class="btn btn-primary w-100 p-3">
                            <i class="fas fa-user-plus fa-2x d-block mb-2"></i>
                            Add New User
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="add_course.php" class="btn btn-success w-100 p-3">
                            <i class="fas fa-book-medical fa-2x d-block mb-2"></i>
                            Create New Course
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="add_enrollment.php" class="btn btn-info w-100 p-3">
                            <i class="fas fa-clipboard-check fa-2x d-block mb-2"></i>
                            Enroll Student
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="add_assignment.php" class="btn btn-warning w-100 p-3">
                            <i class="fas fa-tasks fa-2x d-block mb-2"></i>
                            Create Assignment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted py-4 mt-5">
        <p>&copy; 2024 Learning Management System. Demonstrating MySQL/SQL Features with Modern LMS Platform.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Position tooltips near cursor
        document.querySelectorAll('.sql-tooltip').forEach(function (tooltip) {
            tooltip.addEventListener('mouseenter', function (e) {
                const tooltipText = this.querySelector('.sql-tooltip-text');
                if (tooltipText) {
                    const rect = e.target.getBoundingClientRect();
                    tooltipText.style.left = (rect.left + window.scrollX - 200) + 'px';
                    tooltipText.style.top = (rect.top + window.scrollY - tooltipText.offsetHeight - 10) + 'px';
                }
            });
        });
    </script>
</body>

</html>