# 🎓 Learning Management System (LMS) - Online Course Platform

A comprehensive PHP-based Learning Management System demonstrating advanced MySQL/SQL features for managing online courses, student enrollments, assignments, submissions, and course reviews.

## 📋 Project Overview

This modern LMS platform provides a complete solution for managing online education with features found in platforms like Udemy, Coursera, and Khan Academy. It demonstrates advanced database concepts including complex relationships, user roles, course marketplace functionality, assignment tracking, and review systems.

## ✨ Features

### Core Functionality
- **User Management** - Unified user system with roles (students, instructors, administrators)
- **Course Marketplace** - Browse courses with pricing, ratings, and categories
- **Course Categories** - Organize courses into categories (Web Dev, Data Science, AI, etc.)
- **Enrollment System** - Student course enrollments with progress tracking
- **Assignment Management** - Create and track course assignments with due dates
- **Submission Tracking** - Student assignment submissions with grades
- **Review System** - 5-star course ratings with written reviews
- **Search & Filter** - Advanced filtering on all entity types
- **Modern UI** - Beautiful gradient design with Bootstrap 5

### Dashboard Features
- Total users by role (students, instructors, admins)
- Active courses and total enrollments
- Assignment counts and average course ratings
- Total revenue calculation from paid enrollments
- Recent users listing with role badges
- Top-rated courses with star ratings
- Category overview with enrollment statistics
- Quick action buttons for common tasks

## 🗂️ Database Schema

### Tables (7 Total)

1. **users** - Unified user table with role-based access (student/instructor/admin)
2. **course_categories** - Course categories for organization
3. **courses** - Course catalog with pricing, instructors, and descriptions
4. **enrollments** - Student-course relationships with progress percentage
5. **assignments** - Course assignments with titles, descriptions, and due dates
6. **submissions** - Student assignment submissions with grades
7. **reviews** - Course reviews with 1-5 star ratings and comments

### Key Relationships
```
users (instructor) (1) ----< (M) courses
course_categories (1) ----< (M) courses
users (student) (M) ----< enrollments >---- (M) courses
courses (1) ----< (M) assignments
assignments (1) ----< (M) submissions >---- (M) users (students)
courses (1) ----< (M) reviews >---- (M) users (students)
```

### Schema Features
- **Role-Based User System**: Single users table with ENUM role (student/instructor/admin)
- **Course Marketplace**: Pricing, categories, ratings, and instructor attribution
- **Progress Tracking**: Enrollment progress percentage (0-100)
- **Assignment System**: Due dates, submission tracking, grading
- **Review System**: 1-5 star ratings with CHECK constraint, written feedback

## 📊 Sample Data

The system includes comprehensive sample data:
- **30 users**: 2 admins, 8 instructors, 20 students
- **8 categories**: Web Development, Data Science, Mobile Dev, Database, Security, Cloud, AI, Business
- **20 courses**: Priced from $59.99 to $149.99
- **50 enrollments**: Various progress levels
- **40 assignments**: Across multiple courses
- **60 submissions**: Mix of graded and pending
- **35 reviews**: Realistic course feedback with ratings

### SQL Features Demonstrated

#### Basic Operations
✅ SELECT with WHERE, ORDER BY, LIMIT  
✅ INSERT - Adding new records  
✅ UPDATE - Modifying existing records  
✅ DELETE - Removing records  

#### Advanced Features
✅ INNER JOIN - Multiple table joins (5+ table joins)  
✅ LEFT JOIN - Optional relationships  
✅ COUNT, AVG, SUM - Aggregate functions  
✅ GROUP BY with HAVING - Complex grouping  
✅ DISTINCT - Unique value selection  
✅ LIKE - Pattern matching  
✅ Subqueries - Nested SELECT statements  
✅ DATE functions - Date manipulation and comparison  

#### Database Design
✅ Primary Keys (AUTO_INCREMENT)  
✅ Foreign Keys with ON DELETE CASCADE  
✅ UNIQUE constraints (emails, category names)  
✅ NOT NULL constraints  
✅ DEFAULT values (CURRENT_TIMESTAMP)  
✅ ENUM types (roles, ratings)  
✅ CHECK constraints (rating 1-5)  
✅ Decimal types for pricing and grades  
✅ Composite relationships (many-to-many)  
✅ Self-referencing joins (user roles)  

## 📁 Project Structure

```
lms/
├── config/
│   └── database.php           # PDO database connection class
├── sql/
│   └── setup.sql             # Complete LMS schema with sample data
├── index.php                 # Modern dashboard with LMS statistics
├── users.php                 # User management with role filtering
├── categories.php            # Course category browser
├── courses.php               # Course catalog with pricing/ratings
├── assignments.php           # Assignment list with due dates
├── submissions.php           # Submission tracking with grades
├── reviews.php               # Course reviews and ratings
├── enrollments.php           # Enrollment management with progress
├── add_*.php                 # CRUD: Create operations (users, courses, etc.)
├── edit_*.php                # CRUD: Update operations
├── delete_*.php              # CRUD: Delete handlers
└── README.md                 # This documentation
```

## 🚀 Installation & Setup

### Prerequisites
- **XAMPP** (or similar LAMP/WAMP stack)
  - Apache 2.4+
  - MySQL 5.7+ or MariaDB
  - PHP 7.4+
- Web browser (Chrome, Firefox, Edge, etc.)

### Installation Steps

1. **Install XAMPP**
   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install and start Apache and MySQL services

2. **Copy Project Files**
   ```bash
   # Copy the 'lms' folder to your XAMPP htdocs directory
   # Windows: C:\xampp\htdocs\lms
   # Mac: /Applications/XAMPP/htdocs/lms
   # Linux: /opt/lampp/htdocs/lms
   ```

3. **Access the Application**
   - Open your web browser
   - Navigate to: `http://localhost/lms/`
   - The database will be created automatically on first visit

4. **Database Setup (Automatic)**
   - The `database.php` file checks if the database exists
   - If not found, it automatically:
     - Creates the `university_db` database
     - Runs the `sql/setup.sql` script
     - Creates all 7 tables
     - Inserts sample data (30 users, 8 categories, 20 courses, 50 enrollments, 40 assignments, 60 submissions, 35 reviews)

### Manual Database Setup (Optional)

If you prefer to set up the database manually:

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `university_db`
3. Import the SQL file: `sql/setup.sql`

## 💾 Sample Data Included

The system comes pre-loaded with realistic sample data for a complete LMS experience:

- **30 Users**:
  - 2 Administrators
  - 8 Instructors (Dr. Emily Johnson, Prof. Michael Chen, etc.)
  - 20 Students (Alice Anderson, Bob Baker, Carol Clark, etc.)
- **8 Course Categories**: Web Development, Data Science, Mobile Development, Database Management, Cybersecurity, Cloud Computing, AI, Business & Marketing
- **20 Courses**: Realistic course titles with pricing from $59.99 to $149.99
- **50 Enrollments**: Students enrolled in various courses with progress 0-100%
- **40 Assignments**: Due dates, descriptions across multiple courses
- **60 Submissions**: Mix of graded (with percentages) and pending submissions
- **35 Course Reviews**: 1-5 star ratings with written comments

## 🎨 User Interface

### Design Highlights
- **Modern Gradient Theme** - Purple/indigo color scheme
- **Bootstrap 5** - Responsive, mobile-first design
- **Font Awesome 6** - Professional icon library
- **Card-Based Layouts** - Clean, organized content
- **Color-Coded Elements**:
  - Primary Actions: Purple gradient
  - Students: Blue badges
  - Instructors: Green badges
  - Admins: Red badges
  - Ratings: Gold stars
  - Progress Bars: Color-coded by completion
- **Hover Animations** - Smooth transitions on cards and rows
- **Responsive Tables** - Mobile-friendly data display

### Pages Overview

#### Dashboard (`index.php`)
- 8 statistics cards with LMS metrics
- Recent users table with role badges
- Top-rated courses with star ratings
- Category overview with enrollment counts
- Revenue tracking
- Quick action buttons

#### User Management (`users.php`)
- Role-based filtering (student/instructor/admin)
- Search by name or email
- User statistics by role
- Avatar placeholders
- Edit and delete actions

#### Course Catalog (`courses.php`)
- Card-based grid layout
- Category and instructor filters
- Pricing display
- Star ratings with review counts
- Student enrollment counts
- Course descriptions

#### Categories (`categories.php`)
- Icon-based category cards
- Course and enrollment statistics per category
- Average pricing per category
- Quick navigation to category courses

#### Assignments (`assignments.php`)
- Due date tracking with status badges (Overdue/Due Soon/Active)
- Submission counts and average grades
- Linked to course information
- Date-based coloring

#### Submissions (`submissions.php`)
- Filterable by assignment and student
- Late submission tracking
- Grade display with color-coded badges (A-F)
- Submission timestamps

#### Reviews (`reviews.php`)
- 5-star rating distribution graph
- Filter by course and rating
- Average rating calculation
- Student feedback with timestamps

#### Enrollments (`enrollments.php`)
- Progress bars showing course completion
- Revenue per enrollment
- Category and instructor display
- Search and filter functionality

## 🔍 SQL Query Examples

### Top Courses by Rating
```sql
SELECT 
    c.title,
    c.price,
    u.name AS instructor_name,
    AVG(r.rating) AS avg_rating,
    COUNT(DISTINCT e.enrollment_id) AS total_students,
    COUNT(DISTINCT r.review_id) AS review_count
FROM courses c
INNER JOIN users u ON c.instructor_id = u.user_id
LEFT JOIN reviews r ON c.course_id = r.course_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
GROUP BY c.course_id
HAVING avg_rating IS NOT NULL
ORDER BY avg_rating DESC, total_students DESC
LIMIT 10;
```

### Student Progress Overview
```sql
SELECT 
    u.name AS student_name,
    c.title AS course_title,
    e.progress,
    e.enrolled_at,
    COUNT(DISTINCT s.submission_id) AS submissions_made,
    AVG(s.grade) AS average_grade
FROM enrollments e
INNER JOIN users u ON e.student_id = u.user_id
INNER JOIN courses c ON e.course_id = c.course_id
LEFT JOIN assignments a ON c.course_id = a.course_id
LEFT JOIN submissions s ON a.assignment_id = s.assignment_id AND s.student_id = u.user_id
GROUP BY e.enrollment_id
ORDER BY e.progress DESC;
```

### Category Performance Statistics
```sql
SELECT 
    cc.name AS category_name,
    COUNT(DISTINCT c.course_id) AS total_courses,
    COUNT(DISTINCT e.enrollment_id) AS total_enrollments,
    AVG(c.price) AS avg_price,
    AVG(r.rating) AS avg_rating
FROM course_categories cc
LEFT JOIN courses c ON cc.category_id = c.category_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
LEFT JOIN reviews r ON c.course_id = r.course_id
GROUP BY cc.category_id
ORDER BY total_enrollments DESC;
```

### Assignment Submission Tracking
```sql
SELECT 
    a.title AS assignment_title,
    a.due_date,
    c.title AS course_title,
    COUNT(s.submission_id) AS total_submissions,
    AVG(s.grade) AS average_grade,
    SUM(CASE WHEN s.submitted_at > a.due_date THEN 1 ELSE 0 END) AS late_submissions
FROM assignments a
INNER JOIN courses c ON a.course_id = c.course_id
LEFT JOIN submissions s ON a.assignment_id = s.assignment_id
GROUP BY a.assignment_id
ORDER BY a.due_date DESC;
```

### Instructor Revenue Report
```sql
SELECT 
    u.name AS instructor_name,
    COUNT(DISTINCT c.course_id) AS courses_taught,
    COUNT(DISTINCT e.enrollment_id) AS total_enrollments,
    SUM(c.price) AS total_revenue,
    AVG(r.rating) AS avg_instructor_rating
FROM users u
INNER JOIN courses c ON u.user_id = c.instructor_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
LEFT JOIN reviews r ON c.course_id = r.course_id
WHERE u.role = 'instructor'
GROUP BY u.user_id
ORDER BY total_revenue DESC;
``````

### Students At Risk (Low Progress)
```sql
SELECT 
    u.name AS student_name,
    u.email,
    c.title AS course_title,
    e.progress,
    e.enrolled_at,
    DATEDIFF(CURRENT_DATE, e.enrolled_at) AS days_enrolled
FROM enrollments e
INNER JOIN users u ON e.student_id = u.user_id
INNER JOIN courses c ON e.course_id = c.course_id
WHERE e.progress < 25 
AND DATEDIFF(CURRENT_DATE, e.enrolled_at) > 30
ORDER BY e.progress ASC;
```

### Overdue Assignments Report
```sql
SELECT 
    a.title AS assignment_title,
    c.title AS course_title,
    a.due_date,
    COUNT(DISTINCT e.student_id) AS enrolled_students,
    COUNT(DISTINCT s.submission_id) AS submissions_received,
    (COUNT(DISTINCT e.student_id) - COUNT(DISTINCT s.submission_id)) AS missing_submissions
FROM assignments a
INNER JOIN courses c ON a.course_id = c.course_id
LEFT JOIN enrollments e ON c.course_id = e.course_id
LEFT JOIN submissions s ON a.assignment_id = s.assignment_id
WHERE a.due_date < CURRENT_DATE
GROUP BY a.assignment_id
HAVING missing_submissions > 0
ORDER BY a.due_date DESC;
```

## 📊 Advanced Features

### Role-Based Access Control
- **Students**: View enrolled courses, submit assignments, write reviews
- **Instructors**: Create courses, manage assignments, grade submissions
- **Administrators**: Full system access, user management, analytics

### Revenue Tracking
- Course pricing stored as DECIMAL(10,2)
- Revenue calculations per course, category, and instructor
- Enrollment-based revenue reporting

### Progress Management
- 0-100% progress tracking per enrollment
- Color-coded progress bars (red<25%, yellow<50%, cyan<80%, green≥80%)
- Progress-based filtering and reporting

### Rating System
- 5-star rating with CHECK constraint (1-5)
- Average rating calculations
- Rating distribution analytics
- Review text with timestamps

### Assignment Workflow
- Due date tracking with overdue detection
- Submission grading (0-100%)
- Late submission tracking
- Average grade calculations

## 🛠️ Technical Features

### Database Design
- **InnoDB Engine**: ACID compliance, foreign key support
- **Referential Integrity**: CASCADE and RESTRICT constraints
- **Data Validation**: CHECK constraints for ratings and roles
- **Indexing**: Optimized queries on foreign keys
- **Sample Data**: Comprehensive test dataset included

### PHP Best Practices
- **PDO with Prepared Statements**: SQL injection prevention
- **Error Handling**: Try-catch blocks throughout
- **Separation of Concerns**: database.php connection layer
- **Clean Code**: Consistent formatting and comments

### Security Considerations
- Parameterized queries prevent SQL injection
- Input validation on forms (future CRUD implementation)
- Password hashing (bcrypt) for user authentication (future)
- Role-based authorization checks (future)

## 📁 File Structure
```
lms/
├── sql/
│   └── setup.sql              # Complete database schema and sample data
├── database.php               # PDO connection and auto-setup
├── index.php                  # Dashboard with LMS statistics
├── users.php                  # User management (role-based)
├── categories.php             # Course category browser
├── courses.php                # Course marketplace
├── assignments.php            # Assignment tracker
├── submissions.php            # Submission grading interface
├── reviews.php                # Course review system
├── enrollments.php            # Enrollment progress tracking
├── README.md                  # This documentation
└── [Future CRUD files]        # add_*.php, edit_*.php, delete_*.php
```

## 🚀 Future Enhancements

### Planned Features
- **CRUD Operations**: Complete add/edit/delete handlers for all entities
- **Authentication System**: Login/logout with session management
- **Dashboard Analytics**: Charts and graphs for metrics
- **Search Functionality**: Advanced search across courses and users
- **File Uploads**: Assignment submissions with file attachments
- **Email Notifications**: Due date reminders, grade notifications
- **Payment Integration**: Course purchase workflow
- **Discussion Forums**: Student-instructor communication
- **Certificates**: Completion certificates upon 100% progress

### Scalability Considerations
- Migration to PostgreSQL for larger datasets
- Redis caching for frequently accessed data
- API development for mobile apps
- Microservices architecture for high traffic

## 📝 Usage Tips

### Browsing the System
1. **Start at Dashboard** (`index.php`) - Get overview of all metrics
2. **Explore Categories** - Browse courses by category
3. **View Courses** - See pricing, ratings, and instructor info
4. **Check Enrollments** - Monitor student progress
5. **Review Submissions** - Track assignment completions

### Common Workflows
- **Enrolling Students**: courses.php → find course → (future) enroll button
- **Creating Assignments**: assignments.php → (future) add assignment form
- **Grading Submissions**: submissions.php → find ungraded → (future) grade input
- **Managing Users**: users.php → filter by role → (future) edit/delete

## 🐛 Troubleshooting

### Database Connection Issues
- Verify MySQL/MariaDB is running in XAMPP
- Check `database.php` credentials (default: root, no password)
- Ensure port 3306 is not blocked

### Page Not Loading
- Check Apache is running in XAMPP
- Verify file path: `e:\xampp\htdocs\lms\`
- Clear browser cache and reload

### SQL Errors
- Check if database `university_db` exists
- Verify all tables created successfully
- Review SQL error messages in browser

### Sample Data Missing
- Re-run `sql/setup.sql` in phpMyAdmin
- Check for foreign key constraint errors
- Verify table creation order (parents before children)

## 📄 License

This project is for educational purposes. Feel free to use, modify, and distribute as needed.

## 👨‍💻 Contributing

This is a learning project. Contributions, improvements, and suggestions are welcome!

---

**Built with ❤️ for learning SQL and modern web development**

Last Updated: 2024
```

## 🛠️ Technical Details

### Database Configuration
- **Host**: localhost
- **Database**: university_db
- **Username**: root (default XAMPP)
- **Password**: (empty by default)
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci

### PHP Features Used
- PDO (PHP Data Objects) for database access
- Prepared statements for SQL injection prevention
- Exception handling with try-catch
- GET/POST request handling
- HTML escaping for XSS prevention

### Security Features
- Parameterized queries (PDO prepared statements)
- Input validation
- HTML entity encoding
- SQL injection protection
- Error handling

## 📊 Database Statistics

### Total Sample Records
- Departments: 6
- Instructors: 12
- Students: 25 (23 active, 2 graduated)
- Courses: 20 (all active)
- Enrollments: 60 (38 completed with grades, 22 currently enrolled)

### Constraints
- 8 Foreign key relationships
- 5 Unique constraints
- 15+ Indexes for performance
- Multiple ENUM fields for data integrity

## 🎯 Learning Outcomes

This project demonstrates:
1. **Database Design** - Normalized schema with proper relationships
2. **SQL Operations** - CRUD operations, joins, aggregations
3. **PHP Programming** - Object-oriented database class, form handling
4. **Web Development** - HTML, CSS (Bootstrap), responsive design
5. **Security** - Prepared statements, input validation
6. **User Experience** - Intuitive navigation, search, and filtering

## 🐛 Troubleshooting

### Database Connection Issues
- Ensure Apache and MySQL are running in XAMPP Control Panel
- Check database credentials in `config/database.php`
- Verify port 3306 (MySQL) is not blocked

### Tables Not Created
- Check file permissions on `sql/setup.sql`
- Manually import SQL file via phpMyAdmin
- Check MySQL error logs in XAMPP

### Blank Pages
