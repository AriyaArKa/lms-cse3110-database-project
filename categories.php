<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();

// Get all categories with course counts
$stmt = $conn->query("
    SELECT 
        cc.category_id,
        cc.name,
        COUNT(DISTINCT c.course_id) as total_courses,
        COUNT(DISTINCT e.enrollment_id) as total_enrollments,
        AVG(c.price) as avg_price
    FROM course_categories cc
    LEFT JOIN courses c ON cc.category_id = c.category_id
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    GROUP BY cc.category_id
    ORDER BY cc.name
");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Categories - LMS</title>
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

        .category-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .category-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            margin: 0.25rem;
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
                        <a class="nav-link active" href="categories.php"><i class="fas fa-tags"></i> Categories</a>
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
                    <h1 class="mb-0"><i class="fas fa-tags text-primary"></i> Course Categories</h1>
                    <p class="text-muted mb-0">Browse and manage course categories</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="add_category.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Category
                    </a>
                </div>
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="row">
            <?php
            $icons = [
                'Web Development' => 'code',
                'Data Science' => 'chart-line',
                'Mobile Development' => 'mobile-alt',
                'Database Management' => 'database',
                'Cybersecurity' => 'shield-alt',
                'Cloud Computing' => 'cloud',
                'Artificial Intelligence' => 'brain',
                'Business & Marketing' => 'briefcase'
            ];

            foreach ($categories as $category):
                $icon = $icons[$category['name']] ?? 'folder';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="category-card">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="category-icon">
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="mb-1"><?php echo htmlspecialchars($category['name']); ?></h5>
                                <div class="mb-2">
                                    <span class="badge bg-primary stat-badge">
                                        <i class="fas fa-book"></i> <?php echo $category['total_courses']; ?> Courses
                                    </span>
                                    <span class="badge bg-success stat-badge">
                                        <i class="fas fa-users"></i> <?php echo $category['total_enrollments']; ?> Students
                                    </span>
                                </div>
                                <?php if ($category['avg_price']): ?>
                                    <p class="mb-2 text-muted">
                                        <small>Avg Price: <strong
                                                class="text-success">$<?php echo number_format($category['avg_price'], 2); ?></strong></small>
                                    </p>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <a href="view_category.php?id=<?php echo $category['category_id']; ?>"
                                        class="btn btn-sm btn-info me-2">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <a href="courses.php?category=<?php echo $category['category_id']; ?>"
                                        class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-book"></i> Courses
                                    </a>
                                    <a href="edit_category.php?id=<?php echo $category['category_id']; ?>"
                                        class="btn btn-sm btn-outline-secondary me-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_category.php?id=<?php echo $category['category_id']; ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Are you sure? This will affect all courses in this category.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($categories) == 0): ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                <h3 class="text-muted">No Categories Found</h3>
                <p class="text-muted">Start by creating your first course category.</p>
                <a href="add_category.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Category
                </a>
            </div>
        <?php endif; ?>

        <!-- SQL Query Display -->
        <div class="card mt-4 mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-code"></i> SQL Query Used</h5>
            </div>
            <div class="card-body">
                <?php
                $categories_sql = "SELECT 
    cc.category_id,
    cc.name,
    COUNT(DISTINCT c.course_id) as total_courses,
    COUNT(DISTINCT e.enrollment_id) as total_enrollments,
    AVG(c.price) as avg_price
FROM course_categories cc
LEFT JOIN courses c ON cc.category_id = c.category_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
GROUP BY cc.category_id
ORDER BY cc.name";
                displaySQL($categories_sql, "Categories Query with LEFT JOIN and Aggregates", "categories_main_query");
                ?>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted py-4 mt-5">
        <p>&copy; 2024 Learning Management System</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>