# 🚀 RUN THIS FIRST - Database Setup

## Step 1: Make Sure XAMPP is Running

1. Open **XAMPP Control Panel**
2. Start **Apache** (should show green "Running")
3. Start **MySQL** (should show green "Running")

## Step 2: Run the Setup Script

Open your browser and go to:

```
http://localhost/lms_db/complete_setup.php
```

## Step 3: Click the Button

Click the big blue button that says:
**"🚀 Complete Database Setup"**

## Step 4: Wait for Completion

You should see:

- ✅ Green success messages for all created objects
- ⚠️ Yellow warnings for any errors (usually safe to ignore if most items succeeded)

## Step 5: View the Results

After setup completes, click the button:
**"SQL Operations Demo"**

Or go directly to:

```
http://localhost/lms_db/sql_operations.php
```

---

## ✅ What Gets Created

### Tables & Views (Already Created)

- ✅ 7 Tables (users, courses, enrollments, etc.)
- ✅ 5 Views (course_overview, student_performance, etc.)

### What This Script Creates

- 🔧 4 Stored Procedures

  - `enroll_student` - Enroll a student in a course
  - `get_top_students` - Get top performing students
  - `course_revenue_report` - Revenue report for a course
  - `update_progress` - Update student progress

- 🧮 4 Functions

  - `course_completion_rate` - Calculate completion percentage
  - `get_grade_letter` - Convert numeric grade to letter
  - `count_student_enrollments` - Count student enrollments
  - `calculate_course_revenue` - Calculate course revenue

- ⚡ 4 Triggers
  - `after_enrollment_insert` - Log enrollment activity
  - `before_course_delete` - Prevent deletion with enrollments
  - `before_review_insert` - Validate review data
  - `before_course_insert` - Validate course price

---

## 🔍 Troubleshooting

### Problem: Page shows blank

**Solution**: Check that Apache is running in XAMPP

### Problem: "Access denied" error

**Solution**: Your MySQL password might not be empty. Edit `complete_setup.php` line 18:

```php
$pdo = new PDO('mysql:host=localhost;dbname=university_db', 'root', 'YOUR_PASSWORD');
```

### Problem: Some items show errors

**Solution**: That's usually OK! As long as you see "Created: 12 objects" or similar, you're good to go.

---

## 📊 After Setup

Visit these pages to see everything working:

- **Main Demo**: http://localhost/lms_db/sql_operations.php
- **Dashboard**: http://localhost/lms_db/
- **Users Page**: http://localhost/lms_db/users.php
- **Courses Page**: http://localhost/lms_db/courses.php

---

**Total Time**: 2 minutes ⏱️
**Difficulty**: Easy 😊
