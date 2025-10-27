<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();

// Handle filters
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$rating_filter = isset($_GET['rating']) ? $_GET['rating'] : '';

// Build query
$query = "SELECT 
            r.review_id,
            r.rating,
            r.comment,
            r.created_at,
            c.title as course_title,
            c.course_id,
            u.name as student_name,
            u.user_id as student_id,
            inst.name as instructor_name
          FROM reviews r
          INNER JOIN courses c ON r.course_id = c.course_id
          INNER JOIN users u ON r.student_id = u.user_id
          INNER JOIN users inst ON c.instructor_id = inst.user_id
          WHERE 1=1";

$params = [];

if (!empty($course_filter)) {
    $query .= " AND r.course_id = :course";
    $params[':course'] = $course_filter;
}

if (!empty($rating_filter)) {
    $query .= " AND r.rating = :rating";
    $params[':rating'] = $rating_filter;
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Get courses for filter
$courses_stmt = $conn->query("SELECT course_id, title FROM courses ORDER BY title");
$courses = $courses_stmt->fetchAll();

// Get stats
$stats_stmt = $conn->query("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
    FROM reviews
");
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Reviews - LMS</title>
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

        .review-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .review-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .rating-stars {
            color: #fbbf24;
            font-size: 1.2rem;
        }

        .filter-card,
        .stats-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .rating-bar {
            background: #e5e7eb;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }

        .rating-bar-fill {
            background: #fbbf24;
            height: 100%;
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
                    <h1 class="mb-0"><i class="fas fa-star text-warning"></i> Course Reviews</h1>
                    <p class="text-muted mb-0">Student feedback and course ratings</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Stats Sidebar -->
            <div class="col-lg-3">
                <div class="stats-card">
                    <h5 class="mb-3">Rating Overview</h5>
                    <div class="text-center mb-4">
                        <div class="rating-stars mb-2">
                            ★★★★★
                        </div>
                        <h2 class="mb-0"><?php echo number_format($stats['avg_rating'], 1); ?></h2>
                        <p class="text-muted">out of 5</p>
                        <p class="text-muted mb-0"><?php echo $stats['total_reviews']; ?> total reviews</p>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small>5 ★</small>
                            <small class="text-muted"><?php echo $stats['five_star']; ?></small>
                        </div>
                        <div class="rating-bar">
                            <div class="rating-bar-fill"
                                style="width: <?php echo $stats['total_reviews'] > 0 ? ($stats['five_star'] / $stats['total_reviews'] * 100) : 0; ?>%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small>4 ★</small>
                            <small class="text-muted"><?php echo $stats['four_star']; ?></small>
                        </div>
                        <div class="rating-bar">
                            <div class="rating-bar-fill"
                                style="width: <?php echo $stats['total_reviews'] > 0 ? ($stats['four_star'] / $stats['total_reviews'] * 100) : 0; ?>%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small>3 ★</small>
                            <small class="text-muted"><?php echo $stats['three_star']; ?></small>
                        </div>
                        <div class="rating-bar">
                            <div class="rating-bar-fill"
                                style="width: <?php echo $stats['total_reviews'] > 0 ? ($stats['three_star'] / $stats['total_reviews'] * 100) : 0; ?>%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small>2 ★</small>
                            <small class="text-muted"><?php echo $stats['two_star']; ?></small>
                        </div>
                        <div class="rating-bar">
                            <div class="rating-bar-fill"
                                style="width: <?php echo $stats['total_reviews'] > 0 ? ($stats['two_star'] / $stats['total_reviews'] * 100) : 0; ?>%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small>1 ★</small>
                            <small class="text-muted"><?php echo $stats['one_star']; ?></small>
                        </div>
                        <div class="rating-bar">
                            <div class="rating-bar-fill"
                                style="width: <?php echo $stats['total_reviews'] > 0 ? ($stats['one_star'] / $stats['total_reviews'] * 100) : 0; ?>%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="col-lg-9">
                <!-- Filters -->
                <div class="filter-card">
                    <form method="GET" action="reviews.php" class="row g-3">
                        <div class="col-md-7">
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
                            <label class="form-label">Filter by Rating</label>
                            <select name="rating" class="form-select">
                                <option value="">All Ratings</option>
                                <option value="5" <?php echo $rating_filter == '5' ? 'selected' : ''; ?>>5 Stars</option>
                                <option value="4" <?php echo $rating_filter == '4' ? 'selected' : ''; ?>>4 Stars</option>
                                <option value="3" <?php echo $rating_filter == '3' ? 'selected' : ''; ?>>3 Stars</option>
                                <option value="2" <?php echo $rating_filter == '2' ? 'selected' : ''; ?>>2 Stars</option>
                                <option value="1" <?php echo $rating_filter == '1' ? 'selected' : ''; ?>>1 Star</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Reviews -->
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($review['course_title']); ?></h6>
                                    <small class="text-muted">by
                                        <?php echo htmlspecialchars($review['instructor_name']); ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="rating-stars">
                                        <?php echo str_repeat('★', $review['rating']);
                                        echo str_repeat('☆', 5 - $review['rating']); ?>
                                    </div>
                                    <small
                                        class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                            </div>

                            <div class="mb-2">
                                <strong><?php echo htmlspecialchars($review['student_name']); ?></strong>
                            </div>

                            <p class="mb-3"><?php echo htmlspecialchars($review['comment']); ?></p>

                            <div class="d-flex gap-2">
                                <a href="delete_review.php?id=<?php echo $review['review_id']; ?>"
                                    class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this review?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-star fa-4x text-muted mb-3"></i>
                        <h3 class="text-muted">No Reviews Found</h3>
                        <p class="text-muted">No reviews match your filter criteria.</p>
                        <a href="reviews.php" class="btn btn-primary">Clear Filters</a>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4 mb-4">
                    <p class="text-muted">Showing <?php echo count($reviews); ?> review(s)</p>
                </div>

                <!-- SQL Query Display -->
                <div class="card mb-4">
                    <div class="card-header"
                        style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white;">
                        <h5 class="mb-0"><i class="fas fa-code"></i> SQL Queries Used</h5>
                    </div>
                    <div class="card-body">
                        <h6>Main Reviews Query:</h6>
                        <?php
                        displaySQL($query, "Reviews Query with Multiple JOINs", "reviews_main_query");
                        ?>
                        <hr>
                        <h6>Statistics Query (with CASE statements):</h6>
                        <?php
                        $stats_sql = "SELECT 
    COUNT(*) as total_reviews,
    AVG(rating) as avg_rating,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
FROM reviews";
                        displaySQL($stats_sql, "Rating Distribution with CASE", "reviews_stats_query");
                        ?>
                    </div>
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