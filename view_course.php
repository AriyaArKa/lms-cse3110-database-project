<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();

$course_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get course details
$course_sql = "SELECT 
            c.*,
            cc.name as category_name,
            u.name as instructor_name,
            u.user_id as instructor_id,
            u.email as instructor_email
        FROM courses c
        INNER JOIN course_categories cc ON c.category_id = cc.category_id
        INNER JOIN users u ON c.instructor_id = u.user_id
        WHERE c.course_id = :id";
$stmt = $conn->prepare($course_sql);
$stmt->execute([':id' => $course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: courses.php');
    exit;
}

// Get enrollment statistics
$enrollment_stats_sql = "SELECT 
            COUNT(*) as total_enrollments,
            AVG(progress) as avg_progress
        FROM enrollments
        WHERE course_id = :id";
$stmt = $conn->prepare($enrollment_stats_sql);
$stmt->execute([':id' => $course_id]);
$enrollment_stats = $stmt->fetch();

// Get enrolled students
$students_sql = "SELECT 
            e.enrollment_id,
            e.enrolled_at,
            e.progress,
            u.user_id,
            u.name,
            u.email
        FROM enrollments e
        INNER JOIN users u ON e.student_id = u.user_id
        WHERE e.course_id = :id
        ORDER BY e.enrolled_at DESC
        LIMIT 10";
$stmt = $conn->prepare($students_sql);
$stmt->execute([':id' => $course_id]);
$students = $stmt->fetchAll();

// Get course reviews
$reviews_sql = "SELECT 
            r.review_id,
            r.rating,
            r.comment,
            r.created_at,
            u.name as student_name,
            u.user_id as student_id
        FROM reviews r
        INNER JOIN users u ON r.student_id = u.user_id
        WHERE r.course_id = :id
        ORDER BY r.created_at DESC";
$stmt = $conn->prepare($reviews_sql);
$stmt->execute([':id' => $course_id]);
$reviews = $stmt->fetchAll();

// Calculate rating stats
$rating_stats_sql = "SELECT 
            AVG(rating) as avg_rating,
            COUNT(*) as total_reviews,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_stars,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_stars,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_stars,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_stars,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM reviews
        WHERE course_id = :id";
$stmt = $conn->prepare($rating_stats_sql);
$stmt->execute([':id' => $course_id]);
$rating_stats = $stmt->fetch();

// Get assignments
$assignments_sql = "SELECT 
            assignment_id,
            title,
            description,
            due_date,
            (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.assignment_id) as submission_count
        FROM assignments a
        WHERE course_id = :id
        ORDER BY due_date ASC";
$stmt = $conn->prepare($assignments_sql);
$stmt->execute([':id' => $course_id]);
$assignments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Course - <?php echo htmlspecialchars($course['title']); ?></title>
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

        .course-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
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

        .rating-stars {
            color: #fbbf24;
        }

        .rating-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .rating-bar-fill {
            height: 100%;
            background: #fbbf24;
        }

        .progress {
            height: 8px;
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
                    <li class="nav-item"><a class="nav-link active" href="courses.php"><i class="fas fa-book-open"></i>
                            Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="sql_operations.php"><i class="fas fa-database"></i>
                            SQL Demo</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="mb-3">
            <a href="courses.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left"></i> Back to Courses</a>
        </div>

        <!-- Course Header -->
        <div class="course-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span
                        class="badge bg-light text-dark mb-2"><?php echo htmlspecialchars($course['category_name']); ?></span>
                    <h1 class="mb-3"><?php echo htmlspecialchars($course['title']); ?></h1>
                    <p class="lead mb-3"><?php echo htmlspecialchars($course['description']); ?></p>
                    <p class="mb-0">
                        <i class="fas fa-user"></i> Instructor: <strong><a
                                href="view_user.php?id=<?php echo $course['instructor_id']; ?>"
                                class="text-white"><?php echo htmlspecialchars($course['instructor_name']); ?></a></strong>
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="bg-white text-dark p-4 rounded">
                        <h2 class="text-success mb-0">$<?php echo number_format($course['price'], 2); ?></h2>
                        <small>Course Price</small>
                    </div>
                    <div class="mt-3">
                        <a href="edit_course.php?id=<?php echo $course['course_id']; ?>"
                            class="btn btn-light btn-lg w-100 mb-2">
                            <i class="fas fa-edit"></i> Edit Course
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0"><?php echo $enrollment_stats['total_enrollments']; ?></h3>
                    <small class="text-muted">Total Students</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-star fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0"><?php echo number_format($rating_stats['avg_rating'] ?? 0, 1); ?></h3>
                    <small class="text-muted">Average Rating</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-comment fa-2x text-info mb-2"></i>
                    <h3 class="mb-0"><?php echo $rating_stats['total_reviews']; ?></h3>
                    <small class="text-muted">Total Reviews</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                    <h3 class="mb-0"><?php echo number_format($enrollment_stats['avg_progress'] ?? 0, 1); ?>%</h3>
                    <small class="text-muted">Avg Progress</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Enrolled Students -->
                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-users text-primary"></i> Enrolled Students
                        (<?php echo $enrollment_stats['total_enrollments']; ?>)</h4>
                    <?php if (count($students) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Enrolled Date</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <a
                                                    href="view_user.php?id=<?php echo $student['user_id']; ?>"><?php echo htmlspecialchars($student['name']); ?></a><br>
                                                <small
                                                    class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($student['enrolled_at'])); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2">
                                                        <div class="progress-bar bg-primary"
                                                            style="width: <?php echo $student['progress']; ?>%"></div>
                                                    </div>
                                                    <small><?php echo $student['progress']; ?>%</small>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($enrollment_stats['total_enrollments'] > 10): ?>
                            <p class="text-muted text-center">Showing 10 of
                                <?php echo $enrollment_stats['total_enrollments']; ?> students
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No students enrolled yet.</p>
                    <?php endif; ?>
                    <?php
                    $display_students_sql = str_replace(':id', $course_id, $students_sql);
                    displaySQL($display_students_sql, "SQL - Enrolled Students", "course_students");
                    ?>
                </div>

                <!-- Assignments -->
                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-tasks text-danger"></i> Course Assignments
                        (<?php echo count($assignments); ?>)</h4>
                    <?php if (count($assignments) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($assignments as $assignment): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($assignment['title']); ?></h6>
                                            <p class="mb-1 small text-muted">
                                                <?php echo htmlspecialchars(substr($assignment['description'], 0, 100)); ?>...
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i>
                                                Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-info"><?php echo $assignment['submission_count']; ?>
                                            submissions</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No assignments created yet.</p>
                    <?php endif; ?>
                    <?php
                    $display_assignments_sql = str_replace(':id', $course_id, $assignments_sql);
                    displaySQL($display_assignments_sql, "SQL - Course Assignments", "course_assignments");
                    ?>
                </div>

                <!-- Reviews -->
                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-star text-warning"></i> Student Reviews</h4>
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6>
                                            <a
                                                href="view_user.php?id=<?php echo $review['student_id']; ?>"><?php echo htmlspecialchars($review['student_name']); ?></a>
                                        </h6>
                                        <span class="rating-stars">
                                            <?php echo str_repeat('★', $review['rating']); ?>
                                            <?php echo str_repeat('☆', 5 - $review['rating']); ?>
                                        </span>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    <small
                                        class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No reviews yet.</p>
                    <?php endif; ?>
                    <?php
                    $display_reviews_sql = str_replace(':id', $course_id, $reviews_sql);
                    displaySQL($display_reviews_sql, "SQL - Course Reviews", "course_reviews");
                    ?>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Rating Breakdown -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-chart-bar text-info"></i> Rating Breakdown</h5>
                    <?php
                    $total = $rating_stats['total_reviews'];
                    if ($total > 0):
                        for ($i = 5; $i >= 1; $i--):
                            $key = ($i == 1 ? 'one_star' : ($i == 2 ? 'two_stars' : ($i == 3 ? 'three_stars' : ($i == 4 ? 'four_stars' : 'five_stars'))));
                            $count = $rating_stats[$key];
                            $percentage = ($count / $total) * 100;
                            ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><?php echo $i; ?> <span class="rating-stars">★</span></span>
                                    <span><?php echo $count; ?> (<?php echo number_format($percentage, 1); ?>%)</span>
                                </div>
                                <div class="rating-bar">
                                    <div class="rating-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No ratings yet</p>
                    <?php endif; ?>
                    <?php
                    $display_rating_stats_sql = str_replace(':id', $course_id, $rating_stats_sql);
                    displaySQL($display_rating_stats_sql, "SQL - Rating Statistics", "rating_stats");
                    ?>
                </div>

                <!-- Course Info -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Course Details</h5>
                    <p><strong>Course ID:</strong> #<?php echo $course['course_id']; ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($course['category_name']); ?></p>
                    <p><strong>Instructor:</strong> <a
                            href="view_user.php?id=<?php echo $course['instructor_id']; ?>"><?php echo htmlspecialchars($course['instructor_name']); ?></a>
                    </p>
                    <p><strong>Price:</strong> <span
                            class="text-success fw-bold">$<?php echo number_format($course['price'], 2); ?></span></p>
                    <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($course['created_at'])); ?></p>
                    <?php
                    $display_course_sql = str_replace(':id', $course_id, $course_sql);
                    displaySQL($display_course_sql, "SQL - Course Details", "course_details");
                    ?>
                </div>

                <!-- Enrollment Stats -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-chart-pie text-success"></i> Enrollment Stats</h5>
                    <?php
                    $display_enrollment_stats_sql = str_replace(':id', $course_id, $enrollment_stats_sql);
                    displaySQL($display_enrollment_stats_sql, "SQL - Enrollment Statistics", "enrollment_stats");
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