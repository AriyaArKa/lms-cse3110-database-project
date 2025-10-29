<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();

$enrollment_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get enrollment details
$enrollment_sql = "SELECT 
            e.*,
            u.name as student_name,
            u.email as student_email,
            u.user_id as student_id,
            c.title as course_title,
            c.description as course_description,
            c.price,
            c.course_id,
            inst.name as instructor_name,
            inst.user_id as instructor_id,
            cat.name as category_name
        FROM enrollments e
        INNER JOIN users u ON e.student_id = u.user_id
        INNER JOIN courses c ON e.course_id = c.course_id
        INNER JOIN users inst ON c.instructor_id = inst.user_id
        INNER JOIN course_categories cat ON c.category_id = cat.category_id
        WHERE e.enrollment_id = :id";
$stmt = $conn->prepare($enrollment_sql);
$stmt->execute([':id' => $enrollment_id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    header('Location: enrollments.php');
    exit;
}

// Get student's assignments and submissions for this course
$assignments_sql = "SELECT 
            a.assignment_id,
            a.title,
            a.due_date,
            s.submission_id,
            s.submitted_at,
            s.grade
        FROM assignments a
        LEFT JOIN submissions s ON a.assignment_id = s.assignment_id 
            AND s.student_id = :student_id
        WHERE a.course_id = :course_id
        ORDER BY a.due_date ASC";
$stmt = $conn->prepare($assignments_sql);
$stmt->execute([
    ':student_id' => $enrollment['student_id'],
    ':course_id' => $enrollment['course_id']
]);
$assignments = $stmt->fetchAll();

// Get student's review for this course
$review_sql = "SELECT * FROM reviews 
        WHERE student_id = :student_id AND course_id = :course_id";
$stmt = $conn->prepare($review_sql);
$stmt->execute([
    ':student_id' => $enrollment['student_id'],
    ':course_id' => $enrollment['course_id']
]);
$review = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Enrollment Details</title>
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

        .enrollment-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

        .progress-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: conic-gradient(#10b981 0% var(--progress), #e5e7eb var(--progress) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .progress-circle::before {
            content: '';
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            position: absolute;
        }

        .progress-text {
            position: relative;
            z-index: 1;
            font-size: 2rem;
            font-weight: bold;
            color: #10b981;
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
                    <li class="nav-item"><a class="nav-link active" href="enrollments.php"><i
                                class="fas fa-clipboard-list"></i> Enrollments</a></li>
                    <li class="nav-item"><a class="nav-link" href="sql_operations.php"><i class="fas fa-database"></i>
                            SQL Demo</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="mb-3">
            <a href="enrollments.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left"></i> Back to
                Enrollments</a>
        </div>

        <!-- Enrollment Header -->
        <div class="enrollment-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-3">Enrollment Details</h1>
                    <h4><i class="fas fa-user"></i> Student: <a
                            href="view_user.php?id=<?php echo $enrollment['student_id']; ?>"
                            class="text-white"><u><?php echo htmlspecialchars($enrollment['student_name']); ?></u></a>
                    </h4>
                    <h4><i class="fas fa-book"></i> Course: <a
                            href="view_course.php?id=<?php echo $enrollment['course_id']; ?>"
                            class="text-white"><u><?php echo htmlspecialchars($enrollment['course_title']); ?></u></a>
                    </h4>
                    <p class="mb-0"><i class="fas fa-chalkboard-teacher"></i> Instructor: <a
                            href="view_user.php?id=<?php echo $enrollment['instructor_id']; ?>"
                            class="text-white"><u><?php echo htmlspecialchars($enrollment['instructor_name']); ?></u></a>
                    </p>
                    <p class="mb-0"><i class="fas fa-tag"></i> Category:
                        <?php echo htmlspecialchars($enrollment['category_name']); ?>
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="progress-circle" style="--progress: <?php echo $enrollment['progress']; ?>%;">
                        <div class="progress-text"><?php echo $enrollment['progress']; ?>%</div>
                    </div>
                    <p class="text-white mt-2 mb-0">Course Progress</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Assignments & Submissions -->
                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-tasks text-primary"></i> Assignments & Submissions</h4>
                    <?php if (count($assignments) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Assignment</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($assignment['title']); ?></strong></td>
                                            <td><?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></td>
                                            <td>
                                                <?php if ($assignment['submission_id']): ?>
                                                    <span class="badge bg-success">Submitted</span>
                                                    <br><small
                                                        class="text-muted"><?php echo date('M d', strtotime($assignment['submitted_at'])); ?></small>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Not Submitted</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($assignment['grade'] !== null): ?>
                                                    <strong
                                                        class="text-success"><?php echo number_format($assignment['grade'], 1); ?>%</strong>
                                                <?php elseif ($assignment['submission_id']): ?>
                                                    <span class="badge bg-info">Pending</span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No assignments for this course yet.</p>
                    <?php endif; ?>
                    <?php
                    $display_assignments_sql = str_replace([':student_id', ':course_id'], [$enrollment['student_id'], $enrollment['course_id']], $assignments_sql);
                    displaySQL($display_assignments_sql, "SQL - Course Assignments & Submissions", "enrollment_assignments");
                    ?>
                </div>

                <!-- Student Review -->
                <?php if ($review): ?>
                    <div class="info-card">
                        <h4 class="mb-3"><i class="fas fa-star text-warning"></i> Student's Course Review</h4>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <h5>Rating</h5>
                                    <span class="rating-stars fs-4">
                                        <?php echo str_repeat('★', $review['rating']); ?>
                                        <?php echo str_repeat('☆', 5 - $review['rating']); ?>
                                    </span>
                                </div>
                                <p class="mb-2"><?php echo htmlspecialchars($review['comment']); ?></p>
                                <small class="text-muted">Posted on:
                                    <?php echo date('F d, Y', strtotime($review['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php
                        $display_review_sql = str_replace([':student_id', ':course_id'], [$enrollment['student_id'], $enrollment['course_id']], $review_sql);
                        displaySQL($display_review_sql, "SQL - Student's Review", "enrollment_review");
                        ?>
                    </div>
                <?php else: ?>
                    <div class="info-card">
                        <h4 class="mb-3"><i class="fas fa-star text-warning"></i> Student's Course Review</h4>
                        <p class="text-muted text-center py-3">Student has not reviewed this course yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <!-- Enrollment Info -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Enrollment Details</h5>
                    <p><strong>Enrollment ID:</strong> #<?php echo $enrollment['enrollment_id']; ?></p>
                    <p><strong>Student:</strong> <a
                            href="view_user.php?id=<?php echo $enrollment['student_id']; ?>"><?php echo htmlspecialchars($enrollment['student_name']); ?></a>
                    </p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($enrollment['student_email']); ?></p>
                    <p><strong>Course:</strong> <a
                            href="view_course.php?id=<?php echo $enrollment['course_id']; ?>"><?php echo htmlspecialchars($enrollment['course_title']); ?></a>
                    </p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($enrollment['category_name']); ?></p>
                    <p><strong>Instructor:</strong> <a
                            href="view_user.php?id=<?php echo $enrollment['instructor_id']; ?>"><?php echo htmlspecialchars($enrollment['instructor_name']); ?></a>
                    </p>
                    <p><strong>Price Paid:</strong> <span
                            class="text-success fw-bold">$<?php echo number_format($enrollment['price'], 2); ?></span>
                    </p>
                    <p><strong>Enrolled Date:</strong>
                        <?php echo date('F d, Y', strtotime($enrollment['enrolled_at'])); ?></p>
                    <p><strong>Progress:</strong>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-success" style="width: <?php echo $enrollment['progress']; ?>%">
                            <?php echo $enrollment['progress']; ?>%
                        </div>
                    </div>
                    </p>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="edit_enrollment.php?id=<?php echo $enrollment['enrollment_id']; ?>"
                            class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Enrollment
                        </a>
                        <a href="delete_enrollment.php?id=<?php echo $enrollment['enrollment_id']; ?>"
                            onclick="return confirm('Delete this enrollment?')" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Enrollment
                        </a>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-book text-success"></i> Course Information</h5>
                    <p><?php echo htmlspecialchars($enrollment['course_description']); ?></p>
                    <a href="view_course.php?id=<?php echo $enrollment['course_id']; ?>"
                        class="btn btn-outline-primary w-100">
                        <i class="fas fa-eye"></i> View Full Course Details
                    </a>
                </div>

                <!-- SQL Query -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-code text-info"></i> Main SQL Query</h5>
                    <?php
                    $display_enrollment_sql = str_replace(':id', $enrollment_id, $enrollment_sql);
                    displaySQL($display_enrollment_sql, "Enrollment Details Query", "enrollment_details");
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