<?php
require_once 'config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: enrollments.php");
    exit();
}

$enrollment_id = $_GET['id'];

try {
    // Delete the enrollment
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE enrollment_id = ?");
    $stmt->execute([$enrollment_id]);
    
    header("Location: enrollments.php?success=Enrollment deleted successfully");
} catch (PDOException $e) {
    header("Location: enrollments.php?error=" . urlencode("Error: " . $e->getMessage()));
}
exit();
?>
