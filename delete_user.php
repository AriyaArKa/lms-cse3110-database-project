<?php
require_once 'config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

try {
    // Check if user has dependencies (enrollments, courses, etc.)
    $check_stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM courses WHERE instructor_id = ?) as course_count,
            (SELECT COUNT(*) FROM enrollments WHERE student_id = ?) as enrollment_count,
            (SELECT COUNT(*) FROM submissions WHERE student_id = ?) as submission_count
    ");
    $check_stmt->execute([$user_id, $user_id, $user_id]);
    $dependencies = $check_stmt->fetch();
    
    if ($dependencies['course_count'] > 0 || $dependencies['enrollment_count'] > 0 || $dependencies['submission_count'] > 0) {
        $error = "Cannot delete user with existing courses, enrollments, or submissions. Please remove those first.";
        header("Location: users.php?error=" . urlencode($error));
        exit();
    }
    
    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    header("Location: users.php?success=User deleted successfully");
} catch (PDOException $e) {
    header("Location: users.php?error=" . urlencode("Error deleting user: " . $e->getMessage()));
}
exit();
?>
