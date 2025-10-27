<?php
require_once 'config/database.php';

$enrollment_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: No enrollment ID provided');
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $query = "UPDATE enrollments SET progress = :progress WHERE enrollment_id = :enrollment_id";
        $stmt = $pdo->prepare($query);
        $progress = !empty($_POST['progress']) ? $_POST['progress'] : 0;
        
        // Validate progress
        if ($progress < 0 || $progress > 100) {
            throw new Exception("Progress must be between 0 and 100");
        }
        
        $stmt->bindParam(':progress', $progress);
        $stmt->bindParam(':enrollment_id', $enrollment_id);
        
        if ($stmt->execute()) {
            $success = "Enrollment updated successfully!";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Get enrollment data
$query = "SELECT e.*, u.name as student_name, u.email, c.title as course_title, c.price 
          FROM enrollments e 
          INNER JOIN users u ON e.student_id = u.user_id 
          INNER JOIN courses c ON e.course_id = c.course_id 
          WHERE e.enrollment_id = :enrollment_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':enrollment_id', $enrollment_id);
$stmt->execute();
$enrollment = $stmt->fetch();

if (!$enrollment) {
    die('Enrollment not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Enrollment - LMS</title>
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
        .info-badge {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #6366f1;
            margin-bottom: 20px;
        }
        .progress {
            height: 30px;
            border-radius: 10px;
        }
        .progress-bar {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            font-weight: bold;
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
                <a href="enrollments.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Enrollments
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="page-header">
            <h1><i class="fas fa-user-edit"></i> Edit Enrollment #<?php echo $enrollment_id; ?></h1>
            <p class="text-muted mb-0">Update course progress for this enrollment</p>
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

        <!-- Enrollment Information -->
        <div class="info-badge">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2"><i class="fas fa-user"></i> Student</h6>
                    <h5><?php echo htmlspecialchars($enrollment['student_name']); ?></h5>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($enrollment['email']); ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-2"><i class="fas fa-book"></i> Course</h6>
                    <h5><?php echo htmlspecialchars($enrollment['course_title']); ?></h5>
                    <p class="text-muted mb-0">
                        <span class="badge bg-success">$<?php echo number_format($enrollment['price'], 2); ?></span>
                        <span class="ms-2"><i class="fas fa-calendar"></i> Enrolled: <?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="card form-card">
            <div class="card-header text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Update Progress</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-4">
                        <label for="progress" class="form-label">
                            <i class="fas fa-percentage"></i> Course Progress (0-100%)
                        </label>
                        <input type="number" 
                               name="progress" 
                               id="progress"
                               class="form-control form-control-lg" 
                               min="0" 
                               max="100" 
                               value="<?php echo htmlspecialchars($enrollment['progress']); ?>" 
                               required>
                        <small class="text-muted">Current progress: <?php echo $enrollment['progress']; ?>%</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Progress Visualization</label>
                        <div class="progress">
                            <div class="progress-bar" 
                                 id="progressBar"
                                 role="progressbar" 
                                 style="width: <?php echo $enrollment['progress']; ?>%"
                                 aria-valuenow="<?php echo $enrollment['progress']; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo $enrollment['progress']; ?>%
                            </div>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="enrollments.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update Enrollment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update progress bar in real-time as user types
        document.getElementById('progress').addEventListener('input', function() {
            const value = Math.min(100, Math.max(0, this.value));
            const progressBar = document.getElementById('progressBar');
            progressBar.style.width = value + '%';
            progressBar.setAttribute('aria-valuenow', value);
            progressBar.textContent = value + '%';
            
            // Change color based on progress
            progressBar.classList.remove('bg-danger', 'bg-warning', 'bg-info', 'bg-success');
            if (value < 25) {
                progressBar.classList.add('bg-danger');
            } else if (value < 50) {
                progressBar.classList.add('bg-warning');
            } else if (value < 75) {
                progressBar.classList.add('bg-info');
            } else {
                progressBar.classList.add('bg-success');
            }
        });
    </script>
</body>
</html>

