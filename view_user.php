<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();

$user_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get user details
$user_sql = "SELECT * FROM users WHERE user_id = :id";
$stmt = $conn->prepare($user_sql);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: users.php');
    exit;
}

// Get enrollments if student
$enrollments = [];
$enrollments_sql = "";
if ($user['role'] == 'student') {
    $enrollments_sql = "SELECT 
                e.enrollment_id,
                e.enrolled_at,
                e.progress,
                c.title as course_title,
                c.price,
                c.course_id,
                u.name as instructor_name
            FROM enrollments e
            INNER JOIN courses c ON e.course_id = c.course_id
            INNER JOIN users u ON c.instructor_id = u.user_id
            WHERE e.student_id = :id
            ORDER BY e.enrolled_at DESC";
    $stmt = $conn->prepare($enrollments_sql);
    $stmt->execute([':id' => $user_id]);
    $enrollments = $stmt->fetchAll();
}

// Get courses if instructor
$courses = [];
$courses_sql = "";
if ($user['role'] == 'instructor') {
    $courses_sql = "SELECT 
                c.course_id,
                c.title,
                c.price,
                COUNT(DISTINCT e.enrollment_id) as enrolled_count,
                AVG(r.rating) as avg_rating
            FROM courses c
            LEFT JOIN enrollments e ON c.course_id = e.course_id
            LEFT JOIN reviews r ON c.course_id = r.course_id
            WHERE c.instructor_id = :id
            GROUP BY c.course_id";
    $stmt = $conn->prepare($courses_sql);
    $stmt->execute([':id' => $user_id]);
    $courses = $stmt->fetchAll();
}

// Get submissions if student
$submissions = [];
$submissions_sql = "";
if ($user['role'] == 'student') {
    $submissions_sql = "SELECT 
                s.submission_id,
                s.submitted_at,
                s.grade,
                a.title as assignment_title,
                c.title as course_title
            FROM submissions s
            INNER JOIN assignments a ON s.assignment_id = a.assignment_id
            INNER JOIN courses c ON a.course_id = c.course_id
            WHERE s.student_id = :id
            ORDER BY s.submitted_at DESC
            LIMIT 5";
    $stmt = $conn->prepare($submissions_sql);
    $stmt->execute([':id' => $user_id]);
    $submissions = $stmt->fetchAll();
}

// Get reviews written by user
$reviews_sql = "SELECT 
            r.review_id,
            r.rating,
            r.comment,
            r.created_at,
            c.title as course_title,
            c.course_id
        FROM reviews r
        INNER JOIN courses c ON r.course_id = c.course_id
        WHERE r.student_id = :id
        ORDER BY r.created_at DESC";
$stmt = $conn->prepare($reviews_sql);
$stmt->execute([':id' => $user_id]);
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - <?php echo htmlspecialchars($user['name']); ?></title>
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

        .profile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .avatar {
            width: 120px;
            height: 120px;
            background: white;
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 1rem;
            border: 5px solid rgba(255, 255, 255, 0.3);
        }

        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .rating-stars {
            color: #fbbf24;
        }

        .progress {
            height: 25px;
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
                    <li class="nav-item"><a class="nav-link active" href="users.php"><i class="fas fa-users"></i>
                            Users</a></li>
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
            <a href="users.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left"></i> Back to Users</a>
        </div>

        <!-- Profile Card -->
        <div class="profile-card mb-4">
            <div class="profile-header">
                <div class="avatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="mb-2"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                <?php
                $badge_class = 'secondary';
                $icon = 'user';
                if ($user['role'] == 'student') {
                    $badge_class = 'light';
                    $icon = 'user-graduate';
                } elseif ($user['role'] == 'instructor') {
                    $badge_class = 'light';
                    $icon = 'chalkboard-teacher';
                } elseif ($user['role'] == 'admin') {
                    $badge_class = 'light';
                    $icon = 'user-shield';
                }
                ?>
                <span class="badge bg-<?php echo $badge_class; ?> fs-6">
                    <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo ucfirst($user['role']); ?>
                </span>
            </div>
            <div class="p-4">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>User ID:</strong> #<?php echo $user['user_id']; ?></p>
                        <p><strong>Joined:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                        <a href="delete_user.php?id=<?php echo $user['user_id']; ?>"
                            onclick="return confirm('Delete this user?')" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($user['role'] == 'student' && count($enrollments) > 0): ?>
            <!-- Student Enrollments -->
            <div class="info-card">
                <h4 class="mb-3"><i class="fas fa-clipboard-list text-primary"></i> Enrolled Courses</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Enrolled Date</th>
                                <th>Progress</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <tr>
                                    <td><a
                                            href="view_course.php?id=<?php echo $enrollment['course_id']; ?>"><?php echo htmlspecialchars($enrollment['course_title']); ?></a>
                                    </td>
                                    <td><?php echo htmlspecialchars($enrollment['instructor_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary"
                                                style="width: <?php echo $enrollment['progress']; ?>%">
                                                <?php echo $enrollment['progress']; ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($enrollment['price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
                $display_enrollments_sql = str_replace(':id', $user_id, $enrollments_sql);
                displaySQL($display_enrollments_sql, "SQL - Student Enrollments", "user_enrollments");
                ?>
            </div>
        <?php endif; ?>

        <?php if ($user['role'] == 'instructor' && count($courses) > 0): ?>
            <!-- Instructor Courses -->
            <div class="info-card">
                <h4 class="mb-3"><i class="fas fa-book-open text-success"></i> Teaching Courses</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Course Title</th>
                                <th>Students Enrolled</th>
                                <th>Average Rating</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><a
                                            href="view_course.php?id=<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['title']); ?></a>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo $course['enrolled_count']; ?> students</span>
                                    </td>
                                    <td>
                                        <?php if ($course['avg_rating']): ?>
                                            <span class="rating-stars">
                                                <?php echo str_repeat('★', round($course['avg_rating'])); ?>
                                            </span>
                                            <small>(<?php echo number_format($course['avg_rating'], 1); ?>)</small>
                                        <?php else: ?>
                                            <span class="text-muted">No ratings</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?php echo number_format($course['price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
                $display_courses_sql = str_replace(':id', $user_id, $courses_sql);
                displaySQL($display_courses_sql, "SQL - Instructor Courses", "user_courses");
                ?>
            </div>
        <?php endif; ?>

        <?php if ($user['role'] == 'student' && count($submissions) > 0): ?>
            <!-- Recent Submissions -->
            <div class="info-card">
                <h4 class="mb-3"><i class="fas fa-file-alt text-info"></i> Recent Submissions</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Submitted</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($submission['assignment_title']); ?></td>
                                    <td><?php echo htmlspecialchars($submission['course_title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($submission['submitted_at'])); ?></td>
                                    <td>
                                        <?php if ($submission['grade']): ?>
                                            <strong
                                                class="text-success"><?php echo number_format($submission['grade'], 1); ?>%</strong>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
                $display_submissions_sql = str_replace(':id', $user_id, $submissions_sql);
                displaySQL($display_submissions_sql, "SQL - Student Submissions", "user_submissions");
                ?>
            </div>
        <?php endif; ?>

        <?php if (count($reviews) > 0): ?>
            <!-- User Reviews -->
            <div class="info-card">
                <h4 class="mb-3"><i class="fas fa-star text-warning"></i> Reviews</h4>
                <?php foreach ($reviews as $review): ?>
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6><a
                                        href="view_course.php?id=<?php echo $review['course_id']; ?>"><?php echo htmlspecialchars($review['course_title']); ?></a>
                                </h6>
                                <span class="rating-stars">
                                    <?php echo str_repeat('★', $review['rating']); ?>
                                </span>
                            </div>
                            <p class="mb-1"><?php echo htmlspecialchars($review['comment']); ?></p>
                            <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php
                $display_reviews_sql = str_replace(':id', $user_id, $reviews_sql);
                displaySQL($display_reviews_sql, "SQL - User Reviews", "user_reviews");
                ?>
            </div>
        <?php endif; ?>

        <!-- User Details SQL -->
        <div class="info-card">
            <h5><i class="fas fa-code text-primary"></i> SQL Queries Used</h5>
            <?php
            $display_user_sql = str_replace(':id', $user_id, $user_sql);
            displaySQL($display_user_sql, "User Details Query", "user_details");
            ?>
        </div>
    </div>

    <footer class="text-center text-muted py-4 mt-5">
        <p>&copy; 2024 Learning Management System</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>