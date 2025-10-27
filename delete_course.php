<?php
require_once 'config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: courses.php");
    exit();
}

$course_id = $_GET['id'];

try {
    // Check if course has dependencies
    $check_stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM enrollments WHERE course_id = ?) as enrollment_count,
            (SELECT COUNT(*) FROM assignments WHERE course_id = ?) as assignment_count,
            (SELECT COUNT(*) FROM reviews WHERE course_id = ?) as review_count
    ");
    $check_stmt->execute([$course_id, $course_id, $course_id]);
    $dependencies = $check_stmt->fetch();
    
    if ($dependencies['enrollment_count'] > 0 || $dependencies['assignment_count'] > 0 || $dependencies['review_count'] > 0) {
        $error = "Cannot delete course with existing enrollments, assignments, or reviews.";
        header("Location: courses.php?error=" . urlencode($error));
        exit();
    }
    
    // Delete the course
    $stmt = $pdo->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt->execute([$course_id]);
    
    header("Location: courses.php?success=Course deleted successfully");
} catch (PDOException $e) {
    header("Location: courses.php?error=" . urlencode("Error: " . $e->getMessage()));
}
exit();
?>
