# 📚 LMS Database Project - SQL Features Demonstration

## 🎯 Project Overview

This Learning Management System (LMS) is a comprehensive database project designed to demonstrate **ALL major SQL and MySQL features** including basic operations, advanced queries, views, stored procedures, functions, and triggers.

## ✨ What's New - SQL Demonstration Features

### 🔍 **On Every Page: Collapsible SQL Display**

Every page now includes a collapsible section showing the **exact SQL query** used to generate the data on that page. Click the "SQL Query" button to view and copy the queries.

### 📊 **New SQL Operations Demo Page** (`sql_operations.php`)

A comprehensive demonstration page showcasing **ALL SQL features** with live examples and results:

1. **Basic SQL Operations**

   - SELECT with WHERE conditions
   - ORDER BY sorting
   - LIKE pattern matching
   - BETWEEN range queries
   - LIMIT result sets

2. **JOIN Operations**

   - INNER JOIN
   - LEFT JOIN
   - Multiple table joins (4+ tables)

3. **Aggregate Functions**

   - COUNT - counting records
   - AVG - calculating averages
   - SUM - totaling values
   - MAX/MIN - finding extremes

4. **GROUP BY & HAVING**

   - Grouping data
   - Filtering groups with HAVING clause
   - Multiple column grouping

5. **Subqueries**

   - Subqueries in WHERE clause
   - Correlated subqueries
   - Derived tables (subqueries in FROM)

6. **Set Operations**

   - UNION (distinct results)
   - UNION ALL (with duplicates)

7. **Views** (5 Pre-created)

   - `course_overview` - Course statistics
   - `student_performance` - Student metrics
   - `instructor_dashboard` - Instructor analytics
   - `assignment_status` - Assignment tracking
   - `category_revenue` - Financial analysis

8. **Stored Procedures** (4 Pre-created)

   - `enroll_student` - Enroll student in course
   - `get_top_students` - Get top performers
   - `course_revenue_report` - Revenue analysis
   - `update_progress` - Update student progress

9. **Functions** (4 Pre-created)

   - `course_completion_rate` - Calculate completion %
   - `get_grade_letter` - Convert numeric to letter grade
   - `count_student_enrollments` - Count enrollments
   - `calculate_course_revenue` - Calculate revenue

10. **Triggers** (5 Pre-created)

    - `after_enrollment_insert` - Log enrollment activity
    - `before_course_delete` - Prevent deletion with enrollments
    - `before_review_insert` - Validate ratings and enrollment
    - `after_submission_insert` - Auto-update course progress
    - `before_course_insert` - Prevent negative prices

11. **Advanced Features**
    - CASE statements for conditional logic
    - EXISTS operator for existence checks
    - DISTINCT for unique values
    - Date functions (DATEDIFF, DATE_ADD, etc.)
    - String functions (CONCAT, UPPER, SUBSTRING, etc.)

## 🗂️ Project Structure

```
lms_db/
├── index.php                  # Dashboard with SQL display
├── sql_operations.php         # NEW: Complete SQL demo page
├── users.php                  # User management + SQL
├── courses.php                # Course catalog + SQL
├── categories.php             # Categories + SQL
├── assignments.php            # Assignments + SQL
├── enrollments.php            # Enrollments + SQL
├── submissions.php            # Submissions + SQL
├── reviews.php                # Reviews + SQL
├── add_assignment.php         # NEW: Create assignments
├── add_user.php               # Create users
├── add_course.php             # Create courses
├── add_enrollment.php         # Enroll students
├── includes/
│   └── sql_display.php        # NEW: SQL display component
├── config/
│   └── database.php           # Database connection
└── sql/
    └── setup.sql              # UPDATED: With views, procedures, functions, triggers
```

## 🚀 Quick Start

1. **Install XAMPP** (if not already installed)

   - Download from https://www.apachefriends.org/
   - Start Apache and MySQL services

2. **Place Project Files**

   - Copy `lms_db` folder to `C:\xampp\htdocs\`

3. **Access the Application**

   - Open browser: `http://localhost/lms_db/`
   - Database will auto-create on first visit

4. **View SQL Demonstrations**
   - Click "SQL Demo" in the navigation menu
   - Or visit: `http://localhost/lms_db/sql_operations.php`

## 📋 How to Show SQL Features to Your Teacher

### Method 1: SQL Operations Demo Page (Recommended)

1. Navigate to `http://localhost/lms_db/sql_operations.php`
2. Use the **Table of Contents** to jump to specific SQL features
3. Each section shows:
   - **Operation header** with description
   - **Badge tags** indicating SQL features used
   - **Collapsible SQL code** (click to view/copy)
   - **Live results** in table format

### Method 2: Page-by-Page Demonstration

Visit each page and show the SQL used:

1. **Dashboard** (`index.php`) - Aggregate functions, JOINs
2. **Users** (`users.php`) - WHERE, ORDER BY, GROUP BY
3. **Courses** (`courses.php`) - Multiple JOINs, GROUP BY, HAVING
4. **Categories** (`categories.php`) - LEFT JOIN, Aggregates
5. **Assignments** (`assignments.php`) - JOINs, COUNT, AVG
6. **Enrollments** (`enrollments.php`) - Multiple JOINs
7. **Submissions** (`submissions.php`) - JOINs, grading logic
8. **Reviews** (`reviews.php`) - JOINs, CASE statements

### Method 3: Quick Feature Checklist

Print or show this checklist:

#### ✅ Basic Operations

- [x] SELECT, FROM, WHERE
- [x] ORDER BY (ASC/DESC)
- [x] LIMIT
- [x] LIKE pattern matching
- [x] BETWEEN ranges
- [x] AND, OR conditions

#### ✅ Joins & Relationships

- [x] INNER JOIN
- [x] LEFT JOIN
- [x] Multiple table joins (4+ tables)
- [x] Table aliases

#### ✅ Aggregate Functions

- [x] COUNT(\*)
- [x] AVG()
- [x] SUM()
- [x] MAX()
- [x] MIN()
- [x] GROUP BY
- [x] HAVING

#### ✅ Advanced Queries

- [x] Subqueries in WHERE
- [x] Subqueries in SELECT
- [x] Subqueries in FROM (derived tables)
- [x] Correlated subqueries

#### ✅ Set Operations

- [x] UNION
- [x] UNION ALL

#### ✅ Database Objects

- [x] 5 Views
- [x] 4 Stored Procedures
- [x] 4 Functions
- [x] 5 Triggers

#### ✅ Advanced Features

- [x] CASE statements
- [x] EXISTS operator
- [x] DISTINCT
- [x] Date functions (NOW, DATEDIFF, DATE_ADD)
- [x] String functions (CONCAT, UPPER, SUBSTRING)
- [x] Nested SELECT statements

#### ✅ Database Design

- [x] Primary Keys (AUTO_INCREMENT)
- [x] Foreign Keys
- [x] ON DELETE CASCADE
- [x] ON UPDATE CASCADE
- [x] UNIQUE constraints
- [x] NOT NULL constraints
- [x] DEFAULT values
- [x] ENUM types
- [x] CHECK constraints
- [x] DECIMAL types

#### ✅ CRUD Operations

- [x] INSERT (Create)
- [x] SELECT (Read)
- [x] UPDATE (Update)
- [x] DELETE (Delete)

## 🎓 SQL Features Explained

### Views

Views are saved queries that act as virtual tables:

```sql
-- Example: Course Overview View
SELECT * FROM course_overview;
```

### Stored Procedures

Procedures are reusable database operations:

```sql
-- Example: Enroll a student
CALL enroll_student(11, 1);
```

### Functions

Functions return calculated values:

```sql
-- Example: Get grade letter
SELECT get_grade_letter(85.5); -- Returns 'B'
```

### Triggers

Triggers automatically execute on events:

- **Before INSERT**: Validate data before insertion
- **After INSERT**: Update related data after insertion
- **Before DELETE**: Prevent deletion under conditions

## 📱 Navigation

Every page has consistent navigation with:

- Dashboard
- Users
- Courses
- Categories
- Assignments
- Enrollments
- **SQL Demo** ← Click here for full demonstration

## 🎨 Features of SQL Display Component

On every page, you'll see:

1. **SQL Toggle Button** - Click to show/hide SQL code
2. **Syntax Highlighting** - Keywords in blue
3. **Copy Button** - Copy SQL to clipboard with one click
4. **Dark Theme** - Easy-to-read code display
5. **Result Tables** - See the query results below the SQL

## 📊 Sample Data

The database includes:

- **30 Users** (2 admins, 8 instructors, 20 students)
- **8 Categories** (Web Dev, Data Science, AI, etc.)
- **20 Courses** ($59.99 - $149.99)
- **50 Enrollments** with progress tracking
- **40 Assignments** across multiple courses
- **60 Submissions** (mix of graded and pending)
- **35 Reviews** with 1-5 star ratings

## 🔧 Troubleshooting

### Database Not Creating?

- Ensure MySQL is running in XAMPP
- Check `config/database.php` for correct credentials
- Default: username=`root`, password=`(empty)`

### SQL Display Not Showing?

- Check that `includes/sql_display.php` exists
- Ensure `require_once 'includes/sql_display.php';` is at the top of each PHP file

### Views/Procedures Not Working?

- Re-run `sql/setup.sql` in phpMyAdmin
- Or delete database and refresh any page (auto-recreates)

## 💡 Tips for Presentation

1. **Start with SQL Demo Page** - Shows everything at once
2. **Use Table of Contents** - Jump to specific features quickly
3. **Click SQL buttons** - Show the actual queries being used
4. **Explain each section** - Use the descriptions provided
5. **Show results** - Demonstrate queries return real data
6. **Highlight badges** - Point out which SQL features are used

## 📖 Additional Resources

### To Test Queries Directly:

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select `university_db` database
3. Copy any SQL from the demo page
4. Run it in the SQL tab

### To Modify Data:

- Use the "Add" pages (Add User, Add Course, etc.)
- Or write INSERT/UPDATE queries in phpMyAdmin

### To See Database Structure:

1. phpMyAdmin → `university_db`
2. Click "Structure" tab
3. View tables, columns, relationships

## 🎯 Project Highlights

✨ **Modern UI** - Bootstrap 5, gradient themes, responsive design
✨ **Complete SQL Coverage** - Every major SQL feature demonstrated
✨ **Interactive** - Collapsible SQL on every page
✨ **Educational** - Clear labels, descriptions, and examples
✨ **Production-Ready** - Prepared statements, error handling
✨ **Well-Documented** - Comprehensive README and inline comments

## 📝 Grading Checklist

When presenting to your teacher, you can show:

1. ✅ **Basic SQL**: SELECT, WHERE, ORDER BY, LIMIT → `index.php`
2. ✅ **JOINs**: INNER, LEFT, multiple tables → `courses.php`
3. ✅ **Aggregates**: COUNT, AVG, SUM, MAX, MIN → `sql_operations.php`
4. ✅ **GROUP BY/HAVING**: Grouped statistics → `users.php`
5. ✅ **Subqueries**: Nested SELECTs → `sql_operations.php`
6. ✅ **Set Operations**: UNION → `sql_operations.php`
7. ✅ **Views**: 5 pre-created views → `sql_operations.php`
8. ✅ **Stored Procedures**: 4 procedures → `sql_operations.php`
9. ✅ **Functions**: 4 custom functions → `sql_operations.php`
10. ✅ **Triggers**: 5 triggers → `sql_operations.php`
11. ✅ **Advanced**: CASE, EXISTS, DISTINCT → `sql_operations.php`
12. ✅ **Constraints**: FK, UNIQUE, CHECK → `sql/setup.sql`

## 🌟 Conclusion

This project demonstrates a **complete understanding** of:

- Database design and normalization
- SQL query construction
- Advanced database features
- Modern web development practices
- Security best practices

Perfect for demonstrating database knowledge to teachers and employers!

---

**Created**: 2024
**Database**: MySQL/MariaDB
**Framework**: PHP with PDO
**UI**: Bootstrap 5
**Purpose**: Educational SQL Feature Demonstration

Good luck with your presentation! 🎓
