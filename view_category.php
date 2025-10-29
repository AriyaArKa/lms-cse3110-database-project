<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();

$category_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get category details
$category_sql = "SELECT * FROM course_categories WHERE category_id = :id";
$stmt = $conn->prepare($category_sql);
$stmt->execute([':id' => $category_id]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: categories.php');
    exit;
}

// Get category statistics
$stats_sql = "SELECT 
            COUNT(DISTINCT c.course_id) as total_courses,
            COUNT(DISTINCT e.enrollment_id) as total_enrollments,
            COUNT(DISTINCT c.instructor_id) as total_instructors,
            AVG(c.price) as avg_price,
            SUM(c.price) as total_value,
            AVG(r.rating) as avg_rating
        FROM course_categories cc
        LEFT JOIN courses c ON cc.category_id = c.category_id
        LEFT JOIN enrollments e ON c.course_id = e.course_id
        LEFT JOIN reviews r ON c.course_id = r.course_id
        WHERE cc.category_id = :id";
$stmt = $conn->prepare($stats_sql);
$stmt->execute([':id' => $category_id]);
$stats = $stmt->fetch();

// Get courses in this category
$courses_sql = "SELECT 
            c.course_id,
            c.title,
            c.description,
            c.price,
            c.created_at,
            u.name as instructor_name,
            u.user_id as instructor_id,
            COUNT(DISTINCT e.enrollment_id) as enrolled_count,
            AVG(r.rating) as avg_rating,
            COUNT(DISTINCT r.review_id) as review_count
        FROM courses c
        INNER JOIN users u ON c.instructor_id = u.user_id
        LEFT JOIN enrollments e ON c.course_id = e.course_id
        LEFT JOIN reviews r ON c.course_id = r.course_id
        WHERE c.category_id = :id
        GROUP BY c.course_id
        ORDER BY enrolled_count DESC, avg_rating DESC";
$stmt = $conn->prepare($courses_sql);
$stmt->execute([':id' => $category_id]);
$courses = $stmt->fetchAll();

// Get top instructors in this category
$instructors_sql = "SELECT 
            u.user_id,
            u.name,
            u.email,
            COUNT(DISTINCT c.course_id) as course_count,
            COUNT(DISTINCT e.enrollment_id) as total_students,
            AVG(r.rating) as avg_rating
        FROM users u
        INNER JOIN courses c ON u.user_id = c.instructor_id
        LEFT JOIN enrollments e ON c.course_id = e.course_id
        LEFT JOIN reviews r ON c.course_id = r.course_id
        WHERE c.category_id = :id
        GROUP BY u.user_id
        ORDER BY total_students DESC, avg_rating DESC
        LIMIT 5";
$stmt = $conn->prepare($instructors_sql);
$stmt->execute([':id' => $category_id]);
$instructors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Category - <?php echo htmlspecialchars($category['name']); ?></title>
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

        .category-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 3rem 2rem;
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

        .course-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .rating-stars {
            color: #fbbf24;
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
                    <li class="nav-item"><a class="nav-link active" href="categories.php"><i class="fas fa-tags"></i>
                            Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="courses.php"><i class="fas fa-book-open"></i>
                            Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="sql_operations.php"><i class="fas fa-database"></i>
                            SQL Demo</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="mb-3">
            <a href="categories.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left"></i> Back to
                Categories</a>
        </div>

        <!-- Category Header -->
        <div class="category-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-3"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($category['name']); ?></h1>
                    <p class="lead mb-0">
                        <?php echo htmlspecialchars($category['description'] ?? 'No description available'); ?>
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="bg-white text-dark p-4 rounded">
                        <i class="fas fa-book fa-3x text-warning mb-2"></i>
                        <h2 class="mb-0"><?php echo $stats['total_courses']; ?></h2>
                        <small>Courses Available</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-book fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0"><?php echo $stats['total_courses']; ?></h3>
                    <small class="text-muted">Total Courses</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-users fa-2x text-success mb-2"></i>
                    <h3 class="mb-0"><?php echo $stats['total_enrollments']; ?></h3>
                    <small class="text-muted">Total Enrollments</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-chalkboard-teacher fa-2x text-info mb-2"></i>
                    <h3 class="mb-0"><?php echo $stats['total_instructors']; ?></h3>
                    <small class="text-muted">Instructors</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-star fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
                    <small class="text-muted">Average Rating</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Courses in Category -->
                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-book-open text-primary"></i> Courses in
                        <?php echo htmlspecialchars($category['name']); ?> (<?php echo count($courses); ?>)
                    </h4>

                    <?php if (count($courses) > 0): ?>
                        <?php foreach ($courses as $course): ?>
                            <div class="course-card">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5><a
                                                href="view_course.php?id=<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['title']); ?></a>
                                        </h5>
                                        <p class="text-muted small mb-2">
                                            <?php echo htmlspecialchars(substr($course['description'], 0, 120)); ?>...
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> <a
                                                href="view_user.php?id=<?php echo $course['instructor_id']; ?>"><?php echo htmlspecialchars($course['instructor_name']); ?></a>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> Created:
                                            <?php echo date('M d, Y', strtotime($course['created_at'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h4 class="text-success mb-2">$<?php echo number_format($course['price'], 2); ?></h4>
                                        <?php if ($course['avg_rating']): ?>
                                            <div class="rating-stars">
                                                <?php echo str_repeat('★', round($course['avg_rating'])); ?>
                                                <small
                                                    class="text-muted">(<?php echo number_format($course['avg_rating'], 1); ?>)</small>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted">No ratings</small>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <span class="badge bg-primary"><?php echo $course['enrolled_count']; ?>
                                                students</span>
                                            <span class="badge bg-info"><?php echo $course['review_count']; ?> reviews</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-5">No courses in this category yet.</p>
                    <?php endif; ?>

                    <?php
                    $display_courses_sql = str_replace(':id', $category_id, $courses_sql);
                    displaySQL($display_courses_sql, "SQL - Courses in Category", "category_courses");
                    ?>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Category Info -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Category Details</h5>
                    <p><strong>Category ID:</strong> #<?php echo $category['category_id']; ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($category['name']); ?></p>
                    <p><strong>Description:</strong>
                        <?php echo htmlspecialchars($category['description'] ?? 'No description available'); ?></p>
                    <p><strong>Total Courses:</strong> <?php echo $stats['total_courses']; ?></p>
                    <p><strong>Total Enrollments:</strong> <?php echo $stats['total_enrollments']; ?></p>
                    <p><strong>Average Price:</strong>
                        $<?php echo number_format($stats['avg_price'] ?? 0, 2); ?></p>
                    <p><strong>Total Value:</strong> $<?php echo number_format($stats['total_value'] ?? 0, 2); ?></p>
                    <?php if (isset($category['created_at']) && $category['created_at']): ?>
                        <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($category['created_at'])); ?></p>
                    <?php endif; ?>

                    <?php
                    $display_category_sql = str_replace(':id', $category_id, $category_sql);
                    displaySQL($display_category_sql, "SQL - Category Details", "category_details");
                    ?>
                    <?php
                    $display_stats_sql = str_replace(':id', $category_id, $stats_sql);
                    displaySQL($display_stats_sql, "SQL - Category Statistics", "category_stats");
                    ?>
                </div>

                <!-- Top Instructors -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-trophy text-warning"></i> Top Instructors</h5>
                    <?php if (count($instructors) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($instructors as $instructor): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <a
                                                    href="view_user.php?id=<?php echo $instructor['user_id']; ?>"><?php echo htmlspecialchars($instructor['name']); ?></a>
                                            </h6>
                                            <small
                                                class="text-muted"><?php echo htmlspecialchars($instructor['email']); ?></small>
                                            <br>
                                            <small>
                                                <span class="badge bg-primary"><?php echo $instructor['course_count']; ?>
                                                    courses</span>
                                                <span class="badge bg-success"><?php echo $instructor['total_students']; ?>
                                                    students</span>
                                            </small>
                                        </div>
                                        <div>
                                            <?php if ($instructor['avg_rating']): ?>
                                                <span class="rating-stars">★</span>
                                                <small><?php echo number_format($instructor['avg_rating'], 1); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No instructors yet.</p>
                    <?php endif; ?>
                    <?php
                    $display_instructors_sql = str_replace(':id', $category_id, $instructors_sql);
                    displaySQL($display_instructors_sql, "SQL - Top Instructors", "category_instructors");
                    ?>
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