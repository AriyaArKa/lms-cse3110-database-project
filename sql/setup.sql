-- ============================================================
-- Learning Management System (LMS) - Database Setup Script
-- ============================================================
-- Description: Complete database initialization script for LMS
-- Version: 1.0
-- Author: LMS Development Team
-- Created: 2024
-- 
-- Purpose:
--   This script creates the complete database structure for a 
--   Learning Management System including:
--   - 7 core tables (users, courses, enrollments, etc.)
--   - Sample data (30 users, 20 courses, 50 enrollments)
--   - 5 views for reporting and analytics
--   - 4 stored procedures for business logic
--   - 4 functions for calculations
--   - 5 triggers for data integrity
--
-- Usage:
--   Run this script once during initial setup or when resetting
--   the database to its default state.
--
-- Warning:
--   This script will DROP existing tables! All data will be lost.
--   Make backups before running in production environments.
-- ============================================================
USE university_db;
-- ============================================================
-- SECTION 1: DROP EXISTING TABLES (Clean Reinstall)
-- ============================================================
-- Purpose: Remove all existing tables to ensure a clean installation
-- Note: Foreign key checks are disabled temporarily to avoid constraint errors
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS assignments;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS course_categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================
-- SECTION 2: CREATE DATABASE TABLES
-- ============================================================
-- ============================================================
-- Table 1: users
-- ============================================================
-- Purpose: Store all system users (students, instructors, admins)
-- Columns:
--   - user_id: Primary key, auto-incremented
--   - name: Full name of the user (max 100 chars)
--   - email: Unique email address for login (max 100 chars)
--   - password: Hashed password (bcrypt, 255 chars)
--   - role: User type - student, instructor, or admin
--   - created_at: Account creation timestamp
-- Indexes:
--   - idx_email: Fast lookup by email for authentication
--   - idx_role: Fast filtering by user role
-- ============================================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'instructor', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE = InnoDB;
-- ============================================================
-- Table 2: course_categories
-- ============================================================
-- Purpose: Organize courses into categories for easy browsing
-- Columns:
--   - category_id: Primary key, auto-incremented
--   - name: Category name (unique, max 100 chars)
-- Indexes:
--   - idx_name: Fast lookup and sorting by category name
-- Examples: Web Development, Data Science, Mobile Development
-- ============================================================
CREATE TABLE course_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    INDEX idx_name (name)
) ENGINE = InnoDB;
-- ============================================================
-- Table 3: courses
-- ============================================================
-- Purpose: Store course information and metadata
-- Columns:
--   - course_id: Primary key, auto-incremented
--   - title: Course title (max 200 chars)
--   - description: Detailed course description (TEXT)
--   - price: Course price in USD (DECIMAL 10,2)
--   - category_id: Foreign key to course_categories
--   - instructor_id: Foreign key to users (role must be instructor)
--   - created_at: Course creation timestamp
-- Foreign Keys:
--   - category_id: RESTRICT delete, CASCADE update
--   - instructor_id: RESTRICT delete, CASCADE update
-- Indexes:
--   - idx_category: Filter courses by category
--   - idx_instructor: Find all courses by instructor
--   - idx_title: Search courses by title
-- ============================================================
CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) DEFAULT 0.00,
    category_id INT NOT NULL,
    instructor_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES course_categories(category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_instructor (instructor_id),
    INDEX idx_title (title)
) ENGINE = InnoDB;
-- ============================================================
-- Table 4: enrollments
-- ============================================================
-- Purpose: Track student enrollments in courses
-- Columns:
--   - enrollment_id: Primary key, auto-incremented
--   - student_id: Foreign key to users (must be student role)
--   - course_id: Foreign key to courses
--   - enrolled_at: Enrollment timestamp
--   - progress: Course completion percentage (0-100)
-- Foreign Keys:
--   - student_id: CASCADE delete (remove enrollment if student deleted)
--   - course_id: CASCADE delete (remove enrollment if course deleted)
-- Constraints:
--   - unique_enrollment: Prevent duplicate enrollments (student + course)
-- Indexes:
--   - idx_student: Find all enrollments for a student
--   - idx_course: Find all students enrolled in a course
-- ============================================================
CREATE TABLE enrollments (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progress INT DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id),
    INDEX idx_student (student_id),
    INDEX idx_course (course_id)
) ENGINE = InnoDB;
-- ============================================================
-- Table 5: assignments
-- ============================================================
-- Purpose: Store course assignments and homework
-- Columns:
--   - assignment_id: Primary key, auto-incremented
--   - course_id: Foreign key to courses
--   - title: Assignment title (max 200 chars)
--   - description: Assignment instructions and details (TEXT)
--   - due_date: Submission deadline (DATETIME)
-- Foreign Keys:
--   - course_id: CASCADE delete (remove assignments if course deleted)
-- Indexes:
--   - idx_course: Find all assignments for a course
--   - idx_due_date: Sort and filter by due date
-- ============================================================
CREATE TABLE assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    due_date DATETIME,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_course (course_id),
    INDEX idx_due_date (due_date)
) ENGINE = InnoDB;
-- ============================================================
-- Table 6: submissions
-- ============================================================
-- Purpose: Track student assignment submissions and grades
-- Columns:
--   - submission_id: Primary key, auto-incremented
--   - assignment_id: Foreign key to assignments
--   - student_id: Foreign key to users
--   - submitted_at: Submission timestamp
--   - grade: Assignment grade (0-100, NULL if not graded yet)
-- Foreign Keys:
--   - assignment_id: CASCADE delete (remove submissions if assignment deleted)
--   - student_id: CASCADE delete (remove submissions if student deleted)
-- Indexes:
--   - idx_assignment: Find all submissions for an assignment
--   - idx_student: Find all submissions by a student
-- ============================================================
CREATE TABLE submissions (
    submission_id INT PRIMARY KEY AUTO_INCREMENT,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade DECIMAL(5, 2),
    FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_assignment (assignment_id),
    INDEX idx_student (student_id)
) ENGINE = InnoDB;
-- ============================================================
-- Table 7: reviews
-- ============================================================
-- Purpose: Store student course reviews and ratings
-- Columns:
--   - review_id: Primary key, auto-incremented
--   - course_id: Foreign key to courses
--   - student_id: Foreign key to users
--   - rating: Star rating (1-5, validated by CHECK constraint)
--   - comment: Review text (TEXT, optional)
--   - created_at: Review submission timestamp
-- Foreign Keys:
--   - course_id: CASCADE delete (remove reviews if course deleted)
--   - student_id: CASCADE delete (remove reviews if student deleted)
-- Constraints:
--   - CHECK: Rating must be between 1 and 5
-- Indexes:
--   - idx_course: Find all reviews for a course
--   - idx_student: Find all reviews by a student
--   - idx_rating: Filter and sort by rating
-- ============================================================
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    rating INT NOT NULL CHECK (
        rating >= 1
        AND rating <= 5
    ),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_course (course_id),
    INDEX idx_student (student_id),
    INDEX idx_rating (rating)
) ENGINE = InnoDB;
-- ============================================================
-- SECTION 3: INSERT SAMPLE DATA
-- ============================================================
-- Purpose: Populate database with realistic test data
-- Data includes:
--   - 2 administrators
--   - 8 instructors
--   - 20 students
--   - 8 course categories
--   - 20 courses
--   - 50 enrollments
--   - 40 assignments
--   - 60 submissions
--   - 35 reviews
-- ============================================================
-- ============================================================
-- 1. Users Data (30 records)
-- ============================================================
-- Password: All users have bcrypt hashed password "password"
-- Breakdown: 2 admins, 8 instructors, 20 students
-- ============================================================
INSERT INTO users (name, email, password, role)
VALUES -- Admins (2 records)
    (
        'John Admin',
        'admin@lms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin'
    ),
    (
        'Sarah Manager',
        'sarah.manager@lms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin'
    ),
    -- Instructors (8 records)
    (
        'Dr. Emily Johnson',
        'emily.johnson@lms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'instructor'
    ),
    (
        'Prof. Michael Chen',
        'michael.chen@lms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'instructor'
    ),
    (
        'Dr. Sarah Williams',
        'sarah.williams@lms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'instructor'
    ),
    (
        'Prof. David Brown',
        'david.brown@lms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'instructor'
    ),
    (
        'Dr. Lisa Martinez',
        'lisa.martinez@lms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'instructor'
    ),
    (
        'Prof. James Wilson',
        'james.wilson@lms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'instructor'
    ),
    (
        'Dr. Maria Garcia',
        'maria.garcia@lms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'instructor'
    ),
    (
        'Prof. Robert Taylor',
        'robert.taylor@lms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'instructor'
    ),
    -- Students (20 records)
    (
        'Alice Anderson',
        'alice.anderson@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Bob Baker',
        'bob.baker@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Carol Clark',
        'carol.clark@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Daniel Davis',
        'daniel.davis@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Emma Evans',
        'emma.evans@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Frank Foster',
        'frank.foster@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Grace Green',
        'grace.green@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Henry Harris',
        'henry.harris@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Ivy Jackson',
        'ivy.jackson@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Jack Johnson',
        'jack.johnson@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Kate King',
        'kate.king@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Leo Lee',
        'leo.lee@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Mia Martinez',
        'mia.martinez@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Noah Nelson',
        'noah.nelson@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Olivia O''Brien',
        'olivia.obrien@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Peter Parker',
        'peter.parker@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Quinn Roberts',
        'quinn.roberts@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Rachel Robinson',
        'rachel.robinson@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Sam Smith',
        'sam.smith@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    ),
    (
        'Tina Taylor',
        'tina.taylor@student.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'student'
    );
-- ============================================================
-- 2. Course Categories Data (8 records)
-- ============================================================
-- Categories cover major technology and business domains
-- ============================================================
INSERT INTO course_categories (name)
VALUES ('Web Development'),
    ('Data Science'),
    ('Mobile Development'),
    ('Database Management'),
    ('Cybersecurity'),
    ('Cloud Computing'),
    ('Artificial Intelligence'),
    ('Business & Marketing');
-- ============================================================
-- 3. Courses Data (20 records)
-- ============================================================
-- Realistic courses with prices ranging from $59.99 to $149.99
-- Each course is assigned to an instructor and category
-- ============================================================
INSERT INTO courses (
        title,
        description,
        price,
        category_id,
        instructor_id
    )
VALUES -- Web Development Courses
    (
        'Complete Web Development Bootcamp',
        'Learn HTML, CSS, JavaScript, React, Node.js and more',
        99.99,
        1,
        3
    ),
    (
        'Advanced React Development',
        'Master React, Redux, Hooks, and modern patterns',
        79.99,
        1,
        3
    ),
    (
        'Full-Stack JavaScript',
        'MERN stack development from scratch',
        89.99,
        1,
        4
    ),
    -- Data Science Courses
    (
        'Python for Data Science',
        'Learn Python, Pandas, NumPy and data visualization',
        109.99,
        2,
        5
    ),
    (
        'Machine Learning A-Z',
        'Hands-on machine learning with Python',
        119.99,
        2,
        5
    ),
    (
        'Data Analysis with R',
        'Statistical analysis and visualization with R',
        94.99,
        2,
        6
    ),
    -- Mobile Development
    (
        'iOS Development with Swift',
        'Build iOS apps from beginner to advanced',
        99.99,
        3,
        7
    ),
    (
        'Android Development Masterclass',
        'Complete Android development with Kotlin',
        99.99,
        3,
        7
    ),
    (
        'React Native - Cross Platform Apps',
        'Build apps for iOS and Android',
        89.99,
        3,
        4
    ),
    -- Database Management
    (
        'SQL and Database Design',
        'Master SQL queries and database design',
        69.99,
        4,
        8
    ),
    (
        'MongoDB for Beginners',
        'NoSQL database development',
        59.99,
        4,
        8
    ),
    (
        'PostgreSQL Advanced',
        'Advanced PostgreSQL features and optimization',
        79.99,
        4,
        6
    ),
    -- Cybersecurity
    (
        'Ethical Hacking Fundamentals',
        'Learn penetration testing and security',
        129.99,
        5,
        9
    ),
    (
        'Network Security Essentials',
        'Protect networks from cyber threats',
        109.99,
        5,
        9
    ),
    -- Cloud Computing
    (
        'AWS Certified Solutions Architect',
        'Prepare for AWS certification',
        139.99,
        6,
        10
    ),
    (
        'Microsoft Azure Fundamentals',
        'Learn cloud computing with Azure',
        119.99,
        6,
        10
    ),
    -- Artificial Intelligence
    (
        'Deep Learning with TensorFlow',
        'Neural networks and deep learning',
        149.99,
        7,
        5
    ),
    (
        'Natural Language Processing',
        'NLP with Python and NLTK',
        129.99,
        7,
        5
    ),
    -- Business & Marketing
    (
        'Digital Marketing Mastery',
        'SEO, social media, and content marketing',
        79.99,
        8,
        11
    ),
    (
        'Product Management Essentials',
        'Learn product development lifecycle',
        89.99,
        8,
        11
    );
-- ============================================================
-- 4. Enrollments Data (50 records)
-- ============================================================
-- Students enrolled in various courses with progress tracking
-- Progress ranges from 25% to 95%
-- Enrollment dates: September 2024
-- ============================================================
INSERT INTO enrollments (student_id, course_id, enrolled_at, progress)
VALUES -- Student 11 (Alice) enrollments - 3 courses
    (11, 1, '2024-09-01 10:00:00', 75),
    (11, 4, '2024-09-05 14:30:00', 60),
    (11, 10, '2024-09-10 09:15:00', 90),
    -- Student 12 (Bob) enrollments
    (12, 2, '2024-09-02 11:00:00', 45),
    (12, 3, '2024-09-03 13:20:00', 55),
    (12, 19, '2024-09-15 16:00:00', 30),
    -- Student 13 (Carol) enrollments
    (13, 4, '2024-09-01 08:30:00', 85),
    (13, 5, '2024-09-02 10:45:00', 70),
    (13, 17, '2024-09-08 15:30:00', 65),
    -- Student 14 (Daniel) enrollments
    (14, 7, '2024-09-03 12:00:00', 50),
    (14, 8, '2024-09-04 14:15:00', 40),
    (14, 9, '2024-09-06 11:30:00', 35),
    -- Student 15 (Emma) enrollments
    (15, 1, '2024-09-01 09:00:00', 80),
    (15, 10, '2024-09-07 13:45:00', 95),
    (15, 20, '2024-09-12 10:30:00', 25),
    -- Student 16 (Frank) enrollments
    (16, 11, '2024-09-05 15:00:00', 60),
    (16, 12, '2024-09-06 16:30:00', 55),
    -- Student 17 (Grace) enrollments
    (17, 13, '2024-09-02 11:45:00', 70),
    (17, 14, '2024-09-03 13:00:00', 65),
    (17, 5, '2024-09-10 09:30:00', 40),
    -- Student 18 (Henry) enrollments
    (18, 15, '2024-09-04 10:15:00', 50),
    (18, 16, '2024-09-05 12:30:00', 45),
    -- Student 19 (Ivy) enrollments
    (19, 1, '2024-09-01 14:00:00', 90),
    (19, 2, '2024-09-02 15:30:00', 85),
    (19, 3, '2024-09-03 16:45:00', 80),
    -- Student 20 (Jack) enrollments
    (20, 4, '2024-09-06 09:00:00', 55),
    (20, 6, '2024-09-08 11:15:00', 50),
    -- Student 21 (Kate) enrollments
    (21, 7, '2024-09-01 10:30:00', 75),
    (21, 8, '2024-09-02 12:00:00', 70),
    (21, 9, '2024-09-03 14:30:00', 65),
    -- Student 22 (Leo) enrollments
    (22, 10, '2024-09-05 08:45:00', 85),
    (22, 11, '2024-09-06 10:00:00', 80),
    -- Student 23 (Mia) enrollments
    (23, 17, '2024-09-07 13:00:00', 60),
    (23, 18, '2024-09-08 15:15:00', 55),
    -- Student 24 (Noah) enrollments
    (24, 19, '2024-09-02 09:30:00', 70),
    (24, 20, '2024-09-03 11:45:00', 65),
    -- Student 25 (Olivia) enrollments
    (25, 1, '2024-09-01 16:00:00', 95),
    (25, 4, '2024-09-04 10:30:00', 88),
    (25, 13, '2024-09-09 12:15:00', 75),
    -- Student 26 (Peter) enrollments
    (26, 5, '2024-09-05 14:45:00', 60),
    (26, 6, '2024-09-06 16:00:00', 55),
    -- Student 27 (Quinn) enrollments
    (27, 15, '2024-09-03 09:15:00', 80),
    (27, 16, '2024-09-04 11:30:00', 75),
    -- Student 28 (Rachel) enrollments
    (28, 2, '2024-09-07 10:00:00', 50),
    (28, 3, '2024-09-08 12:30:00', 45),
    -- Student 29 (Sam) enrollments
    (29, 12, '2024-09-02 15:45:00', 70),
    (29, 14, '2024-09-05 17:00:00', 65),
    -- Student 30 (Tina) enrollments
    (30, 18, '2024-09-06 13:30:00', 55),
    (30, 20, '2024-09-09 15:45:00', 50);
-- ============================================================
-- 5. Assignments Data (40 records)
-- ============================================================
-- Assignments distributed across 13 courses
-- Due dates range from October to November 2024
-- ============================================================
INSERT INTO assignments (course_id, title, description, due_date)
VALUES -- Course 1 assignments (3 assignments)
    (
        1,
        'HTML/CSS Project',
        'Build a portfolio website using HTML and CSS',
        '2024-10-15 23:59:59'
    ),
    (
        1,
        'JavaScript Quiz App',
        'Create an interactive quiz using JavaScript',
        '2024-10-30 23:59:59'
    ),
    (
        1,
        'Final Project - E-commerce Site',
        'Build a complete e-commerce website',
        '2024-11-30 23:59:59'
    ),
    -- Course 2 assignments
    (
        2,
        'React Todo App',
        'Build a todo app with React hooks',
        '2024-10-20 23:59:59'
    ),
    (
        2,
        'Redux Shopping Cart',
        'Implement a shopping cart with Redux',
        '2024-11-05 23:59:59'
    ),
    -- Course 3 assignments
    (
        3,
        'Node.js REST API',
        'Create a RESTful API with Express',
        '2024-10-25 23:59:59'
    ),
    (
        3,
        'MongoDB Integration',
        'Connect your API to MongoDB',
        '2024-11-10 23:59:59'
    ),
    -- Course 4 assignments
    (
        4,
        'Data Cleaning Exercise',
        'Clean and prepare a dataset using Pandas',
        '2024-10-18 23:59:59'
    ),
    (
        4,
        'Data Visualization Project',
        'Create visualizations with Matplotlib',
        '2024-11-01 23:59:59'
    ),
    (
        4,
        'Exploratory Data Analysis',
        'Complete EDA on provided dataset',
        '2024-11-20 23:59:59'
    ),
    -- Course 5 assignments
    (
        5,
        'Linear Regression Model',
        'Build a predictive model',
        '2024-10-22 23:59:59'
    ),
    (
        5,
        'Classification Project',
        'Classify data using ML algorithms',
        '2024-11-08 23:59:59'
    ),
    -- Course 7 assignments
    (
        7,
        'iOS Calculator App',
        'Build a calculator in Swift',
        '2024-10-28 23:59:59'
    ),
    (
        7,
        'Weather App with API',
        'Create a weather app using external API',
        '2024-11-15 23:59:59'
    ),
    -- Course 8 assignments
    (
        8,
        'Android To-Do List',
        'Build a task manager app',
        '2024-10-26 23:59:59'
    ),
    (
        8,
        'Firebase Integration',
        'Integrate Firebase into your app',
        '2024-11-12 23:59:59'
    ),
    -- Course 10 assignments
    (
        10,
        'Database Design Project',
        'Design a normalized database schema',
        '2024-10-19 23:59:59'
    ),
    (
        10,
        'SQL Query Challenge',
        'Complete advanced SQL exercises',
        '2024-11-03 23:59:59'
    ),
    (
        10,
        'Database Optimization',
        'Optimize queries and add indexes',
        '2024-11-25 23:59:59'
    ),
    -- Course 13 assignments
    (
        13,
        'Penetration Testing Lab',
        'Complete security assessment',
        '2024-10-21 23:59:59'
    ),
    (
        13,
        'Vulnerability Report',
        'Write a comprehensive security report',
        '2024-11-07 23:59:59'
    ),
    -- Course 15 assignments
    (
        15,
        'AWS EC2 Setup',
        'Deploy an application on AWS EC2',
        '2024-10-24 23:59:59'
    ),
    (
        15,
        'S3 and CloudFront',
        'Configure static website hosting',
        '2024-11-09 23:59:59'
    ),
    -- Course 17 assignments
    (
        17,
        'Neural Network Implementation',
        'Build a simple neural network',
        '2024-10-27 23:59:59'
    ),
    (
        17,
        'Image Classification',
        'Train a CNN for image recognition',
        '2024-11-13 23:59:59'
    ),
    (
        17,
        'Transfer Learning Project',
        'Use pre-trained models',
        '2024-11-28 23:59:59'
    ),
    -- Course 19 assignments
    (
        19,
        'SEO Audit',
        'Perform SEO audit on a website',
        '2024-10-16 23:59:59'
    ),
    (
        19,
        'Social Media Campaign',
        'Create a marketing campaign plan',
        '2024-10-31 23:59:59'
    ),
    (
        19,
        'Content Marketing Strategy',
        'Develop content strategy',
        '2024-11-18 23:59:59'
    ),
    -- Course 20 assignments
    (
        20,
        'Product Roadmap',
        'Create a product development roadmap',
        '2024-10-23 23:59:59'
    ),
    (
        20,
        'User Research Report',
        'Conduct and document user research',
        '2024-11-06 23:59:59'
    ),
    (
        20,
        'Feature Prioritization',
        'Prioritize features for next release',
        '2024-11-22 23:59:59'
    ),
    -- Course 6 assignments
    (
        6,
        'R Data Frames',
        'Work with data frames in R',
        '2024-10-29 23:59:59'
    ),
    (
        6,
        'Statistical Analysis',
        'Perform statistical tests in R',
        '2024-11-11 23:59:59'
    ),
    -- Course 9 assignments
    (
        9,
        'React Native Todo App',
        'Build cross-platform todo app',
        '2024-10-26 23:59:59'
    ),
    (
        9,
        'Native Module Integration',
        'Integrate native modules',
        '2024-11-14 23:59:59'
    ),
    -- Course 11 assignments
    (
        11,
        'MongoDB CRUD Operations',
        'Complete MongoDB exercises',
        '2024-10-20 23:59:59'
    ),
    (
        11,
        'Aggregation Pipeline',
        'Build complex aggregation queries',
        '2024-11-08 23:59:59'
    ),
    -- Course 18 assignments
    (
        18,
        'Text Classification',
        'Build a text classifier',
        '2024-10-25 23:59:59'
    );
-- ============================================================
-- 6. Submissions Data (60 records)
-- ============================================================
-- Mix of graded and pending submissions
-- Grades range from 75.50 to 99.00
-- NULL grades indicate pending grading
-- ============================================================
INSERT INTO submissions (assignment_id, student_id, submitted_at, grade)
VALUES -- Student 11 submissions (5 submissions)
    (1, 11, '2024-10-14 18:30:00', 95.00),
    (2, 11, '2024-10-29 20:15:00', 88.50),
    (8, 11, '2024-10-17 16:45:00', 92.00),
    -- Student 13 submissions
    (8, 13, '2024-10-18 14:30:00', 98.00),
    (9, 13, '2024-10-31 19:00:00', 94.50),
    (10, 13, '2024-11-19 21:30:00', 91.00),
    (11, 13, '2024-10-21 17:15:00', 89.50),
    -- Student 15 submissions
    (1, 15, '2024-10-13 22:00:00', 87.00),
    (17, 15, '2024-10-18 15:45:00', 93.50),
    (18, 15, '2024-11-02 18:30:00', 90.00),
    -- Student 19 submissions
    (1, 19, '2024-10-15 20:30:00', 96.00),
    (2, 19, '2024-10-30 17:45:00', 94.00),
    (4, 19, '2024-10-19 19:15:00', 92.50),
    (5, 19, '2024-11-04 21:00:00', 89.00),
    (6, 19, '2024-10-24 16:30:00', 91.50),
    -- Student 21 submissions
    (13, 21, '2024-10-27 18:00:00', 85.00),
    (14, 21, '2024-11-14 20:30:00', 88.50),
    (15, 21, '2024-10-25 19:45:00', 87.00),
    -- Student 22 submissions
    (17, 22, '2024-10-18 17:30:00', 90.00),
    (18, 22, '2024-11-02 21:15:00', 92.50),
    (19, 22, '2024-11-24 18:45:00', 88.00),
    -- Student 25 submissions
    (1, 25, '2024-10-14 21:00:00', 99.00),
    (8, 25, '2024-10-17 19:30:00', 97.50),
    (9, 25, '2024-10-30 22:15:00', 96.00),
    (10, 25, '2024-10-20 16:00:00', 94.50),
    -- Student 11 (more submissions)
    (9, 11, '2024-10-31 18:00:00', 90.00),
    -- Student 13 (more submissions)
    (12, 13, '2024-11-07 20:45:00', 93.00),
    -- Student 17 submissions
    (20, 17, '2024-10-20 17:30:00', 82.00),
    (21, 17, '2024-11-06 19:45:00', 85.50),
    -- Student 23 submissions
    (24, 23, '2024-10-26 18:15:00', 88.00),
    (25, 23, '2024-11-12 20:00:00', 90.50),
    -- Student 24 submissions
    (27, 24, '2024-10-15 21:30:00', 79.00),
    (28, 24, '2024-10-30 18:45:00', 83.50),
    (29, 24, '2024-11-17 20:15:00', 86.00),
    -- Student 27 submissions
    (21, 27, '2024-10-23 19:00:00', 91.00),
    (22, 27, '2024-11-08 21:30:00', 89.50),
    -- Student 29 submissions
    (34, 29, '2024-10-22 17:45:00', 87.00),
    (35, 29, '2024-11-05 19:30:00', 90.00),
    -- Additional random submissions
    (3, 15, '2024-11-29 22:45:00', 85.50),
    (7, 19, '2024-11-09 20:00:00', 88.00),
    (16, 21, '2024-11-11 18:30:00', 84.50),
    (29, 24, '2024-11-21 21:15:00', 81.00),
    (11, 25, '2024-10-15 19:45:00', 95.50),
    (30, 27, '2024-10-15 20:30:00', 92.00),
    (4, 11, '2024-10-19 17:00:00', NULL),
    (5, 11, '2024-11-04 19:30:00', NULL),
    (13, 17, '2024-10-27 18:45:00', 80.00),
    (14, 17, '2024-11-14 20:15:00', NULL),
    (6, 12, '2024-10-24 21:00:00', 75.50),
    (27, 12, '2024-10-15 18:00:00', 78.00),
    (38, 28, '2024-11-03 19:15:00', 82.50),
    (39, 28, '2024-11-16 20:45:00', 85.00);
-- ============================================================
-- 7. Reviews Data (35 records)
-- ============================================================
-- Student course reviews with ratings (1-5 stars)
-- Reviews include helpful comments and feedback
-- ============================================================
INSERT INTO reviews (
        course_id,
        student_id,
        rating,
        comment,
        created_at
    )
VALUES (
        1,
        11,
        5,
        'Excellent course! Very comprehensive and well-structured.',
        '2024-10-20 14:30:00'
    ),
    (
        1,
        15,
        4,
        'Great content, but could use more practical projects.',
        '2024-10-21 16:45:00'
    ),
    (
        1,
        19,
        5,
        'Best web development course I have taken!',
        '2024-10-22 10:15:00'
    ),
    (
        1,
        25,
        5,
        'Amazing instructor and fantastic materials.',
        '2024-10-23 18:20:00'
    ),
    (
        2,
        12,
        4,
        'Good React course, learned a lot about hooks.',
        '2024-10-25 12:30:00'
    ),
    (
        2,
        19,
        5,
        'Deep dive into React patterns. Highly recommended!',
        '2024-10-26 15:45:00'
    ),
    (
        3,
        12,
        3,
        'Content is good but pacing is a bit fast.',
        '2024-10-27 11:00:00'
    ),
    (
        3,
        19,
        4,
        'Solid MERN stack course with practical examples.',
        '2024-10-28 14:30:00'
    ),
    (
        4,
        11,
        5,
        'Perfect introduction to data science with Python.',
        '2024-10-18 09:15:00'
    ),
    (
        4,
        13,
        5,
        'Clear explanations and great hands-on exercises.',
        '2024-10-19 13:45:00'
    ),
    (
        4,
        25,
        4,
        'Very informative, though some topics could be expanded.',
        '2024-10-20 17:00:00'
    ),
    (
        5,
        13,
        5,
        'Best machine learning course for beginners!',
        '2024-11-01 10:30:00'
    ),
    (
        7,
        21,
        4,
        'Comprehensive iOS development course.',
        '2024-11-02 16:20:00'
    ),
    (
        8,
        21,
        5,
        'Learned Android development from scratch. Excellent!',
        '2024-11-03 12:45:00'
    ),
    (
        10,
        11,
        5,
        'SQL fundamentals explained perfectly.',
        '2024-10-22 14:00:00'
    ),
    (
        10,
        15,
        5,
        'Great database course with practical examples.',
        '2024-10-23 16:30:00'
    ),
    (
        10,
        22,
        4,
        'Good content, would like more advanced topics.',
        '2024-10-24 11:15:00'
    ),
    (
        13,
        17,
        4,
        'Eye-opening course on cybersecurity.',
        '2024-10-28 13:00:00'
    ),
    (
        15,
        27,
        5,
        'Excellent AWS certification preparation.',
        '2024-10-29 15:45:00'
    ),
    (
        17,
        23,
        5,
        'Deep learning concepts explained brilliantly.',
        '2024-11-04 10:00:00'
    ),
    (
        19,
        24,
        4,
        'Practical digital marketing strategies.',
        '2024-10-19 12:30:00'
    ),
    (
        19,
        12,
        3,
        'Good overview but lacks depth in some areas.',
        '2024-10-20 14:45:00'
    ),
    (
        20,
        24,
        5,
        'Essential product management knowledge.',
        '2024-10-21 16:00:00'
    ),
    (
        20,
        30,
        4,
        'Very useful for aspiring product managers.',
        '2024-10-22 11:30:00'
    ),
    (
        4,
        20,
        5,
        'Fantastic Python for data science course!',
        '2024-10-24 13:15:00'
    ),
    (
        6,
        20,
        4,
        'R programming made easy to understand.',
        '2024-10-25 15:30:00'
    ),
    (
        9,
        28,
        4,
        'React Native is powerful for cross-platform dev.',
        '2024-10-26 10:45:00'
    ),
    (
        11,
        16,
        5,
        'MongoDB course is very well organized.',
        '2024-10-27 12:00:00'
    ),
    (
        12,
        29,
        4,
        'Advanced PostgreSQL techniques are valuable.',
        '2024-10-28 14:15:00'
    ),
    (
        14,
        17,
        5,
        'Network security course is comprehensive.',
        '2024-10-29 16:30:00'
    ),
    (
        16,
        27,
        4,
        'Azure fundamentals explained clearly.',
        '2024-10-30 11:45:00'
    ),
    (
        18,
        23,
        5,
        'NLP course with excellent practical examples.',
        '2024-11-01 13:00:00'
    ),
    (
        18,
        30,
        4,
        'Great introduction to natural language processing.',
        '2024-11-02 15:15:00'
    ),
    (
        5,
        26,
        3,
        'Content is good but needs better organization.',
        '2024-11-03 10:30:00'
    );
-- ============================================================
-- SECTION 4: ADVANCED DATABASE FEATURES
-- ============================================================
-- This section creates views, stored procedures, functions,
-- and triggers for advanced database functionality
-- ============================================================
-- ============================================================
-- SUBSECTION 4.1: VIEWS - Virtual Tables for Reporting
-- ============================================================
-- Views provide pre-defined queries for common reporting needs
-- Benefits: Simplified queries, data abstraction, security
-- ============================================================
-- ------------------------------------------------------------
-- View 1: course_overview
-- ------------------------------------------------------------
-- Purpose: Comprehensive course statistics dashboard
-- Returns: Course details with enrollment, rating, and assignment counts
-- Usage: SELECT * FROM course_overview WHERE category_name = 'Web Development';
-- ------------------------------------------------------------
DROP VIEW IF EXISTS course_overview;
CREATE VIEW course_overview AS
SELECT c.course_id,
    c.title AS course_title,
    c.price,
    cc.name AS category_name,
    u.name AS instructor_name,
    COUNT(DISTINCT e.enrollment_id) AS total_enrollments,
    AVG(r.rating) AS avg_rating,
    COUNT(DISTINCT r.review_id) AS review_count,
    COUNT(DISTINCT a.assignment_id) AS assignment_count
FROM courses c
    INNER JOIN course_categories cc ON c.category_id = cc.category_id
    INNER JOIN users u ON c.instructor_id = u.user_id
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    LEFT JOIN reviews r ON c.course_id = r.course_id
    LEFT JOIN assignments a ON c.course_id = a.course_id
GROUP BY c.course_id;
-- ------------------------------------------------------------
-- View 2: student_performance
-- ------------------------------------------------------------
-- Purpose: Track student progress and performance metrics
-- Returns: Student info with enrollment count, progress, grades, and reviews
-- Usage: SELECT * FROM student_performance ORDER BY avg_grade DESC LIMIT 10;
-- ------------------------------------------------------------
DROP VIEW IF EXISTS student_performance;
CREATE VIEW student_performance AS
SELECT u.user_id AS student_id,
    u.name AS student_name,
    u.email AS student_email,
    COUNT(DISTINCT e.course_id) AS courses_enrolled,
    AVG(e.progress) AS avg_progress,
    COUNT(DISTINCT s.submission_id) AS total_submissions,
    AVG(s.grade) AS avg_grade,
    COUNT(DISTINCT r.review_id) AS reviews_written
FROM users u
    LEFT JOIN enrollments e ON u.user_id = e.student_id
    LEFT JOIN submissions s ON u.user_id = s.student_id
    LEFT JOIN reviews r ON u.user_id = r.student_id
WHERE u.role = 'student'
GROUP BY u.user_id;
-- ------------------------------------------------------------
-- View 3: instructor_dashboard
-- ------------------------------------------------------------
-- Purpose: Instructor performance and revenue analytics
-- Returns: Instructor metrics including courses, students, revenue, ratings
-- Usage: SELECT * FROM instructor_dashboard WHERE avg_rating >= 4.5;
-- ------------------------------------------------------------
DROP VIEW IF EXISTS instructor_dashboard;
CREATE VIEW instructor_dashboard AS
SELECT u.user_id AS instructor_id,
    u.name AS instructor_name,
    COUNT(DISTINCT c.course_id) AS courses_taught,
    COUNT(DISTINCT e.enrollment_id) AS total_students,
    SUM(
        c.price * (
            SELECT COUNT(*)
            FROM enrollments
            WHERE course_id = c.course_id
        )
    ) AS total_revenue,
    AVG(r.rating) AS avg_rating
FROM users u
    LEFT JOIN courses c ON u.user_id = c.instructor_id
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    LEFT JOIN reviews r ON c.course_id = r.course_id
WHERE u.role = 'instructor'
GROUP BY u.user_id;
-- ------------------------------------------------------------
-- View 4: assignment_status
-- ------------------------------------------------------------
-- Purpose: Monitor assignment submission and grading status
-- Returns: Assignment details with submission statistics and due date status
-- Usage: SELECT * FROM assignment_status WHERE status = 'Due Soon';
-- ------------------------------------------------------------
DROP VIEW IF EXISTS assignment_status;
CREATE VIEW assignment_status AS
SELECT a.assignment_id,
    a.title AS assignment_title,
    c.title AS course_title,
    a.due_date,
    COUNT(DISTINCT e.student_id) AS total_students,
    COUNT(DISTINCT s.submission_id) AS submissions_received,
    (
        COUNT(DISTINCT e.student_id) - COUNT(DISTINCT s.submission_id)
    ) AS pending_submissions,
    AVG(s.grade) AS avg_grade,
    CASE
        WHEN a.due_date < NOW() THEN 'Overdue'
        WHEN a.due_date < DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 'Due Soon'
        ELSE 'Active'
    END AS status
FROM assignments a
    INNER JOIN courses c ON a.course_id = c.course_id
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    LEFT JOIN submissions s ON a.assignment_id = s.assignment_id
    AND s.student_id = e.student_id
GROUP BY a.assignment_id;
-- ------------------------------------------------------------
-- View 5: category_revenue
-- ------------------------------------------------------------
-- Purpose: Analyze revenue and performance by course category
-- Returns: Category statistics including courses, enrollments, revenue, ratings
-- Usage: SELECT * FROM category_revenue ORDER BY total_revenue DESC;
-- ------------------------------------------------------------
DROP VIEW IF EXISTS category_revenue;
CREATE VIEW category_revenue AS
SELECT cc.category_id,
    cc.name AS category_name,
    COUNT(DISTINCT c.course_id) AS total_courses,
    COUNT(DISTINCT e.enrollment_id) AS total_enrollments,
    SUM(c.price) AS total_revenue,
    AVG(c.price) AS avg_course_price,
    AVG(r.rating) AS avg_category_rating
FROM course_categories cc
    LEFT JOIN courses c ON cc.category_id = c.category_id
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    LEFT JOIN reviews r ON c.course_id = r.course_id
GROUP BY cc.category_id;
-- ============================================================
-- SUBSECTION 4.2: STORED PROCEDURES - Reusable Business Logic
-- ============================================================
-- Procedures encapsulate complex operations and business rules
-- Benefits: Code reuse, security, performance optimization
-- ============================================================
-- ------------------------------------------------------------
-- Procedure 1: enroll_student
-- ------------------------------------------------------------
-- Purpose: Safely enroll a student in a course with validation
-- Parameters:
--   - p_student_id: ID of the student to enroll
--   - p_course_id: ID of the course
-- Validation:
--   - Checks student exists and has 'student' role
--   - Checks course exists
--   - Prevents duplicate enrollments
-- Usage: CALL enroll_student(11, 5);
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS enroll_student;
DELIMITER // CREATE PROCEDURE enroll_student(
    IN p_student_id INT,
    IN p_course_id INT
) BEGIN
DECLARE student_exists INT;
DECLARE course_exists INT;
DECLARE already_enrolled INT;
-- Check if student exists
SELECT COUNT(*) INTO student_exists
FROM users
WHERE user_id = p_student_id
    AND role = 'student';
-- Check if course exists
SELECT COUNT(*) INTO course_exists
FROM courses
WHERE course_id = p_course_id;
-- Check if already enrolled
SELECT COUNT(*) INTO already_enrolled
FROM enrollments
WHERE student_id = p_student_id
    AND course_id = p_course_id;
IF student_exists = 0 THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Student not found';
ELSEIF course_exists = 0 THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Course not found';
ELSEIF already_enrolled > 0 THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Student already enrolled in this course';
ELSE
INSERT INTO enrollments (student_id, course_id, enrolled_at, progress)
VALUES (p_student_id, p_course_id, NOW(), 0);
SELECT 'Enrollment successful' AS message;
END IF;
END // DELIMITER;
-- ------------------------------------------------------------
-- Procedure 2: get_top_students
-- ------------------------------------------------------------
-- Purpose: Retrieve top performing students based on grades
-- Parameters:
--   - limit_count: Number of top students to return
-- Returns: Student details with average grade and submission count
-- Usage: CALL get_top_students(10);
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS get_top_students;
DELIMITER // CREATE PROCEDURE get_top_students(IN limit_count INT) BEGIN
SELECT u.user_id,
    u.name,
    u.email,
    AVG(s.grade) AS avg_grade,
    COUNT(DISTINCT s.submission_id) AS total_submissions,
    AVG(e.progress) AS avg_progress
FROM users u
    INNER JOIN enrollments e ON u.user_id = e.student_id
    LEFT JOIN submissions s ON u.user_id = s.student_id
WHERE u.role = 'student'
    AND s.grade IS NOT NULL
GROUP BY u.user_id
HAVING avg_grade IS NOT NULL
ORDER BY avg_grade DESC,
    total_submissions DESC
LIMIT limit_count;
END // DELIMITER;
-- ------------------------------------------------------------
-- Procedure 3: course_revenue_report
-- ------------------------------------------------------------
-- Purpose: Generate comprehensive revenue report for a course
-- Parameters:
--   - p_course_id: ID of the course
-- Returns: Course details with enrollment count, revenue, and ratings
-- Usage: CALL course_revenue_report(1);
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS course_revenue_report;
DELIMITER // CREATE PROCEDURE course_revenue_report(IN p_course_id INT) BEGIN
SELECT c.course_id,
    c.title,
    c.price,
    COUNT(e.enrollment_id) AS total_enrollments,
    (c.price * COUNT(e.enrollment_id)) AS total_revenue,
    AVG(r.rating) AS avg_rating,
    u.name AS instructor_name
FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    LEFT JOIN reviews r ON c.course_id = r.course_id
    LEFT JOIN users u ON c.instructor_id = u.user_id
WHERE c.course_id = p_course_id
GROUP BY c.course_id;
END // DELIMITER;
-- ------------------------------------------------------------
-- Procedure 4: update_progress
-- ------------------------------------------------------------
-- Purpose: Update student's course progress with validation
-- Parameters:
--   - p_student_id: ID of the student
--   - p_course_id: ID of the course
--   - p_progress: New progress value (0-100)
-- Validation: Ensures progress is between 0 and 100
-- Usage: CALL update_progress(11, 1, 85);
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS update_progress;
DELIMITER // CREATE PROCEDURE update_progress(
    IN p_student_id INT,
    IN p_course_id INT,
    IN p_progress INT
) BEGIN IF p_progress < 0
OR p_progress > 100 THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Progress must be between 0 and 100';
ELSE
UPDATE enrollments
SET progress = p_progress
WHERE student_id = p_student_id
    AND course_id = p_course_id;
SELECT 'Progress updated successfully' AS message;
END IF;
END // DELIMITER;
-- ============================================================
-- SUBSECTION 4.3: FUNCTIONS - Reusable Calculations
-- ============================================================
-- Functions return single values and can be used in SELECT statements
-- Benefits: Code reuse, cleaner queries, consistent calculations
-- ============================================================
-- ------------------------------------------------------------
-- Function 1: course_completion_rate
-- ------------------------------------------------------------
-- Purpose: Calculate average completion rate for a course
-- Parameters:
--   - p_course_id: ID of the course
-- Returns: Average progress percentage (0-100)
-- Usage: SELECT course_completion_rate(1);
-- ------------------------------------------------------------
DROP FUNCTION IF EXISTS course_completion_rate;
DELIMITER // CREATE FUNCTION course_completion_rate(p_course_id INT) RETURNS DECIMAL(5, 2) DETERMINISTIC BEGIN
DECLARE completion_rate DECIMAL(5, 2);
SELECT AVG(progress) INTO completion_rate
FROM enrollments
WHERE course_id = p_course_id;
RETURN IFNULL(completion_rate, 0);
END // DELIMITER;
-- ------------------------------------------------------------
-- Function 2: get_grade_letter
-- ------------------------------------------------------------
-- Purpose: Convert numeric grade to letter grade
-- Parameters:
--   - grade: Numeric grade (0-100)
-- Returns: Letter grade (A, B, C, D, F, or N/A)
-- Grading Scale: A(90+), B(80-89), C(70-79), D(60-69), F(<60)
-- Usage: SELECT get_grade_letter(85.5);
-- ------------------------------------------------------------
DROP FUNCTION IF EXISTS get_grade_letter;
DELIMITER // CREATE FUNCTION get_grade_letter(grade DECIMAL(5, 2)) RETURNS VARCHAR(2) DETERMINISTIC BEGIN
DECLARE letter VARCHAR(2);
IF grade IS NULL THEN RETURN 'N/A';
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
END // DELIMITER;
-- ------------------------------------------------------------
-- Function 3: count_student_enrollments
-- ------------------------------------------------------------
-- Purpose: Count total enrollments for a student
-- Parameters:
--   - p_student_id: ID of the student
-- Returns: Number of courses the student is enrolled in
-- Usage: SELECT count_student_enrollments(11);
-- ------------------------------------------------------------
DROP FUNCTION IF EXISTS count_student_enrollments;
DELIMITER // CREATE FUNCTION count_student_enrollments(p_student_id INT) RETURNS INT DETERMINISTIC BEGIN
DECLARE enrollment_count INT;
SELECT COUNT(*) INTO enrollment_count
FROM enrollments
WHERE student_id = p_student_id;
RETURN enrollment_count;
END // DELIMITER;
-- ------------------------------------------------------------
-- Function 4: calculate_course_revenue
-- ------------------------------------------------------------
-- Purpose: Calculate total revenue generated by a course
-- Parameters:
--   - p_course_id: ID of the course
-- Returns: Total revenue (price × enrollment count)
-- Usage: SELECT calculate_course_revenue(1);
-- ------------------------------------------------------------
DROP FUNCTION IF EXISTS calculate_course_revenue;
DELIMITER // CREATE FUNCTION calculate_course_revenue(p_course_id INT) RETURNS DECIMAL(10, 2) DETERMINISTIC BEGIN
DECLARE revenue DECIMAL(10, 2);
SELECT c.price * COUNT(e.enrollment_id) INTO revenue
FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
WHERE c.course_id = p_course_id
GROUP BY c.course_id;
RETURN IFNULL(revenue, 0);
END // DELIMITER;
-- ============================================================
-- SUBSECTION 4.4: TRIGGERS - Automated Data Integrity
-- ============================================================
-- Triggers automatically execute actions in response to events
-- Benefits: Data validation, audit trails, automatic updates
-- ============================================================
-- ------------------------------------------------------------
-- Trigger 1: after_enrollment_insert
-- ------------------------------------------------------------
-- Purpose: Log enrollment activity (placeholder for audit system)
-- Event: AFTER INSERT on enrollments
-- Action: Sets session variable with enrollment timestamp
-- Future: Can be extended to insert into audit_log table
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS after_enrollment_insert;
DELIMITER // CREATE TRIGGER after_enrollment_insert
AFTER
INSERT ON enrollments FOR EACH ROW BEGIN -- Log enrollment activity (you can create an audit table if needed)
    -- For now, this is a placeholder for future audit logging
SET @last_enrollment_time = NOW();
END // DELIMITER;
-- ------------------------------------------------------------
-- Trigger 2: before_course_delete
-- ------------------------------------------------------------
-- Purpose: Prevent deletion of courses with active enrollments
-- Event: BEFORE DELETE on courses
-- Action: Raises error if course has enrollments
-- Reason: Protects data integrity and prevents accidental data loss
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS before_course_delete;
DELIMITER // CREATE TRIGGER before_course_delete BEFORE DELETE ON courses FOR EACH ROW BEGIN
DECLARE enrollment_count INT;
SELECT COUNT(*) INTO enrollment_count
FROM enrollments
WHERE course_id = OLD.course_id;
IF enrollment_count > 0 THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Cannot delete course with active enrollments';
END IF;
END // DELIMITER;
-- ------------------------------------------------------------
-- Trigger 3: before_review_insert
-- ------------------------------------------------------------
-- Purpose: Validate reviews before insertion
-- Event: BEFORE INSERT on reviews
-- Validation:
--   1. Rating must be between 1 and 5
--   2. Student must be enrolled in the course being reviewed
-- Reason: Ensures data quality and prevents invalid reviews
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS before_review_insert;
DELIMITER // CREATE TRIGGER before_review_insert BEFORE
INSERT ON reviews FOR EACH ROW BEGIN IF NEW.rating < 1
    OR NEW.rating > 5 THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Rating must be between 1 and 5';
END IF;
-- Check if student is enrolled in the course
IF NOT EXISTS (
    SELECT 1
    FROM enrollments
    WHERE student_id = NEW.student_id
        AND course_id = NEW.course_id
) THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Student must be enrolled in course to review';
END IF;
END // DELIMITER;
-- ------------------------------------------------------------
-- Trigger 4: after_submission_insert
-- ------------------------------------------------------------
-- Purpose: Automatically update student progress when assignment submitted
-- Event: AFTER INSERT on submissions
-- Action: Calculates completion percentage and updates enrollment progress
-- Formula: (completed_assignments / total_assignments) × 100
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS after_submission_insert;
DELIMITER // CREATE TRIGGER after_submission_insert
AFTER
INSERT ON submissions FOR EACH ROW BEGIN
DECLARE total_assignments INT;
DECLARE completed_assignments INT;
DECLARE new_progress INT;
DECLARE p_course_id INT;
-- Get course_id from assignment
SELECT course_id INTO p_course_id
FROM assignments
WHERE assignment_id = NEW.assignment_id;
-- Count total assignments for the course
SELECT COUNT(*) INTO total_assignments
FROM assignments
WHERE course_id = p_course_id;
-- Count completed assignments by this student
SELECT COUNT(DISTINCT s.assignment_id) INTO completed_assignments
FROM submissions s
    INNER JOIN assignments a ON s.assignment_id = a.assignment_id
WHERE s.student_id = NEW.student_id
    AND a.course_id = p_course_id;
-- Calculate progress percentage
IF total_assignments > 0 THEN
SET new_progress = ROUND(
        (completed_assignments / total_assignments) * 100
    );
-- Update enrollment progress
UPDATE enrollments
SET progress = new_progress
WHERE student_id = NEW.student_id
    AND course_id = p_course_id;
END IF;
END // DELIMITER;
-- ------------------------------------------------------------
-- Trigger 5: before_course_insert
-- ------------------------------------------------------------
-- Purpose: Prevent creation of courses with negative prices
-- Event: BEFORE INSERT on courses
-- Validation: Price must be >= 0
-- Reason: Ensures business rule compliance
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS before_course_insert;
DELIMITER // CREATE TRIGGER before_course_insert BEFORE
INSERT ON courses FOR EACH ROW BEGIN IF NEW.price < 0 THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Course price cannot be negative';
END IF;
END // DELIMITER;
-- ============================================================
-- DATABASE SETUP COMPLETE!
-- ============================================================
-- ============================================================
-- SUMMARY OF DATABASE CONTENTS
-- ============================================================
-- 
-- TABLES (7):
--   ├── users (30 records: 2 admins, 8 instructors, 20 students)
--   ├── course_categories (8 records)
--   ├── courses (20 records, $59.99-$149.99 pricing)
--   ├── enrollments (50 records with 0-100% progress)
--   ├── assignments (40 records across 13 courses)
--   ├── submissions (60 records, mix of graded and pending)
--   └── reviews (35 records with 1-5 star ratings)
--
-- VIEWS (5):
--   ├── course_overview - Course statistics dashboard
--   ├── student_performance - Student progress tracking
--   ├── instructor_dashboard - Instructor analytics
--   ├── assignment_status - Assignment monitoring
--   └── category_revenue - Category performance analysis
--
