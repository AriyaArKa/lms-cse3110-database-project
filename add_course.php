<?php
require_once 'config/database.php';

$success_message = '';
$error_message = '';

// Get instructors and categories for dropdowns
try {
    $instructors_stmt = $pdo->query("SELECT user_id, name FROM users WHERE role = 'instructor' ORDER BY name");
    $instructors = $instructors_stmt->fetchAll();
    
    $categories_stmt = $pdo->query("SELECT category_id, name FROM course_categories ORDER BY name");
    $categories = $categories_stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error loading form data: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $instructor_id = $_POST['instructor_id'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        
        // Validate inputs
        if (empty($title) || empty($description) || empty($instructor_id) || empty($category_id) || empty($price)) {
            throw new Exception("All fields are required");
        }
        
        if (!is_numeric($price) || $price < 0 || $price > 9999.99) {
            throw new Exception("Price must be between 0 and 9999.99");
        }
        
        // Insert new course
        $stmt = $pdo->prepare("
            INSERT INTO courses (title, description, instructor_id, category_id, price, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $description, $instructor_id, $category_id, $price]);
        
        $success_message = "Course added successfully!";
        
        // Redirect after success
        header("refresh:2;url=courses.php");
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
    <title>Add Course - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .form-container {
            max-width: 700px;
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
                        <i class="fas fa-book-medical me-2"></i>Add New Course
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
                            <label for="title" class="form-label required">Course Title</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   placeholder="Enter course title" required 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label required">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4" placeholder="Enter course description" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="instructor_id" class="form-label required">Instructor</label>
                                <select class="form-select" id="instructor_id" name="instructor_id" required>
                                    <option value="">Select instructor...</option>
                                    <?php foreach ($instructors as $instructor): ?>
                                        <option value="<?php echo $instructor['user_id']; ?>"
                                            <?php echo (isset($_POST['instructor_id']) && $_POST['instructor_id'] == $instructor['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($instructor['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label required">Category</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select category...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>"
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="price" class="form-label required">Price (USD)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       placeholder="99.99" step="0.01" min="0" max="9999.99" required
                                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Add Course
                            </button>
                            <a href="courses.php" class="btn btn-outline-secondary">
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); }
        .page-header { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; }
        .form-card { border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap"></i> Learning Management System
            </a>
            <div class="ms-auto">
                <a href="courses.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="page-header">
            <h1><i class="fas fa-book-medical"></i> Add New Course</h1>
            <p class="mb-0">Enter course information to create a new course offering</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card form-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Course Information Form</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="add_course.php">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Course Code <span class="text-danger">*</span></label>
                            <input type="text" name="course_code" class="form-control" required placeholder="CS101" value="<?php echo htmlspecialchars($_POST['course_code'] ?? ''); ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Course Name <span class="text-danger">*</span></label>
                            <input type="text" name="course_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['course_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Credits <span class="text-danger">*</span></label>
                            <input type="number" name="credits" class="form-control" required min="1" max="6" value="<?php echo htmlspecialchars($_POST['credits'] ?? '3'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="dept_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php foreach($departments as $dept): ?>
                                <option value="<?php echo $dept['dept_id']; ?>" <?php echo (($_POST['dept_id'] ?? '') == $dept['dept_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['dept_code'] . ' - ' . $dept['dept_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Instructor</label>
                            <select name="instructor_id" class="form-select">
                                <option value="">To Be Assigned</option>
                                <?php foreach($instructors as $instructor): ?>
                                <option value="<?php echo $instructor['instructor_id']; ?>" <?php echo (($_POST['instructor_id'] ?? '') == $instructor['instructor_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($instructor['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <select name="semester" class="form-select" required>
                                <option value="Fall" <?php echo (($_POST['semester'] ?? '') === 'Fall') ? 'selected' : ''; ?>>Fall</option>
                                <option value="Spring" <?php echo (($_POST['semester'] ?? '') === 'Spring') ? 'selected' : ''; ?>>Spring</option>
                                <option value="Summer" <?php echo (($_POST['semester'] ?? '') === 'Summer') ? 'selected' : ''; ?>>Summer</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control" required min="2020" max="2030" value="<?php echo htmlspecialchars($_POST['year'] ?? date('Y')); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Students</label>
                            <input type="number" name="max_students" class="form-control" min="1" value="<?php echo htmlspecialchars($_POST['max_students'] ?? '30'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="room_number" class="form-control" placeholder="EA-101" value="<?php echo htmlspecialchars($_POST['room_number'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Schedule</label>
                            <input type="text" name="schedule" class="form-control" placeholder="MWF 9:00-10:00" value="<?php echo htmlspecialchars($_POST['schedule'] ?? ''); ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo (($_POST['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="cancelled" <?php echo (($_POST['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <hr>
                            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> Add Course</button>
                            <a href="courses.php" class="btn btn-secondary btn-lg"><i class="fas fa-times"></i> Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted py-4 mt-5">
        <p>&copy; 2024 University Database Management System</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
