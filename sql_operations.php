<?php
require_once 'config/database.php';
require_once 'includes/sql_display.php';

$database = new Database();
$conn = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Operations Demonstration - LMS</title>
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

        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1.5rem;
            border-bottom: 3px solid rgba(255, 255, 255, 0.3);
        }

        .section-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .section-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .section-body {
            padding: 2rem;
        }

        .operation-item {
            background: #f8fafc;
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
        }

        .operation-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .operation-desc {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .badge-feature {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-right: 0.5rem;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .page-header-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .toc {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .toc-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .toc-list {
            list-style: none;
            padding: 0;
        }

        .toc-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .toc-list li:last-child {
            border-bottom: none;
        }

        .toc-list a {
            color: #475569;
            text-decoration: none;
            transition: all 0.2s;
        }

        .toc-list a:hover {
            color: var(--primary-color);
            padding-left: 0.5rem;
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
                        <a class="nav-link active" href="sql_operations.php"><i class="fas fa-database"></i> SQL
                            Demo</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="page-header-banner">
            <h1><i class="fas fa-database"></i> SQL Operations Demonstration</h1>
            <p class="mb-0 fs-5">Comprehensive showcase of all SQL features implemented in this LMS database</p>
        </div>

        <!-- Table of Contents -->
        <div class="toc">
            <div class="toc-title"><i class="fas fa-list"></i> Quick Navigation</div>
            <ul class="toc-list">
                <li><a href="#basic-operations"><i class="fas fa-chevron-right"></i> 1. Basic SQL Operations (SELECT,
                        WHERE, ORDER BY, LIMIT)</a></li>
                <li><a href="#joins"><i class="fas fa-chevron-right"></i> 2. JOIN Operations (INNER, LEFT, RIGHT)</a>
                </li>
                <li><a href="#aggregates"><i class="fas fa-chevron-right"></i> 3. Aggregate Functions (COUNT, AVG, SUM,
                        MAX, MIN)</a></li>
                <li><a href="#grouping"><i class="fas fa-chevron-right"></i> 4. GROUP BY & HAVING</a></li>
                <li><a href="#subqueries"><i class="fas fa-chevron-right"></i> 5. Subqueries & Nested Queries</a></li>
                <li><a href="#set-operations"><i class="fas fa-chevron-right"></i> 6. Set Operations (UNION, INTERSECT,
                        EXCEPT)</a></li>
                <li><a href="#views"><i class="fas fa-chevron-right"></i> 7. Views</a></li>
            </ul>
        </div>

        <!-- 1. BASIC SQL OPERATIONS -->
        <div id="basic-operations" class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-search"></i> 1. Basic SQL Operations</h3>
                <p>Fundamental SQL queries demonstrating SELECT, WHERE, ORDER BY, and LIMIT clauses</p>
            </div>
            <div class="section-body">
                <!-- SELECT with WHERE -->
                <div class="operation-item">
                    <div class="operation-title">1.1 SELECT with WHERE Condition</div>
                    <div class="operation-desc">Retrieve all students from the users table</div>
                    <span class="badge-feature">SELECT</span>
                    <span class="badge-feature">WHERE</span>
                    <?php
                    $sql = "SELECT user_id, name, email, role, created_at 
                            FROM users 
                            WHERE role = 'student'";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, array_slice($results, 0, 5), "View SQL Query", "basic_select");
                    ?>
                </div>

                <!-- ORDER BY -->
                <div class="operation-item">
                    <div class="operation-title">1.2 ORDER BY - Sorting Results</div>
                    <div class="operation-desc">Get recent users ordered by registration date (descending)</div>
                    <span class="badge-feature">SELECT</span>
                    <span class="badge-feature">ORDER BY</span>
                    <span class="badge-feature">LIMIT</span>
                    <?php
                    $sql = "SELECT user_id, name, email, created_at 
                            FROM users 
                            ORDER BY created_at DESC 
                            LIMIT 5";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "basic_order");
                    ?>
                </div>

                <!-- LIKE Pattern Matching -->
                <div class="operation-item">
                    <div class="operation-title">1.3 LIKE - Pattern Matching</div>
                    <div class="operation-desc">Search for courses with "Development" in the title</div>
                    <span class="badge-feature">LIKE</span>
                    <span class="badge-feature">Pattern Matching</span>
                    <?php
                    $sql = "SELECT course_id, title, description, price 
                            FROM courses 
                            WHERE title LIKE '%Development%'";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "basic_like");
                    ?>
                </div>

                <!-- BETWEEN -->
                <div class="operation-item">
                    <div class="operation-title">1.4 BETWEEN - Range Queries</div>
                    <div class="operation-desc">Find courses priced between $80 and $100</div>
                    <span class="badge-feature">BETWEEN</span>
                    <span class="badge-feature">Range Query</span>
                    <?php
                    $sql = "SELECT course_id, title, price, category_id 
                            FROM courses 
                            WHERE price BETWEEN 80 AND 100 
                            ORDER BY price";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "basic_between");
                    ?>
                </div>
            </div>
        </div>

        <!-- 2. JOIN OPERATIONS -->
        <div id="joins" class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-link"></i> 2. JOIN Operations</h3>
                <p>Combining data from multiple tables using different types of joins</p>
            </div>
            <div class="section-body">
                <!-- INNER JOIN -->
                <div class="operation-item">
                    <div class="operation-title">2.1 INNER JOIN - Matching Records</div>
                    <div class="operation-desc">Get courses with their categories and instructors (only matching
                        records)</div>
                    <span class="badge-feature">INNER JOIN</span>
                    <span class="badge-feature">Multiple Tables</span>
                    <?php
                    $sql = "SELECT 
                                c.course_id,
                                c.title AS course_title,
                                c.price,
                                cc.name AS category,
                                u.name AS instructor
                            FROM courses c
                            INNER JOIN course_categories cc ON c.category_id = cc.category_id
                            INNER JOIN users u ON c.instructor_id = u.user_id
                            LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "join_inner");
                    ?>
                </div>

                <!-- LEFT JOIN -->
                <div class="operation-item">
                    <div class="operation-title">2.2 LEFT JOIN - Include All from Left Table</div>
                    <div class="operation-desc">Get all courses with enrollment count (including courses with no
                        enrollments)</div>
                    <span class="badge-feature">LEFT JOIN</span>
                    <span class="badge-feature">COUNT</span>
                    <?php
                    $sql = "SELECT 
                                c.course_id,
                                c.title,
                                c.price,
                                COUNT(e.enrollment_id) AS total_enrollments
                            FROM courses c
                            LEFT JOIN enrollments e ON c.course_id = e.course_id
                            GROUP BY c.course_id
                            ORDER BY total_enrollments DESC
                            LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "join_left");
                    ?>
                </div>

                <!-- Multiple JOINS -->
                <div class="operation-item">
                    <div class="operation-title">2.3 Multiple JOINs - Complex Query</div>
                    <div class="operation-desc">Get student enrollments with course and category information</div>
                    <span class="badge-feature">Multiple JOINs</span>
                    <span class="badge-feature">4 Tables</span>
                    <?php
                    $sql = "SELECT 
                                u.name AS student_name,
                                c.title AS course_title,
                                cc.name AS category,
                                e.progress,
                                e.enrolled_at
                            FROM enrollments e
                            INNER JOIN users u ON e.student_id = u.user_id
                            INNER JOIN courses c ON e.course_id = c.course_id
                            INNER JOIN course_categories cc ON c.category_id = cc.category_id
                            ORDER BY e.enrolled_at DESC
                            LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "join_multiple");
                    ?>
                </div>
            </div>
        </div>

        <!-- 3. AGGREGATE FUNCTIONS -->
        <div id="aggregates" class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-calculator"></i> 3. Aggregate Functions</h3>
                <p>Statistical calculations using COUNT, AVG, SUM, MAX, MIN</p>
            </div>
            <div class="section-body">
                <!-- COUNT -->
                <div class="operation-item">
                    <div class="operation-title">3.1 COUNT - Counting Records</div>
                    <div class="operation-desc">Count users by role</div>
                    <span class="badge-feature">COUNT</span>
                    <span class="badge-feature">GROUP BY</span>
                    <?php
                    $sql = "SELECT 
                                role,
                                COUNT(*) AS total_users
                            FROM users
                            GROUP BY role
                            ORDER BY total_users DESC";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "agg_count");
                    ?>
                </div>

                <!-- AVG -->
                <div class="operation-item">
                    <div class="operation-title">3.2 AVG - Average Calculations</div>
                    <div class="operation-desc">Calculate average course ratings by category</div>
                    <span class="badge-feature">AVG</span>
                    <span class="badge-feature">GROUP BY</span>
                    <?php
                    $sql = "SELECT 
                                cc.name AS category,
                                COUNT(DISTINCT c.course_id) AS total_courses,
                                AVG(r.rating) AS avg_rating,
                                COUNT(r.review_id) AS total_reviews
                            FROM course_categories cc
                            LEFT JOIN courses c ON cc.category_id = c.category_id
                            LEFT JOIN reviews r ON c.course_id = r.course_id
                            GROUP BY cc.category_id
                            HAVING avg_rating IS NOT NULL
                            ORDER BY avg_rating DESC";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "agg_avg");
                    ?>
                </div>

                <!-- SUM -->
                <div class="operation-item">
                    <div class="operation-title">3.3 SUM - Total Calculations</div>
                    <div class="operation-desc">Calculate total revenue per instructor</div>
                    <span class="badge-feature">SUM</span>
                    <span class="badge-feature">Revenue Analysis</span>
                    <?php
                    $sql = "SELECT 
                                u.name AS instructor,
                                COUNT(DISTINCT c.course_id) AS courses_taught,
                                COUNT(e.enrollment_id) AS total_enrollments,
                                SUM(c.price) AS total_revenue
                            FROM users u
                            INNER JOIN courses c ON u.user_id = c.instructor_id
                            LEFT JOIN enrollments e ON c.course_id = e.course_id
                            WHERE u.role = 'instructor'
                            GROUP BY u.user_id
                            ORDER BY total_revenue DESC
                            LIMIT 5";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "agg_sum");
                    ?>
                </div>

                <!-- MAX and MIN -->
                <div class="operation-item">
                    <div class="operation-title">3.4 MAX & MIN - Finding Extremes</div>
                    <div class="operation-desc">Find highest and lowest priced courses by category</div>
                    <span class="badge-feature">MAX</span>
                    <span class="badge-feature">MIN</span>
                    <?php
                    $sql = "SELECT 
                                cc.name AS category,
                                MIN(c.price) AS lowest_price,
                                MAX(c.price) AS highest_price,
                                AVG(c.price) AS avg_price,
                                COUNT(*) AS course_count
                            FROM course_categories cc
                            INNER JOIN courses c ON cc.category_id = c.category_id
                            GROUP BY cc.category_id
                            ORDER BY category";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "agg_max_min");
                    ?>
                </div>
            </div>
        </div>

        <!-- 4. GROUP BY & HAVING -->
        <div id="grouping" class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-layer-group"></i> 4. GROUP BY & HAVING</h3>
                <p>Grouping data and filtering groups with conditions</p>
            </div>
            <div class="section-body">
                <!-- GROUP BY with HAVING -->
                <div class="operation-item">
                    <div class="operation-title">4.1 GROUP BY with HAVING Clause</div>
                    <div class="operation-desc">Find courses with more than 3 enrollments</div>
                    <span class="badge-feature">GROUP BY</span>
                    <span class="badge-feature">HAVING</span>
                    <?php
                    $sql = "SELECT 
                                c.title AS course,
                                COUNT(e.enrollment_id) AS total_enrollments,
                                AVG(e.progress) AS avg_progress,
                                c.price
                            FROM courses c
                            LEFT JOIN enrollments e ON c.course_id = e.course_id
                            GROUP BY c.course_id
                            HAVING total_enrollments > 3
                            ORDER BY total_enrollments DESC";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "group_having");
                    ?>
                </div>

                <!-- Multiple GROUP BY columns -->
                <div class="operation-item">
                    <div class="operation-title">4.2 Multiple GROUP BY Columns</div>
                    <div class="operation-desc">Student submission statistics by course</div>
                    <span class="badge-feature">GROUP BY Multiple</span>
                    <span class="badge-feature">Complex Grouping</span>
                    <?php
                    $sql = "SELECT 
                                u.name AS student,
                                c.title AS course,
                                COUNT(s.submission_id) AS submissions,
                                AVG(s.grade) AS avg_grade
                            FROM users u
                            INNER JOIN enrollments e ON u.user_id = e.student_id
                            INNER JOIN courses c ON e.course_id = c.course_id
                            LEFT JOIN assignments a ON c.course_id = a.course_id
                            LEFT JOIN submissions s ON a.assignment_id = s.assignment_id AND s.student_id = u.user_id
                            WHERE u.role = 'student'
                            GROUP BY u.user_id, c.course_id
                            HAVING submissions > 0
                            ORDER BY avg_grade DESC
                            LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "group_multiple");
                    ?>
                </div>
            </div>
        </div>

        <!-- 5. SUBQUERIES -->
        <div id="subqueries" class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-code-branch"></i> 5. Subqueries & Nested Queries</h3>
                <p>Using queries within queries for complex data retrieval</p>
            </div>
            <div class="section-body">
                <!-- Subquery in WHERE -->
                <div class="operation-item">
                    <div class="operation-title">5.1 Subquery in WHERE Clause</div>
                    <div class="operation-desc">Find courses more expensive than average</div>
                    <span class="badge-feature">Subquery</span>
                    <span class="badge-feature">WHERE</span>
                    <?php
                    $sql = "SELECT 
                                course_id,
                                title,
                                price,
                                (SELECT AVG(price) FROM courses) AS avg_price
                            FROM courses
                            WHERE price > (SELECT AVG(price) FROM courses)
                            ORDER BY price DESC
                            LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "sub_where");
                    ?>
                </div>

                <!-- Correlated Subquery -->
                <div class="operation-item">
                    <div class="operation-title">5.2 Correlated Subquery</div>
                    <div class="operation-desc">Students with above-average grades in their courses</div>
                    <span class="badge-feature">Correlated Subquery</span>
                    <span class="badge-feature">Advanced</span>
                    <?php
                    $sql = "SELECT 
                                u.name AS student,
                                (SELECT AVG(grade) FROM submissions WHERE student_id = u.user_id) AS avg_grade,
                                (SELECT COUNT(*) FROM enrollments WHERE student_id = u.user_id) AS courses_enrolled
                            FROM users u
                            WHERE u.role = 'student'
                            AND (SELECT AVG(grade) FROM submissions WHERE student_id = u.user_id) > 85
                            ORDER BY avg_grade DESC
                            LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "sub_correlated");
                    ?>
                </div>

                <!-- Subquery in FROM (Derived Table) -->
                <div class="operation-item">
                    <div class="operation-title">5.3 Subquery in FROM Clause (Derived Table)</div>
                    <div class="operation-desc">Top rated courses from a filtered set</div>
                    <span class="badge-feature">Derived Table</span>
                    <span class="badge-feature">FROM Clause</span>
                    <?php
                    $sql = "SELECT 
                                course_title,
                                avg_rating,
                                review_count,
                                enrollment_count
                            FROM (
                                SELECT 
                                    c.title AS course_title,
                                    AVG(r.rating) AS avg_rating,
                                    COUNT(DISTINCT r.review_id) AS review_count,
                                    COUNT(DISTINCT e.enrollment_id) AS enrollment_count
                                FROM courses c
                                LEFT JOIN reviews r ON c.course_id = r.course_id
                                LEFT JOIN enrollments e ON c.course_id = e.course_id
                                GROUP BY c.course_id
                            ) AS course_stats
                            WHERE avg_rating >= 4.0
                            ORDER BY avg_rating DESC
                            LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "sub_from");
                    ?>
                </div>
            </div>
        </div>

        <!-- 6. SET OPERATIONS -->
        <div id="set-operations" class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-object-group"></i> 6. Set Operations</h3>
                <p>Combining result sets with UNION, UNION ALL, and similar operations</p>
            </div>
            <div class="section-body">
                <!-- UNION -->
                <div class="operation-item">
                    <div class="operation-title">6.1 UNION - Combining Distinct Results</div>
                    <div class="operation-desc">Get all users who are either instructors or have written reviews</div>
                    <span class="badge-feature">UNION</span>
                    <span class="badge-feature">Distinct Results</span>
                    <?php
                    $sql = "SELECT DISTINCT u.user_id, u.name, u.role, 'Instructor' AS category
                            FROM users u
                            WHERE u.role = 'instructor'
                            UNION
                            SELECT DISTINCT u.user_id, u.name, u.role, 'Reviewer' AS category
                            FROM users u
                            INNER JOIN reviews r ON u.user_id = r.student_id
                            ORDER BY user_id
                            LIMIT 15";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "set_union");
                    ?>
                </div>

                <!-- UNION ALL -->
                <div class="operation-item">
                    <div class="operation-title">6.2 UNION ALL - Including Duplicates</div>
                    <div class="operation-desc">List all course interactions (enrollments and reviews)</div>
                    <span class="badge-feature">UNION ALL</span>
                    <span class="badge-feature">With Duplicates</span>
                    <?php
                    $sql = "SELECT c.title AS course, u.name AS user, 'Enrollment' AS action, e.enrolled_at AS action_date
                            FROM enrollments e
                            INNER JOIN courses c ON e.course_id = c.course_id
                            INNER JOIN users u ON e.student_id = u.user_id
                            UNION ALL
                            SELECT c.title AS course, u.name AS user, 'Review' AS action, r.created_at AS action_date
                            FROM reviews r
                            INNER JOIN courses c ON r.course_id = c.course_id
                            INNER JOIN users u ON r.student_id = u.user_id
                            ORDER BY action_date DESC
                            LIMIT 15";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "set_union_all");
                    ?>
                </div>
            </div>
        </div>

        <!-- 7. VIEWS -->
        <div id="views" class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-eye"></i> 7. Views</h3>
                <p>Virtual tables created from stored queries</p>
            </div>
            <div class="section-body">
                <!-- Course Overview View -->
                <div class="operation-item">
                    <div class="operation-title">7.1 Course Overview View</div>
                    <div class="operation-desc">Pre-defined view combining course statistics</div>
                    <span class="badge-feature">VIEW</span>
                    <span class="badge-feature">Virtual Table</span>

                    <div class="alert alert-secondary mb-3">
                        <strong><i class="fas fa-code"></i> View Definition:</strong>
                        <?php
                        $viewDef = "CREATE OR REPLACE VIEW course_overview AS
SELECT 
    c.course_id,
    c.title,
    c.price,
    cc.name AS category,
    u.name AS instructor,
    COUNT(DISTINCT e.enrollment_id) AS total_enrollments,
    AVG(e.progress) AS avg_progress,
    AVG(r.rating) AS avg_rating
FROM courses c
LEFT JOIN course_categories cc ON c.category_id = cc.category_id
LEFT JOIN users u ON c.instructor_id = u.user_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
LEFT JOIN reviews r ON c.course_id = r.course_id
GROUP BY c.course_id";
                        displaySQL($viewDef, "View Definition SQL", "view_def_course");
                        ?>
                    </div>

                    <strong><i class="fas fa-play"></i> Querying the View:</strong>
                    <?php
                    $sql = "SELECT * FROM course_overview ORDER BY total_enrollments DESC LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "view_course");
                    ?>
                </div>

                <!-- Student Performance View -->
                <div class="operation-item">
                    <div class="operation-title">7.2 Student Performance View</div>
                    <div class="operation-desc">Aggregate student performance metrics</div>
                    <span class="badge-feature">VIEW</span>
                    <span class="badge-feature">Performance Metrics</span>

                    <div class="alert alert-secondary mb-3">
                        <strong><i class="fas fa-code"></i> View Definition:</strong>
                        <?php
                        $viewDef = "CREATE OR REPLACE VIEW student_performance AS
SELECT 
    u.user_id,
    u.name AS student_name,
    COUNT(DISTINCT e.course_id) AS courses_enrolled,
    AVG(e.progress) AS avg_progress,
    COUNT(DISTINCT s.submission_id) AS total_submissions,
    AVG(s.grade) AS avg_grade
FROM users u
LEFT JOIN enrollments e ON u.user_id = e.student_id
LEFT JOIN submissions s ON u.user_id = s.student_id
WHERE u.role = 'student'
GROUP BY u.user_id";
                        displaySQL($viewDef, "View Definition SQL", "view_def_student");
                        ?>
                    </div>

                    <strong><i class="fas fa-play"></i> Querying the View:</strong>
                    <?php
                    $sql = "SELECT * FROM student_performance ORDER BY avg_grade DESC LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "view_student");
                    ?>
                </div>

                <!-- Category Revenue View -->
                <div class="operation-item">
                    <div class="operation-title">7.3 Category Revenue View</div>
                    <div class="operation-desc">Revenue analysis by category</div>
                    <span class="badge-feature">VIEW</span>
                    <span class="badge-feature">Revenue Analysis</span>

                    <div class="alert alert-secondary mb-3">
                        <strong><i class="fas fa-code"></i> View Definition:</strong>
                        <?php
                        $viewDef = "CREATE OR REPLACE VIEW category_revenue AS
SELECT 
    cc.category_id,
    cc.name AS category_name,
    COUNT(DISTINCT c.course_id) AS total_courses,
    COUNT(DISTINCT e.enrollment_id) AS total_enrollments,
    SUM(c.price) AS total_revenue,
    AVG(c.price) AS avg_course_price
FROM course_categories cc
LEFT JOIN courses c ON cc.category_id = c.category_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
GROUP BY cc.category_id";
                        displaySQL($viewDef, "View Definition SQL", "view_def_revenue");
                        ?>
                    </div>

                    <strong><i class="fas fa-play"></i> Querying the View:</strong>
                    <?php
                    $sql = "SELECT * FROM category_revenue ORDER BY total_revenue DESC";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "view_revenue");
                    ?>
                </div>

                <!-- Instructor Dashboard View -->
                <div class="operation-item">
                    <div class="operation-title">7.4 Instructor Dashboard View</div>
                    <div class="operation-desc">Instructor performance and course statistics</div>
                    <span class="badge-feature">VIEW</span>
                    <span class="badge-feature">Instructor Analytics</span>

                    <div class="alert alert-secondary mb-3">
                        <strong><i class="fas fa-code"></i> View Definition:</strong>
                        <?php
                        $viewDef = "CREATE OR REPLACE VIEW instructor_dashboard AS
SELECT 
    u.user_id AS instructor_id,
    u.name AS instructor_name,
    COUNT(DISTINCT c.course_id) AS courses_taught,
    COUNT(DISTINCT e.enrollment_id) AS total_students,
    SUM(c.price) AS total_revenue,
    AVG(r.rating) AS avg_rating
FROM users u
LEFT JOIN courses c ON u.user_id = c.instructor_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
LEFT JOIN reviews r ON c.course_id = r.course_id
WHERE u.role = 'instructor'
GROUP BY u.user_id";
                        displaySQL($viewDef, "View Definition SQL", "view_def_instructor");
                        ?>
                    </div>

                    <strong><i class="fas fa-play"></i> Querying the View:</strong>
                    <?php
                    $sql = "SELECT * FROM instructor_dashboard ORDER BY courses_taught DESC LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "view_instructor");
                    ?>
                </div>

                <!-- Assignment Status View -->
                <div class="operation-item">
                    <div class="operation-title">7.5 Assignment Status View</div>
                    <div class="operation-desc">Assignment completion and grading statistics</div>
                    <span class="badge-feature">VIEW</span>
                    <span class="badge-feature">Assignment Tracking</span>

                    <div class="alert alert-secondary mb-3">
                        <strong><i class="fas fa-code"></i> View Definition:</strong>
                        <?php
                        $viewDef = "CREATE OR REPLACE VIEW assignment_status AS
SELECT 
    a.assignment_id,
    a.title AS assignment_title,
    c.title AS course_title,
    a.due_date,
    COUNT(DISTINCT s.submission_id) AS total_submissions,
    AVG(s.grade) AS avg_grade,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) AS enrolled_students
FROM assignments a
INNER JOIN courses c ON a.course_id = c.course_id
LEFT JOIN submissions s ON a.assignment_id = s.assignment_id
GROUP BY a.assignment_id";
                        displaySQL($viewDef, "View Definition SQL", "view_def_assignment");
                        ?>
                    </div>

                    <strong><i class="fas fa-play"></i> Querying the View:</strong>
                    <?php
                    $sql = "SELECT * FROM assignment_status ORDER BY due_date DESC LIMIT 10";
                    $stmt = $conn->query($sql);
                    $results = $stmt->fetchAll();
                    displaySQLWithResult($sql, $results, "View SQL Query", "view_assignment");
                    ?>
                </div>
            </div>
        </div>

    </div>

    <footer class="text-center text-muted py-4 mt-5">
        <p>&copy; 2024 Learning Management System - Complete SQL Feature Demonstration</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>