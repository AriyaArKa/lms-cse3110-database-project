-- Cleanup existing objects
DROP PROCEDURE IF EXISTS enroll_student;
DROP PROCEDURE IF EXISTS get_top_students;
DROP PROCEDURE IF EXISTS course_revenue_report;
DROP PROCEDURE IF EXISTS update_progress;
DROP FUNCTION IF EXISTS course_completion_rate;
DROP FUNCTION IF EXISTS get_grade_letter;
DROP FUNCTION IF EXISTS count_student_enrollments;
DROP FUNCTION IF EXISTS calculate_course_revenue;
DROP TRIGGER IF EXISTS after_enrollment_insert;
DROP TRIGGER IF EXISTS before_course_delete;
DROP TRIGGER IF EXISTS before_review_insert;
DROP TRIGGER IF EXISTS after_submission_insert;
DROP TRIGGER IF EXISTS before_course_insert;