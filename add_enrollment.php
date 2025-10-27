<?php
require_once 'config/database.php';

$success_message = '';
$error_message = '';

// Get students and courses for dropdowns
try {
    $students_stmt = $pdo->query("SELECT user_id, name, email FROM users WHERE role = 'student' ORDER BY name");
    $students = $students_stmt->fetchAll();
    
    $courses_stmt = $pdo->query("
        SELECT c.course_id, c.title, c.price, u.name as instructor_name 
        FROM courses c
        INNER JOIN users u ON c.instructor_id = u.user_id
        ORDER BY c.title
    ");
    $courses = $courses_stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error loading form data: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];
        $progress = isset($_POST['progress']) ? $_POST['progress'] : 0;
        
        // Validate inputs
        if (empty($student_id) || empty($course_id)) {
            throw new Exception("Student and course are required");
        }
        
        if (!is_numeric($progress) || $progress < 0 || $progress > 100) {
            throw new Exception("Progress must be between 0 and 100");
        }
        
        // Check if enrollment already exists
        $check_stmt = $pdo->prepare("
            SELECT enrollment_id FROM enrollments 
            WHERE student_id = ? AND course_id = ?
        ");
        $check_stmt->execute([$student_id, $course_id]);
        if ($check_stmt->fetch()) {
            throw new Exception("Student is already enrolled in this course");
        }
        
        // Insert new enrollment
        $stmt = $pdo->prepare("
            INSERT INTO enrollments (student_id, course_id, progress, enrolled_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$student_id, $course_id, $progress]);
        
        $success_message = "Enrollment added successfully!";
        
        // Redirect after success
        header("refresh:2;url=enrollments.php");
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Enrollment - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5558e3 0%, #7c4de8 100%);
        }
        .form-label {
            font-weight: 600;
            color: #4b5563;
        }
        .required::after {
            content: " *";
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Enroll Student
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="student_id" class="form-label required">Student</label>
                            <select class="form-select" id="student_id" name="student_id" required>
                                <option value="">Select student...</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['user_id']; ?>"
                                        <?php echo (isset($_POST['student_id']) && $_POST['student_id'] == $student['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="course_id" class="form-label required">Course</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Select course...</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['course_id']; ?>"
                                        <?php echo (isset($_POST['course_id']) && $_POST['course_id'] == $course['course_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['title']); ?> - $<?php echo number_format($course['price'], 2); ?>
                                        (<?php echo htmlspecialchars($course['instructor_name']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="progress" class="form-label">Initial Progress (%)</label>
                            <input type="number" class="form-control" id="progress" name="progress" 
                                   placeholder="0" step="1" min="0" max="100" value="0">
                            <small class="text-muted">Leave at 0 for new enrollments</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Enroll Student
                            </button>
                            <a href="enrollments.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
        </div>
    </div>
    <footer class="text-center text-muted py-4 mt-5"><p>&copy; 2024 University Database Management System</p></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
