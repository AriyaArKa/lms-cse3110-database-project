<?php
require_once 'config/database.php';

$user_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: No user ID provided');
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Build update query based on whether password is being changed
        if (!empty($_POST['password'])) {
            $query = "UPDATE users SET name = :name, email = :email, password = :password, role = :role WHERE user_id = :user_id";
        } else {
            $query = "UPDATE users SET name = :name, email = :email, role = :role WHERE user_id = :user_id";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':role', $_POST['role']);
        $stmt->bindParam(':user_id', $user_id);
        
        if (!empty($_POST['password'])) {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashed_password);
        }
        
        if ($stmt->execute()) {
            $success = "User updated successfully!";
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get user data
if (!isset($user)) {
    $query = "SELECT * FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (!$user) {
        die('User not found.');
    }
}

// Get user statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM enrollments WHERE student_id = :user_id) as enrollments_count,
    (SELECT COUNT(*) FROM courses WHERE instructor_id = :user_id) as courses_taught,
    (SELECT COUNT(*) FROM submissions WHERE student_id = :user_id) as submissions_count
";
$stmt = $pdo->prepare($stats_query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - LMS</title>
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
        .role-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        .role-student { background: #dcfce7; color: #166534; }
        .role-instructor { background: #dbeafe; color: #1e40af; }
        .role-admin { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap"></i> Learning Management System
            </a>
            <div class="ms-auto">
                <a href="users.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="page-header">
            <h1><i class="fas fa-user-edit"></i> Edit User #<?php echo $user_id; ?></h1>
            <p class="text-muted mb-0">Update user information and permissions</p>
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

        <!-- User Statistics -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h6 class="text-muted mb-2">Enrollments</h6>
                    <h3 class="mb-0"><?php echo $stats['enrollments_count']; ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h6 class="text-muted mb-2">Courses Teaching</h6>
                    <h3 class="mb-0"><?php echo $stats['courses_taught']; ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h6 class="text-muted mb-2">Submissions</h6>
                    <h3 class="mb-0"><?php echo $stats['submissions_count']; ?></h3>
                </div>
            </div>
        </div>

        <div class="card form-card">
            <div class="card-header text-white">
                <h5 class="mb-0"><i class="fas fa-edit"></i> User Information</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user"></i> Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" 
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email"
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">
                                <i class="fas fa-user-tag"></i> Role <span class="text-danger">*</span>
                            </label>
                            <select name="role" id="role" class="form-select" required>
                                <option value="student" <?php echo ($user['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                                <option value="instructor" <?php echo ($user['role'] === 'instructor') ? 'selected' : ''; ?>>Instructor</option>
                                <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <small class="text-muted">Current: <span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> New Password
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   class="form-control" 
                                   placeholder="Leave blank to keep current password">
                            <small class="text-muted">Only fill if changing password</small>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Account Created:</strong> <?php echo date('F d, Y g:i A', strtotime($user['created_at'])); ?>
                    </div>

                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="users.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
