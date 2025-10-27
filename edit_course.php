<?php
require_once 'config/database.php';

$course_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: No course ID provided');
$success = $error = '';

// Get categories for dropdown
$categories = $pdo->query("SELECT category_id, name FROM course_categories ORDER BY name")->fetchAll();

// Get instructors for dropdown
$instructors = $pdo->query("SELECT user_id, name FROM users WHERE role = 'instructor' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $query = "UPDATE courses SET 
                  title = :title, 
                  description = :description, 
                  category_id = :category_id, 
                  instructor_id = :instructor_id, 
                  price = :price 
                  WHERE course_id = :course_id";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':title', $_POST['title']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':category_id', $_POST['category_id']);
        
        $instructor_id = !empty($_POST['instructor_id']) ? $_POST['instructor_id'] : null;
        $stmt->bindParam(':instructor_id', $instructor_id);
        
        $stmt->bindParam(':price', $_POST['price']);
        $stmt->bindParam(':course_id', $course_id);
        
        if ($stmt->execute()) {
            $success = "Course updated successfully!";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get course data
$query = "SELECT c.*, cat.name as category_name, u.name as instructor_name 
          FROM courses c 
          LEFT JOIN course_categories cat ON c.category_id = cat.category_id 
          LEFT JOIN users u ON c.instructor_id = u.user_id 
          WHERE c.course_id = :course_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':course_id', $course_id);
$stmt->execute();
$course = $stmt->fetch();

if (!$course) {
    die('Course not found.');
}

// Get course statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM enrollments WHERE course_id = :course_id) as enrollments_count,
    (SELECT COUNT(*) FROM assignments WHERE course_id = :course_id) as assignments_count,
    (SELECT AVG(rating) FROM reviews WHERE course_id = :course_id) as avg_rating
";
$stmt = $pdo->prepare($stats_query);
$stmt->bindParam(':course_id', $course_id);
$stmt->execute();
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-bottom: 50px;
        }
        .navbar {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .page-header h1 {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .form-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 15px 15px 0 0 !important;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #6366f1;
        }
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
            <h1><i class="fas fa-book-open"></i> Edit Course #<?php echo $course_id; ?></h1>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($course['title']); ?></p>
        </div>

        <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Course Statistics -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h6 class="text-muted mb-2"><i class="fas fa-users"></i> Enrollments</h6>
                    <h3 class="mb-0"><?php echo $stats['enrollments_count']; ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h6 class="text-muted mb-2"><i class="fas fa-tasks"></i> Assignments</h6>
                    <h3 class="mb-0"><?php echo $stats['assignments_count']; ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h6 class="text-muted mb-2"><i class="fas fa-star"></i> Avg Rating</h6>
                    <h3 class="mb-0"><?php echo $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : 'N/A'; ?></h3>
                </div>
            </div>
        </div>

        <div class="card form-card">
            <div class="card-header text-white">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Course Information</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading"></i> Course Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   id="title"
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($course['title']); ?>" 
                                   required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left"></i> Description
                            </label>
                            <textarea name="description" 
                                      id="description"
                                      class="form-control" 
                                      rows="4"><?php echo htmlspecialchars($course['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">
                                <i class="fas fa-folder"></i> Category <span class="text-danger">*</span>
                            </label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" 
                                            <?php echo ($course['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="instructor_id" class="form-label">
                                <i class="fas fa-chalkboard-teacher"></i> Instructor
                            </label>
                            <select name="instructor_id" id="instructor_id" class="form-select">
                                <option value="">Not Assigned</option>
                                <?php foreach($instructors as $instructor): ?>
                                    <option value="<?php echo $instructor['user_id']; ?>" 
                                            <?php echo ($course['instructor_id'] == $instructor['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($instructor['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="price" class="form-label">
                                <i class="fas fa-dollar-sign"></i> Price <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="price" 
                                   id="price"
                                   class="form-control" 
                                   step="0.01" 
                                   min="0" 
                                   value="<?php echo $course['price']; ?>" 
                                   required>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Created:</strong> <?php echo date('F d, Y g:i A', strtotime($course['created_at'])); ?>
                    </div>

                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="courses.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

