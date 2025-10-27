# 🎯 Quick Reference - SQL Features by Page

## Main Pages & SQL Operations

### 1. **SQL Operations Demo** (`sql_operations.php`) ⭐ START HERE

**The complete SQL showcase - shows ALL features in one place**

| Section          | SQL Features                                  | Example Use                                   |
| ---------------- | --------------------------------------------- | --------------------------------------------- |
| Basic Operations | SELECT, WHERE, ORDER BY, LIMIT, LIKE, BETWEEN | Filter students, search courses               |
| JOINs            | INNER JOIN, LEFT JOIN, 4+ table joins         | Combine courses with categories & instructors |
| Aggregates       | COUNT, AVG, SUM, MAX, MIN                     | Calculate totals, averages, statistics        |
| Grouping         | GROUP BY, HAVING                              | Group by category, filter groups              |
| Subqueries       | WHERE, SELECT, FROM subqueries                | Find expensive courses, nested queries        |
| Set Operations   | UNION, UNION ALL                              | Combine instructors and reviewers             |
| Views            | 5 pre-created views                           | Virtual tables for common queries             |
| Procedures       | 4 stored procedures                           | Enroll students, get top performers           |
| Functions        | 4 custom functions                            | Grade conversion, revenue calculation         |
| Triggers         | 5 triggers                                    | Auto-update progress, validate data           |
| Advanced         | CASE, EXISTS, DISTINCT, Date/String functions | Categorize performance, check existence       |

---

### 2. **Dashboard** (`index.php`)

**SQL Used:**

- Aggregate functions (COUNT, AVG, SUM)
- Multiple queries for statistics
- INNER JOIN for recent users
- LEFT JOIN for top courses with ratings
- GROUP BY for category statistics

**Key Queries:**

```sql
-- Recent Users
SELECT user_id, name, email, role, created_at
FROM users ORDER BY created_at DESC LIMIT 6

-- Top Rated Courses
SELECT c.course_id, c.title, AVG(r.rating) as avg_rating,
       COUNT(e.enrollment_id) as total_enrollments
FROM courses c
INNER JOIN reviews r ON c.course_id = r.course_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
GROUP BY c.course_id HAVING avg_rating IS NOT NULL
ORDER BY avg_rating DESC
```

---

### 3. **Users** (`users.php`)

**SQL Used:**

- SELECT with WHERE conditions
- Dynamic filtering by role
- Pattern matching with LIKE
- GROUP BY for role counts

**Key Features:**

- Filter by role (student/instructor/admin)
- Search by name or email
- Count users by role

---

### 4. **Courses** (`courses.php`)

**SQL Used:**

- Multiple INNER JOINs (4 tables)
- LEFT JOINs for optional data
- GROUP BY with aggregates
- HAVING clause for filtering

**Key Query:**

```sql
SELECT c.course_id, c.title, cc.name as category,
       u.name as instructor,
       COUNT(e.enrollment_id) as enrolled_count,
       AVG(r.rating) as avg_rating
FROM courses c
INNER JOIN course_categories cc ON c.category_id = cc.category_id
INNER JOIN users u ON c.instructor_id = u.user_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
LEFT JOIN reviews r ON c.course_id = r.course_id
GROUP BY c.course_id
```

---

### 5. **Categories** (`categories.php`)

**SQL Used:**

- LEFT JOIN to include all categories
- COUNT DISTINCT for accurate counts
- AVG for price calculations
- GROUP BY for category aggregation

---

### 6. **Assignments** (`assignments.php`)

**SQL Used:**

- INNER JOIN for required relationships
- LEFT JOIN for submissions
- COUNT and AVG aggregates
- Filtering by course

---

### 7. **Enrollments** (`enrollments.php`)

**SQL Used:**

- Multiple INNER JOINs (5 tables!)
- Complex filtering
- Progress tracking
- Revenue calculations

---

### 8. **Submissions** (`submissions.php`)

**SQL Used:**

- Multiple INNER JOINs
- Date comparisons
- Grade calculations
- Late submission detection

---

### 9. **Reviews** (`reviews.php`)

**SQL Used:**

- Multiple INNER JOINs
- CASE statements for rating distribution
- SUM with CASE for conditional counting
- AVG for rating calculations

**Special Feature - CASE Statement:**

```sql
SELECT
    COUNT(*) as total_reviews,
    AVG(rating) as avg_rating,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star
FROM reviews
```

---

### 10. **Add Assignment** (`add_assignment.php`)

**SQL Used:**

- INSERT statement with prepared parameters
- Demonstrates CRUD - Create operation

---

## Database Objects (setup.sql)

### Views (5 Total)

1. **course_overview** - Course statistics with enrollments and ratings
2. **student_performance** - Student metrics and grades
3. **instructor_dashboard** - Instructor analytics
4. **assignment_status** - Assignment tracking with due dates
5. **category_revenue** - Financial analysis by category

### Stored Procedures (4 Total)

1. **enroll_student(student_id, course_id)** - Enroll with validation
2. **get_top_students(limit_count)** - Get top performers
3. **course_revenue_report(course_id)** - Detailed revenue
4. **update_progress(student_id, course_id, progress)** - Update progress

### Functions (4 Total)

1. **course_completion_rate(course_id)** - Returns completion %
2. **get_grade_letter(grade)** - Converts number to letter (A-F)
3. **count_student_enrollments(student_id)** - Count enrollments
4. **calculate_course_revenue(course_id)** - Calculate revenue

### Triggers (5 Total)

1. **after_enrollment_insert** - Log enrollment timestamp
2. **before_course_delete** - Prevent deletion with enrollments
3. **before_review_insert** - Validate rating and enrollment
4. **after_submission_insert** - Auto-update course progress
5. **before_course_insert** - Prevent negative prices

---

## Complete SQL Feature Checklist

### ✅ Basic SQL

- [x] SELECT
- [x] FROM
- [x] WHERE
- [x] ORDER BY (ASC/DESC)
- [x] LIMIT
- [x] DISTINCT

### ✅ Filtering & Conditions

- [x] AND, OR
- [x] LIKE (pattern matching)
- [x] BETWEEN
- [x] IN
- [x] IS NULL / IS NOT NULL

### ✅ JOINs

- [x] INNER JOIN
- [x] LEFT JOIN
- [x] Multiple table joins (4-5 tables)
- [x] Table aliases

### ✅ Aggregate Functions

- [x] COUNT(\*)
- [x] COUNT(DISTINCT)
- [x] AVG()
- [x] SUM()
- [x] MAX()
- [x] MIN()

### ✅ Grouping

- [x] GROUP BY
- [x] HAVING
- [x] Multiple column GROUP BY

### ✅ Subqueries

- [x] Subquery in WHERE
- [x] Subquery in SELECT
- [x] Subquery in FROM (derived table)
- [x] Correlated subquery

### ✅ Set Operations

- [x] UNION
- [x] UNION ALL

### ✅ Advanced Operations

- [x] CASE statements
- [x] EXISTS
- [x] NOT EXISTS
- [x] Nested SELECT

### ✅ Date Functions

- [x] NOW()
- [x] CURRENT_DATE
- [x] DATEDIFF()
- [x] DATE_ADD()
- [x] DATE_SUB()

### ✅ String Functions

- [x] CONCAT()
- [x] UPPER()
- [x] LOWER()
- [x] SUBSTRING()
- [x] LENGTH()
- [x] SUBSTRING_INDEX()

### ✅ Database Objects

- [x] Views (CREATE VIEW)
- [x] Stored Procedures (CREATE PROCEDURE)
- [x] Functions (CREATE FUNCTION)
- [x] Triggers (CREATE TRIGGER)

### ✅ CRUD Operations

- [x] INSERT INTO
- [x] SELECT (Read)
- [x] UPDATE
- [x] DELETE

### ✅ Constraints

- [x] PRIMARY KEY
- [x] FOREIGN KEY
- [x] UNIQUE
- [x] NOT NULL
- [x] DEFAULT
- [x] CHECK
- [x] ON DELETE CASCADE
- [x] ON UPDATE CASCADE

### ✅ Data Types

- [x] INT
- [x] VARCHAR
- [x] TEXT
- [x] DECIMAL
- [x] DATETIME / TIMESTAMP
- [x] ENUM

---

## 🎓 Presentation Flow

### For Teacher Demonstration (5-10 minutes):

1. **Start**: Open `sql_operations.php`

   - Show table of contents
   - Demonstrate 2-3 sections (e.g., JOINs, Aggregates, Views)

2. **Navigate**: Show collapsible SQL on any page

   - Click SQL button on dashboard
   - Copy query and explain

3. **Database Objects**: Show views/procedures/functions

   - Scroll to Views section
   - Explain how they work

4. **Constraints**: Open phpMyAdmin

   - Show table structure
   - Point out foreign keys, constraints

5. **Finish**: Back to summary
   - Show feature checklist
   - Highlight completeness

**Total Time**: 5-10 minutes
**Pages to Visit**: 2-3 (sql_operations.php + 1-2 others)
**Key Point**: Everything is implemented and working!

---

## 📱 Quick Access URLs

After starting XAMPP and navigating to your project:

- **Main Demo**: `http://localhost/lms_db/sql_operations.php`
- **Dashboard**: `http://localhost/lms_db/`
- **Users**: `http://localhost/lms_db/users.php`
- **Courses**: `http://localhost/lms_db/courses.php`
- **phpMyAdmin**: `http://localhost/phpmyadmin`

---

## 💡 Pro Tips

1. **Use Ctrl+F** on sql_operations.php to find specific SQL keywords
2. **Copy SQL** using the copy button - test in phpMyAdmin
3. **Check setup.sql** to see all view/procedure/function/trigger definitions
4. **Filter and search** on each page to see dynamic SQL in action
5. **View page source** to see how SQL is embedded in PHP

---

**Last Updated**: October 2024
**Project**: LMS Database with Complete SQL Feature Demonstration
**Status**: ✅ Production Ready
