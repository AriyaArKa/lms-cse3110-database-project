# Learning Management System (LMS) - Database Project

[![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A comprehensive Learning Management System database project built with PHP and MySQL, demonstrating advanced database concepts including relational design, stored procedures, triggers, views, and functions. This project showcases a complete educational platform backend with course management, student enrollment, assignments, submissions, and analytics.

## 📋 Table of Contents

* [Features](#-features)
* [Database Schema](#-database-schema)
* [Technology Stack](#-technology-stack)
* [Prerequisites](#-prerequisites)
* [Installation](#-installation)
* [Project Structure](#-project-structure)
* [Database Objects](#-database-objects)
* [Usage Examples](#-usage-examples)
* [Screenshots](#-screenshots)
* [Advanced SQL Features](#-advanced-sql-features)
* [API Endpoints](#-api-endpoints)
* [Security Features](#-security-features)
* [Contributing](#-contributing)
* [License](#-license)
* [Authors](#-authors)

## ✨ Features

### Core Functionality

* **User Management**: Support for students, instructors, and administrators with role-based access
* **Course Management**: Create, edit, and organize courses with categories and pricing
* **Enrollment System**: Student enrollment tracking with progress monitoring
* **Assignment Management**: Create assignments with due dates and grade tracking
* **Submission Tracking**: Student assignment submissions with grading functionality
* **Review System**: Course reviews and ratings (1-5 stars) from enrolled students

### Advanced Features

* **Analytics Dashboard**: Real-time statistics and performance metrics
* **Revenue Tracking**: Comprehensive financial analytics by course and category
* **Progress Monitoring**: Automatic progress calculation based on assignment completion
* **Top Performers**: Leaderboards for students and instructors
* **SQL Query Visualization**: Interactive SQL query display with tooltips
* **Responsive Design**: Mobile-first Bootstrap 5 interface

### Database Features

* **5 Database Views**: Pre-built queries for reporting and analytics
* **4 Stored Procedures**: Reusable business logic for common operations
* **4 Functions**: Custom calculations for grades, revenue, and completion rates
* **5 Triggers**: Automated data validation and integrity enforcement
* **Sample Data**: 30 users, 20 courses, 50 enrollments, and more

## 🗄️ Database Schema

### Entity Relationship Diagram

```
┌──────────────┐         ┌──────────────────┐         ┌──────────────┐
│    users     │         │  course_categories│         │   courses    │
├──────────────┤         ├──────────────────┤         ├──────────────┤
│ user_id (PK) │         │ category_id (PK) │         │ course_id (PK)│
│ name         │         │ name             │◄───────┤│ title        │
│ email        │         └──────────────────┘         │ description  │
│ password     │                                       │ price        │
│ role         │◄─────────────────────────────────────┤│ category_id  │
│ created_at   │                                       │ instructor_id│
└──────────────┘                                       │ created_at   │
      │                                                └──────────────┘
      │                                                      │
      │                                                      │
      ▼                                                      ▼
┌──────────────┐                                     ┌──────────────┐
│ enrollments  │                                     │ assignments  │
├──────────────┤                                     ├──────────────┤
│enrollment_id │                                     │assignment_id │
│ student_id   │                                     │ course_id    │
│ course_id    │                                     │ title        │
│ enrolled_at  │                                     │ description  │
│ progress     │                                     │ due_date     │
└──────────────┘                                     └──────────────┘
      │                                                      │
      │                                                      │
      ▼                                                      ▼
┌──────────────┐                                     ┌──────────────┐
│   reviews    │                                     │ submissions  │
├──────────────┤                                     ├──────────────┤
│ review_id    │                                     │submission_id │
│ course_id    │                                     │assignment_id │
│ student_id   │                                     │ student_id   │
│ rating       │                                     │ submitted_at │
│ comment      │                                     │ grade        │
│ created_at   │                                     └──────────────┘
└──────────────┘
```

### Database Tables

| Table | Description | Records |
| ----- | ----------- | ------- |
| `users` | System users (students, instructors, admins) | 30 |
| `course_categories` | Course organization categories | 8 |
| `courses` | Course information and metadata | 20 |
| `enrollments` | Student course enrollments | 50 |
| `assignments` | Course assignments and homework | 40 |
| `submissions` | Student assignment submissions | 60 |
| `reviews` | Course reviews and ratings | 35 |

## 🛠️ Technology Stack

### Backend

* **PHP 7.4+**: Server-side scripting
* **PDO**: Secure database connections
* **MySQL 8.0+**: Relational database management

### Frontend

* **HTML5**: Semantic markup
* **CSS3**: Modern styling with gradients and animations
* **Bootstrap 5.3**: Responsive UI framework
* **Font Awesome 6.4**: Icon library
* **JavaScript**: Client-side interactivity

### Development Tools

* **XAMPP**: Local development environment
* **MySQL Workbench**: Database design and management (recommended)
* **VS Code**: Code editor (recommended)

## 📦 Prerequisites

Before you begin, ensure you have the following installed:

* **XAMPP** (or equivalent LAMP/WAMP stack)
    * PHP 7.4 or higher
    * MySQL 8.0 or higher
    * Apache Web Server
* **Web Browser** (Chrome, Firefox, Edge, Safari)
* **Git** (optional, for cloning the repository)

## 🚀 Installation

### Method 1: Automated Setup (Recommended)

1. **Clone or Download the Repository**

``` bash
cd c:\xampp\htdocs
git clone https://github.com/AriyaArKa/lms-cse3110-database-project.git lms_db
```

2. **Start XAMPP**
    * Launch XAMPP Control Panel
    * Start **Apache** and **MySQL** services
3. **Access the Setup Page**

```
http://localhost/setup_database.php
```

This will automatically:
    * Create the `university_db` database
    * Generate all 7 tables
    * Insert sample data
    * Create views, procedures, functions, and triggers
4. **Access the Application**

```
http://localhost/
```

### Method 2: Manual Setup

1. **Create Database**

``` sql
CREATE DATABASE university_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE university_db;
```

2. **Import SQL Schema**
    * Navigate to phpMyAdmin: `http://localhost/phpmyadmin`
    * Select `university_db`
    * Click "Import" tab
    * Choose `sql/setup.sql`
    * Click "Go"
3. **Verify Installation**

``` sql
SHOW TABLES;
-- Should display 7 tables
```

4. **Access Application**

```
http://localhost/
```

### Configuration

Database configuration is located in `config/database.php`:

``` php
private $host = "localhost";
private $db_name = "university_db";
private $username = "root";
private $password = "";
```

Modify these values if your MySQL credentials differ.

## 📁 Project Structure

```

│
├── config/
│   └── database.php              # Database connection class
│
├── includes/
│   └── sql_display.php           # SQL query display utilities
│
├── sql/
│   ├── setup.sql                 # Complete database setup script
│   └── cleanup.sql               # Database reset script
│
├── index.php                     # Dashboard with statistics
├── users.php                     # User management interface
├── courses.php                   # Course listing and management
├── categories.php                # Category management
├── assignments.php               # Assignment tracking
├── enrollments.php               # Enrollment management
├── reviews.php                   # Course reviews
├── submissions.php               # Assignment submissions
│
├── add_user.php                  # Create new user
├── edit_user.php                 # Modify user details
├── delete_user.php               # Remove user
│
├── add_course.php                # Create new course
├── edit_course.php               # Modify course details
├── delete_course.php             # Remove course
│
├── add_enrollment.php            # Enroll student
├── edit_enrollment.php           # Update enrollment
├── delete_enrollment.php         # Remove enrollment
│
├── add_assignment.php            # Create assignment
├── view_assignment.php           # Assignment details
├── view_course.php               # Course details
├── view_category.php             # Category details
├── view_enrollment.php           # Enrollment details
├── view_user.php                 # User profile
│
├── sql_operations.php            # SQL demonstration page
├── setup_database.php            # Automated setup script
├── complete_setup.php            # Setup completion page
│
└── README.md                     # This file
```

## 🗃️ Database Objects

### Views (5)

| View | Purpose |
| ---- | ------- |
| `course_overview` | Comprehensive course statistics with enrollments, ratings, and assignments |
| `student_performance` | Student progress tracking with grades and completion rates |
| `instructor_dashboard` | Instructor metrics including revenue and ratings |
| `assignment_status` | Assignment submission tracking and grading status |
| `category_revenue` | Revenue and performance analytics by category |

### Stored Procedures (4)

| Procedure | Parameters | Description |
| --------- | ---------- | ----------- |
| `enroll_student` | student\_id, course\_id | Safely enroll a student with validation |
| `get_top_students` | limit\_count | Retrieve top-performing students by grades |
| `course_revenue_report` | course\_id | Generate comprehensive revenue report |
| `update_progress` | student\_id, course\_id, progress | Update student course progress |

### Functions (4)

| Function | Returns | Description |
| -------- | ------- | ----------- |
| `course_completion_rate` | DECIMAL(5,2) | Average completion percentage for a course |
| `get_grade_letter` | VARCHAR(2) | Convert numeric grade to letter grade (A-F) |
| `count_student_enrollments` | INT | Total enrollment count for a student |
| `calculate_course_revenue` | DECIMAL(10,2) | Total revenue generated by a course |

### Triggers (5)

| Trigger | Event | Purpose |
| ------- | ----- | ------- |
| `after_enrollment_insert` | AFTER INSERT on enrollments | Log enrollment activity |
| `before_course_delete` | BEFORE DELETE on courses | Prevent deletion of courses with enrollments |
| `before_review_insert` | BEFORE INSERT on reviews | Validate ratings and enrollment status |
| `after_submission_insert` | AFTER INSERT on submissions | Auto-update course progress |
| `before_course_insert` | BEFORE INSERT on courses | Validate course price is non-negative |

## 💡 Usage Examples

### SQL Query Examples

#### 1\. Enroll a Student in a Course

``` sql
CALL enroll_student(11, 5);
```

#### 2\. Get Top 10 Students

``` sql
CALL get_top_students(10);
```

#### 3\. Calculate Course Completion Rate

``` sql
SELECT
    course_id,
    title,
    course_completion_rate(course_id) AS completion_percentage
FROM courses;
```

#### 4\. Get Grade Letter for Numeric Grade

``` sql
SELECT
    student_id,
    assignment_id,
    grade,
    get_grade_letter(grade) AS letter_grade
FROM submissions
WHERE grade IS NOT NULL;
```

#### 5\. View Course Statistics

``` sql
SELECT * FROM course_overview WHERE avg_rating >= 4.5;
```

#### 6\. View Student Performance

``` sql
SELECT * FROM student_performance ORDER BY avg_grade DESC LIMIT 10;
```

#### 7\. Calculate Course Revenue

``` sql
SELECT
    course_id,
    title,
    calculate_course_revenue(course_id) AS total_revenue
FROM courses
ORDER BY total_revenue DESC;
```

### PHP Usage Examples

#### Connecting to Database

``` php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();
```

#### Fetching Course Data

``` php
$sql = "SELECT * FROM course_overview ORDER BY avg_rating DESC LIMIT 5";
$stmt = $conn->query($sql);
$top_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($top_courses as $course) {
    echo $course['course_title'] . " - Rating: " . $course['avg_rating'];
}
```

#### Calling Stored Procedure

``` php
$stmt = $conn->prepare("CALL get_top_students(?)");
$stmt->execute([10]);
$top_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

## 📸 Screenshots

### Dashboard

The main dashboard displays real-time statistics including:

* Total students, instructors, and admins
* Active courses and enrollments
* Average course ratings
* Total revenue
* Recent users
* Top-rated courses
* Category statistics

### SQL Operations Demo

Interactive SQL query demonstrations with:

* SELECT, INSERT, UPDATE, DELETE operations
* JOIN operations (INNER, LEFT, RIGHT)
* Aggregate functions (COUNT, AVG, SUM, MAX, MIN)
* Subqueries and UNION operations
* Stored procedure calls
* Function usage examples

## 🔒 Security Features

### Password Security

* **Bcrypt Hashing**: All passwords are hashed using bcrypt algorithm
* **Salt**: Automatic salt generation for each password
* Default password for all sample users: `password`

### SQL Injection Prevention

* **PDO Prepared Statements**: All database queries use parameterized statements
* **Input Validation**: Server-side validation on all inputs
* **Error Handling**: Secure error messages without exposing sensitive information

### Access Control

* **Role-Based Access**: Three user roles (admin, instructor, student)
* **Foreign Key Constraints**: Data integrity enforcement at database level
* **Trigger Validation**: Business rules enforced through triggers

## 🧪 Testing

### Sample User Accounts

| Role | Email | Password |
| ---- | ----- | -------- |
| Admin | admin@lms.com | password |
| Instructor | emily.johnson@lms.com | password |
| Student | alice.anderson@student.com | password |

### Test Scenarios

1. **Enrollment Process**
    * Enroll a student in a course
    * Verify enrollment appears in student's courses
    * Check progress initialization
2. **Assignment Submission**
    * Submit an assignment
    * Verify automatic progress update
    * Check grade calculation
3. **Review System**
    * Attempt review without enrollment (should fail)
    * Enroll and submit valid review
    * Verify average rating update

## 🔧 Advanced SQL Features

### Complex Queries

#### Revenue Analysis by Category

``` sql
SELECT
    cc.name AS category,
    COUNT(DISTINCT c.course_id) AS total_courses,
    COUNT(DISTINCT e.enrollment_id) AS total_enrollments,
    SUM(c.price) AS total_revenue,
    AVG(r.rating) AS avg_rating
FROM course_categories cc
LEFT JOIN courses c ON cc.category_id = c.category_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
LEFT JOIN reviews r ON c.course_id = r.course_id
GROUP BY cc.category_id
ORDER BY total_revenue DESC;
```

#### Student Performance Report

``` sql
SELECT
    u.name AS student_name,
    COUNT(DISTINCT e.course_id) AS courses_enrolled,
    AVG(e.progress) AS avg_progress,
    AVG(s.grade) AS avg_grade,
    get_grade_letter(AVG(s.grade)) AS letter_grade
FROM users u
JOIN enrollments e ON u.user_id = e.student_id
LEFT JOIN submissions s ON u.user_id = s.student_id
WHERE u.role = 'student'
GROUP BY u.user_id
HAVING avg_grade IS NOT NULL
ORDER BY avg_grade DESC;
```

#### Instructor Earnings Report

``` sql
SELECT
    u.name AS instructor_name,
    COUNT(DISTINCT c.course_id) AS courses_taught,
    SUM(calculate_course_revenue(c.course_id)) AS total_earnings,
    AVG(r.rating) AS avg_rating
FROM users u
JOIN courses c ON u.user_id = c.instructor_id
LEFT JOIN reviews r ON c.course_id = r.course_id
WHERE u.role = 'instructor'
GROUP BY u.user_id
ORDER BY total_earnings DESC;
```

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards

* Follow PSR-12 coding standards for PHP
* Use meaningful variable and function names
* Comment complex logic
* Write SQL queries in uppercase for keywords
* Test all changes before submitting PR

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

**AriyaArKa**

* GitHub: [@AriyaArKa](https://github.com/AriyaArKa)
* Repository: [lms-cse3110-database-project](https://github.com/AriyaArKa/lms-cse3110-database-project)

## 🙏 Acknowledgments

* Bootstrap 5 for the responsive UI framework
* Font Awesome for the icon library
* MySQL documentation for advanced SQL features
* PHP documentation for PDO best practices
* The open-source community for inspiration

## 📞 Support

For support, please:

* Open an issue on GitHub
* Check the [Wiki](https://github.com/AriyaArKa/lms-cse3110-database-project/wiki) for documentation
* Review existing issues for solutions

## 🗺️ Roadmap

Future enhancements planned:

* [ ] User authentication system with sessions
* [ ] File upload for assignment submissions
* [ ] Email notifications for deadlines
* [ ] Advanced analytics dashboard with charts
* [ ] REST API for mobile app integration
* [ ] Real-time chat system between students and instructors
* [ ] Calendar integration for due dates
* [ ] Bulk operations for admin users
* [ ] Export functionality (CSV, PDF)
* [ ] Multi-language support

## 📊 Project Statistics

* **Total Lines of Code**: \~5,000+
* **Database Tables**: 7
* **Views**: 5
* **Stored Procedures**: 4
* **Functions**: 4
* **Triggers**: 5
* **Sample Records**: 243
* **PHP Files**: 30+

- - -

**Note**: This project is designed for educational purposes to demonstrate advanced database concepts and web development skills. It showcases proficiency in SQL, PHP, database design, and full-stack development.

**Project Category**: Database Systems \| Web Development \| Learning Management System

**Academic**: CSE 3110 - Database Project

- - -

Made with ❤️ by AriyaArKa