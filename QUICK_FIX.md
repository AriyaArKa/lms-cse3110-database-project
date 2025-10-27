# 🔧 Quick Fix Guide

## The Problem

The `course_overview` view and other database objects don't exist yet.

## The Solution (2 Minutes)

### Step 1: Open your browser

Go to:

```
http://localhost/lms_db/complete_setup.php
```

### Step 2: Click the button

Click **"🚀 Complete Database Setup"**

### Step 3: Done!

You'll see a list of all created objects:

- ✓ Created procedure: enroll_student
- ✓ Created procedure: get_top_students
- ✓ Created procedure: course_revenue_report
- ✓ Created procedure: update_progress
- ✓ Created function: course_completion_rate
- ✓ Created function: get_grade_letter
- ✓ Created function: count_student_enrollments
- ✓ Created function: calculate_course_revenue
- ✓ Created trigger: after_enrollment_insert
- ✓ Created trigger: before_course_delete
- ✓ Created trigger: before_review_insert
- ✓ Created trigger: before_course_insert

### Step 4: Visit SQL Operations

Click the link or go to:

```
http://localhost/lms_db/sql_operations.php
```

**Everything should work perfectly now! ✅**

---

## Alternative: Using phpMyAdmin

If you prefer phpMyAdmin:

1. Go to `http://localhost/phpmyadmin`
2. Select `university_db` database
3. Click **SQL** tab
4. Copy and paste the contents of `sql/setup.sql`
5. Click **Go**

---

## Verification

To verify everything is created, visit phpMyAdmin and check:

### Views (should see 5):

- course_overview ✓
- student_performance ✓
- instructor_dashboard ✓
- assignment_status ✓
- category_revenue ✓

### Procedures (should see 4):

- enroll_student ✓
- get_top_students ✓
- course_revenue_report ✓
- update_progress ✓

### Functions (should see 4):

- course_completion_rate ✓
- get_grade_letter ✓
- count_student_enrollments ✓
- calculate_course_revenue ✓

### Triggers (should see 4):

- after_enrollment_insert ✓
- before_course_delete ✓
- before_review_insert ✓
- before_course_insert ✓

---

## Quick Status Check

Run this in your browser:

```
http://localhost/lms_db/complete_setup.php
```

It will create everything you need!
