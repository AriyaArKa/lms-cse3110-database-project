<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4"><i class="fas fa-database"></i> Complete Database Setup</h1>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo = new PDO('mysql:host=localhost;dbname=university_db', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $results = [];
                $errors = [];

                // Create Procedures
                $procedures = [
                    'enroll_student' => "
                        CREATE PROCEDURE enroll_student(
                            IN p_student_id INT,
                            IN p_course_id INT
                        )
                        BEGIN
                            DECLARE student_exists INT;
                            DECLARE course_exists INT;
                            DECLARE already_enrolled INT;
                            
                            SELECT COUNT(*) INTO student_exists FROM users WHERE user_id = p_student_id AND role = 'student';
                            SELECT COUNT(*) INTO course_exists FROM courses WHERE course_id = p_course_id;
                            SELECT COUNT(*) INTO already_enrolled FROM enrollments WHERE student_id = p_student_id AND course_id = p_course_id;
                            
                            IF student_exists = 0 THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Student not found';
                            ELSEIF course_exists = 0 THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Course not found';
                            ELSEIF already_enrolled > 0 THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Student already enrolled in this course';
                            ELSE
                                INSERT INTO enrollments (student_id, course_id, enrolled_at, progress)
                                VALUES (p_student_id, p_course_id, NOW(), 0);
                            END IF;
                        END
                    ",
                    'get_top_students' => "
                        CREATE PROCEDURE get_top_students(IN limit_count INT)
                        BEGIN
                            SELECT
                                u.user_id,
                                u.name,
                                u.email,
                                AVG(e.progress) AS avg_progress,
                                COUNT(e.enrollment_id) AS course_count
                            FROM users u
                            JOIN enrollments e ON u.user_id = e.student_id
                            WHERE u.role = 'student'
                            GROUP BY u.user_id
                            ORDER BY avg_progress DESC
                            LIMIT limit_count;
                        END
                    ",
                    'course_revenue_report' => "
                        CREATE PROCEDURE course_revenue_report(IN p_course_id INT)
                        BEGIN
                            SELECT
                                c.course_id,
                                c.title,
                                c.price,
                                COUNT(e.enrollment_id) AS total_enrollments,
                                (c.price * COUNT(e.enrollment_id)) AS total_revenue
                            FROM courses c
                            LEFT JOIN enrollments e ON c.course_id = e.course_id
                            WHERE c.course_id = p_course_id
                            GROUP BY c.course_id;
                        END
                    ",
                    'update_progress' => "
                        CREATE PROCEDURE update_progress(
                            IN p_student_id INT,
                            IN p_course_id INT,
                            IN p_progress INT
                        )
                        BEGIN
                            IF p_progress < 0 OR p_progress > 100 THEN
                                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Progress must be between 0 and 100';
                            ELSE
                                UPDATE enrollments
                                SET progress = p_progress
                                WHERE student_id = p_student_id AND course_id = p_course_id;
                                SELECT 'Progress updated successfully' AS message;
                            END IF;
                        END
                    "
                ];

                foreach ($procedures as $name => $sql) {
                    try {
                        $pdo->exec("DROP PROCEDURE IF EXISTS $name");
                        $pdo->exec($sql);
                        $results[] = "✓ Created procedure: $name";
                    } catch (PDOException $e) {
                        $errors[] = "✗ Error creating procedure $name: " . $e->getMessage();
                    }
                }

                // Create Functions
                $functions = [
                    'course_completion_rate' => "
                        CREATE FUNCTION course_completion_rate(p_course_id INT)
                        RETURNS DECIMAL(5,2)
                        DETERMINISTIC
                        BEGIN
                            DECLARE completion_rate DECIMAL(5,2);
                            SELECT AVG(progress) INTO completion_rate
                            FROM enrollments
                            WHERE course_id = p_course_id;
                            RETURN IFNULL(completion_rate, 0);
                        END
                    ",
                    'get_grade_letter' => "
                        CREATE FUNCTION get_grade_letter(grade DECIMAL(5,2))
                        RETURNS VARCHAR(2)
                        DETERMINISTIC
                        BEGIN
                            DECLARE letter VARCHAR(2);
                            IF grade IS NULL THEN
                                RETURN 'N/A';
                            ELSEIF grade >= 90 THEN
                                SET letter = 'A';
                            ELSEIF grade >= 80 THEN
                                SET letter = 'B';
                            ELSEIF grade >= 70 THEN
                                SET letter = 'C';
                            ELSEIF grade >= 60 THEN
                                SET letter = 'D';
                            ELSE
                                SET letter = 'F';
                            END IF;
                            RETURN letter;
                        END
                    ",
                    'count_student_enrollments' => "
                        CREATE FUNCTION count_student_enrollments(p_student_id INT)
                        RETURNS INT
                        DETERMINISTIC
                        BEGIN
                            DECLARE enrollment_count INT;
                            SELECT COUNT(*) INTO enrollment_count
                            FROM enrollments
                            WHERE student_id = p_student_id;
                            RETURN enrollment_count;
                        END
                    ",
                    'calculate_course_revenue' => "
                        CREATE FUNCTION calculate_course_revenue(p_course_id INT)
                        RETURNS DECIMAL(10,2)
                        DETERMINISTIC
                        BEGIN
                            DECLARE revenue DECIMAL(10,2);
                            SELECT c.price * COUNT(e.enrollment_id) INTO revenue
                            FROM courses c
                            LEFT JOIN enrollments e ON c.course_id = e.course_id
                            WHERE c.course_id = p_course_id
                            GROUP BY c.course_id;
                            RETURN IFNULL(revenue, 0);
                        END
                    "
                ];

                foreach ($functions as $name => $sql) {
                    try {
                        $pdo->exec("DROP FUNCTION IF EXISTS $name");
                        $pdo->exec($sql);
                        $results[] = "✓ Created function: $name";
                    } catch (PDOException $e) {
                        $errors[] = "✗ Error creating function $name: " . $e->getMessage();
                    }
                }

                // Create Triggers
                $triggers = [
                    'after_enrollment_insert' => "
                        CREATE TRIGGER after_enrollment_insert
                        AFTER INSERT ON enrollments
                        FOR EACH ROW
                        BEGIN
                            -- This trigger would log enrollment in an audit table if it existed
                            -- For demo purposes, we just validate the trigger works
                            SET @last_enrollment_id = NEW.enrollment_id;
                        END
                    ",
                    'before_course_delete' => "
                        CREATE TRIGGER before_course_delete
                        BEFORE DELETE ON courses
                        FOR EACH ROW
                        BEGIN
                            DECLARE enrollment_count INT;
                            SELECT COUNT(*) INTO enrollment_count
                            FROM enrollments
                            WHERE course_id = OLD.course_id;
                            
                            IF enrollment_count > 0 THEN
                                SIGNAL SQLSTATE '45000'
                                SET MESSAGE_TEXT = 'Cannot delete course with existing enrollments';
                            END IF;
                        END
                    ",
                    'before_review_insert' => "
                        CREATE TRIGGER before_review_insert
                        BEFORE INSERT ON reviews
                        FOR EACH ROW
                        BEGIN
                            IF NEW.rating < 1 OR NEW.rating > 5 THEN
                                SIGNAL SQLSTATE '45000'
                                SET MESSAGE_TEXT = 'Rating must be between 1 and 5';
                            END IF;
                            
                            IF NOT EXISTS (SELECT 1 FROM enrollments WHERE student_id = NEW.student_id AND course_id = NEW.course_id) THEN
                                SIGNAL SQLSTATE '45000'
                                SET MESSAGE_TEXT = 'Student must be enrolled in course to review it';
                            END IF;
                        END
                    ",
                    'before_course_insert' => "
                        CREATE TRIGGER before_course_insert
                        BEFORE INSERT ON courses
                        FOR EACH ROW
                        BEGIN
                            IF NEW.price < 0 THEN
                                SIGNAL SQLSTATE '45000'
                                SET MESSAGE_TEXT = 'Course price cannot be negative';
                            END IF;
                        END
                    "
                ];

                foreach ($triggers as $name => $sql) {
                    try {
                        $pdo->exec("DROP TRIGGER IF EXISTS $name");
                        $pdo->exec($sql);
                        $results[] = "✓ Created trigger: $name";
                    } catch (PDOException $e) {
                        $errors[] = "✗ Error creating trigger $name: " . $e->getMessage();
                    }
                }

                // Display Results
                if (!empty($results)) {
                    echo '<div class="alert alert-success">';
                    echo '<h4><i class="fas fa-check-circle"></i> Successfully Created:</h4>';
                    echo '<ul class="mb-0">';
                    foreach ($results as $result) {
                        echo '<li>' . htmlspecialchars($result) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }

                if (!empty($errors)) {
                    echo '<div class="alert alert-warning">';
                    echo '<h4><i class="fas fa-exclamation-triangle"></i> Errors/Warnings:</h4>';
                    echo '<ul class="mb-0">';
                    foreach ($errors as $error) {
                        echo '<li>' . htmlspecialchars($error) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }

                echo '<div class="alert alert-info">';
                echo '<h4><i class="fas fa-info-circle"></i> Setup Complete!</h4>';
                echo '<p class="mb-2">Created: <strong>' . count($results) . '</strong> objects</p>';
                if (!empty($errors)) {
                    echo '<p class="mb-2">Errors: <strong>' . count($errors) . '</strong></p>';
                }
                echo '<hr>';
                echo '<p class="mb-0">You can now visit: <a href="sql_operations.php" class="alert-link btn btn-info btn-sm"><i class="fas fa-database"></i> SQL Operations Demo</a></p>';
                echo '</div>';

            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            ?>
            <div class="alert alert-warning">
                <h4>⚠️ Important</h4>
                <p>This will create all stored procedures, functions, and triggers needed for the SQL demo.</p>
                <p>Click the button below to complete the database setup.</p>
            </div>

            <form method="POST">
                <button type="submit" class="btn btn-primary btn-lg">
                    🚀 Complete Database Setup
                </button>
            </form>

            <div class="mt-4">
                <h5>What will be created:</h5>
                <ul>
                    <li>4 Stored Procedures (enroll_student, get_top_students, course_revenue_report, update_progress)</li>
                    <li>4 Functions (course_completion_rate, get_grade_letter, count_student_enrollments,
                        calculate_course_revenue)</li>
                    <li>4 Triggers (after_enrollment_insert, before_course_delete, before_review_insert,
                        before_course_insert)</li>
                </ul>
            </div>
            <?php
        }
        ?>
    </div>
</body>

</html>