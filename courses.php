<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Handle filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$instructor_filter = isset($_GET['instructor']) ? $_GET['instructor'] : '';

// Build query
$query = "SELECT 
            c.course_id,
            c.title,
            c.description,
            c.price,
            c.created_at,
            cc.name as category_name,
            cc.category_id,
            u.name as instructor_name,
            u.user_id as instructor_id,
            COUNT(DISTINCT e.enrollment_id) as enrolled_count,
            AVG(r.rating) as avg_rating,
            COUNT(DISTINCT r.review_id) as review_count
          FROM courses c
          INNER JOIN course_categories cc ON c.category_id = cc.category_id
          INNER JOIN users u ON c.instructor_id = u.user_id
          LEFT JOIN enrollments e ON c.course_id = e.course_id
          LEFT JOIN reviews r ON c.course_id = r.course_id
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (c.title LIKE :search OR c.description LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($category_filter)) {
    $query .= " AND c.category_id = :category";
    $params[':category'] = $category_filter;
}

if (!empty($instructor_filter)) {
    $query .= " AND c.instructor_id = :instructor";
    $params[':instructor'] = $instructor_filter;
}

$query .= " GROUP BY c.course_id ORDER BY c.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Get categories for filter
$cat_stmt = $conn->query("SELECT category_id, name FROM course_categories ORDER BY name");
$categories = $cat_stmt->fetchAll();

// Get instructors for filter
$inst_stmt = $conn->query("SELECT user_id, name FROM users WHERE role = 'instructor' ORDER BY name");
$instructors = $inst_stmt->fetchAll();

// Get statistics
$stats_stmt = $conn->query("
    SELECT 
        COUNT(*) as total_courses,
        SUM(price) as total_value,
        AVG(price) as avg_price
    FROM courses
");
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - LMS</title>
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

        .course-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border-top: 3px solid var(--primary-color);
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .filter-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .price-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: #10b981;
        }

        .rating-stars {
            color: #fbbf24;
        }

        .stat-box {
            background: white;
            padding: 1.2rem;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* SQL Tooltip Styles */
        .sql-tooltip {
            position: relative;
            cursor: help;
        }

        .sql-tooltip .sql-tooltip-text {
            visibility: hidden;
            width: 400px;
            background-color: #1e293b;
            color: #e2e8f0;
            text-align: left;
            border-radius: 8px;
            padding: 0.75rem;
            position: absolute;
            z-index: 1000;
            bottom: 125%;
            left: 50%;
            margin-left: -200px;
            opacity: 0;
            transition: opacity 0.3s;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            font-family: 'Courier New', monospace;
            font-size: 0.7rem;
            line-height: 1.3;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }

        .sql-tooltip .sql-tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #1e293b transparent transparent transparent;
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
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php"><i class="fas fa-users"></i> Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="courses.php"><i class="fas fa-book-open"></i> Courses</a>
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
                    <h1 class="mb-0"><i class="fas fa-book-open text-primary"></i> Course Catalog</h1>
                    <p class="text-muted mb-0">Browse and manage available courses</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="add_course.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Course
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-box sql-tooltip">
                    <i class="fas fa-book fa-2x text-primary mb-2"></i>
                    <h3 class="text-primary mb-0"><?php echo $stats['total_courses']; ?></h3>
                    <p class="text-muted mb-0">Total Courses <i class="fas fa-info-circle small"></i></p>
                    <span class="sql-tooltip-text">
                        <span class="sql-keyword">SELECT COUNT</span>(*) as total_courses
                        <span class="sql-keyword">FROM</span> courses
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box sql-tooltip">
                    <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                    <h3 class="text-success mb-0">$<?php echo number_format($stats['avg_price'], 2); ?></h3>
                    <p class="text-muted mb-0">Average Price <i class="fas fa-info-circle small"></i></p>
                    <span class="sql-tooltip-text">
                        <span class="sql-keyword">SELECT AVG</span>(price) as avg_price
                        <span class="sql-keyword">FROM</span> courses
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box sql-tooltip">
                    <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                    <h3 class="text-info mb-0">$<?php echo number_format($stats['total_value'], 2); ?></h3>
                    <p class="text-muted mb-0">Total Value <i class="fas fa-info-circle small"></i></p>
                    <span class="sql-tooltip-text">
                        <span class="sql-keyword">SELECT SUM</span>(price) as total_value
                        <span class="sql-keyword">FROM</span> courses
                    </span>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" action="courses.php" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search Courses</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by title..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_filter == $cat['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Instructor</label>
                    <select name="instructor" class="form-select">
                        <option value="">All Instructors</option>
                        <?php foreach ($instructors as $inst): ?>
                            <option value="<?php echo $inst['user_id']; ?>" <?php echo $instructor_filter == $inst['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($inst['name']); ?>
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

        <!-- Courses Grid -->
        <div class="row">
            <?php if (count($courses) > 0): ?>
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="course-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($course['category_name']); ?></span>
                                <span class="price-tag">$<?php echo number_format($course['price'], 2); ?></span>
                            </div>

                            <h5 class="mb-2"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="text-muted small mb-3">
                                <?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?>
                            </p>

                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-user text-primary"></i>
                                    <?php echo htmlspecialchars($course['instructor_name']); ?>
                                </small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <?php if ($course['avg_rating']): ?>
                                        <span class="rating-stars">
                                            <?php
                                            $rating = round($course['avg_rating'], 1);
                                            echo str_repeat('★', floor($rating));
                                            if ($rating - floor($rating) >= 0.5)
                                                echo '½';
                                            ?>
                                        </span>
                                        <small class="text-muted">(<?php echo number_format($rating, 1); ?>)</small>
                                    <?php else: ?>
                                        <small class="text-muted">No ratings yet</small>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-users"></i> <?php echo $course['enrolled_count']; ?> students
                                </small>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="view_course.php?id=<?php echo $course['course_id']; ?>"
                                    class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="edit_course.php?id=<?php echo $course['course_id']; ?>"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_course.php?id=<?php echo $course['course_id']; ?>"
                                    class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-book fa-4x text-muted mb-3"></i>
                        <h3 class="text-muted">No Courses Found</h3>
                        <p class="text-muted">Try adjusting your search or filter criteria.</p>
                        <a href="courses.php" class="btn btn-primary me-2">Clear Filters</a>
                        <a href="add_course.php" class="btn btn-success">Create Course</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4 mb-4">
            <p class="text-muted">Showing <?php echo count($courses); ?> course(s)</p>
        </div>

        <!-- SQL Query Display -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-code"></i> SQL Query Used</h5>
            </div>
            <div class="card-body">
                <?php
                displaySQL($query, "Courses Query with JOIN, GROUP BY, and Aggregates", "courses_main_query");
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