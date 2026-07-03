# AI Student Academic Performance Predictor

This project is a PHP + MySQL web application for an academic performance predictor system designed for tertiary-level (university) students. It includes:

- Student profile management
- Teacher and admin grade encoding
- Attendance monitoring
- Performance prediction using attendance, study hours, assignments, and quiz scores
- Dashboard views for admin, teacher, and student users
- Report generation and email notifications

## Dataset

### Current Dataset
The application comes pre-loaded with a **synthetic tertiary-level dataset containing 300 university students**. This dataset includes:
- Student IDs (STU1000 - STU1299)
- Realistic attendance data (60-100%)
- Study hours per week (5-25 hours)
- Assignment scores (50-100%)
- Quiz scores (50-100%)
- AI-predicted grades based on the weighted formula
- Final grades

**Prediction Formula**: `(Attendance × 0.25) + (Study Hours × 2.5) + (Assignments × 0.25) + (Quiz Score × 0.25)`

### Optional: Open University Learning Analytics Dataset
For a more comprehensive university-level dataset, you can manually integrate the **Open University Learning Analytics Dataset**, which contains:
- **32,593 students** from real university courses
- 22 courses across multiple semesters
- 10.6+ million interaction logs
- Student assessment results
- VLE (Virtual Learning Environment) interaction data

**To use this dataset:**
1. Download from: https://www.kaggle.com/datasets/rocki37/open-university-learning-analytics-dataset
2. Extract `studentInfo.csv` and `studentAssessment.csv`
3. Place them in the `data/` folder
4. Run the import script to transform the data

**Citation**: Kuzilek J., Hlosta M., Zdrahal Z. (2017). Open University Learning Analytics dataset. Scientific Data, 4:170171.

## How to run with XAMPP

1. **Start XAMPP services:**
   - Open XAMPP Control Panel
   - Start Apache and MySQL

2. **Place the project:**
   - Copy this entire folder to `C:\xampp\htdocs\`
   - Example path: `C:\xampp\htdocs\student-predictor`

3. **Access the application:**
   - Open browser and go to: `http://localhost/student-predictor/`

## Admin approval and email notifications
New accounts are created as pending and require administrator approval before they can log in. Default seeded accounts are already verified and can be used immediately.

To receive real email notifications, configure the SMTP settings in `includes/db.php` with a legitimate Gmail account and an App Password.

- `SMTP_HOST`: `smtp.gmail.com`
- `SMTP_PORT`: `465`
- `SMTP_USERNAME`: your Gmail address
- `SMTP_PASSWORD`: your Gmail app password
- `SMTP_FROM_EMAIL`: usually the same as your Gmail address

Example:
```php
define('SMTP_USERNAME', 'your_gmail_address@gmail.com');
define('SMTP_PASSWORD', 'your_gmail_app_password');
```

Email notifications are sent only after an administrator approves a pending account.

4. **Default login credentials:**
   - **Admin**: username: `admin` | password: `password123`
   - **Teacher**: username: `teacher` | password: `password123`
   - **Student**: username: `student` | password: `password123`
   - **Additional students**: `stu1000` through `stu1299` with password `student123`

> Note: all seeded demo/test accounts are pre-approved by default. Only new registrations require manual admin approval.
>
> The database now includes a curated set of dummy users and student records with name-based email addresses (for example, `olivia.james@student.edu`). Email notifications will be sent to those configured addresses when notifications are created. To receive them, configure SMTP settings in `includes/db.php` with a valid mail server or Gmail app password.
## Project Structure

```
├── index.php                 # Home page
├── login.php                 # Login page
├── register.php              # Registration page
├── dashboard.php             # Role-based dashboard
├── logout.php                # Logout handler
├── includes/
│   ├── db.php               # Database connection and initialization
│   ├── functions.php        # Helper functions and prediction logic
│   ├── header.php           # Page header template
│   └── footer.php           # Page footer template
├── admin/
│   ├── index.php            # Admin dashboard
│   ├── students.php         # View all students
│   └── teachers.php         # View all teachers
├── teacher/
│   ├── encode-grades.php    # Grade entry form
│   ├── attendance.php       # Attendance tracking
│   └── reports.php          # Generate student reports
├── student/
│   ├── index.php            # Student dashboard
│   ├── profile.php          # Update student profile
│   ├── predict.php          # AI prediction tool
│   └── reports.php          # View generated reports
├── database/
│   └── schema.sql           # MySQL database schema
├── data/
│   └── student_performance.csv   # Tertiary-level student dataset (300 students)
├── assets/
│   └── styles.css           # CSS styling
└── scripts/
    └── generate_dataset.py  # Python script to regenerate dataset
```

## Features

### Admin Dashboard
- Overview of all students and teachers
- Student roster with performance metrics
- Teacher roster management
- System monitoring

### Teacher Dashboard
- Grade entry and management
- Attendance tracking
- Report generation for students
- Notification sending

### Student Dashboard
- Personal performance metrics
- AI-powered grade prediction tool
- Grade history and reports
- Notification inbox
- Profile management

## Database Schema

The application uses a relational MySQL database with the following tables:
- **users**: Authentication and user profiles
- **students**: Student information and metrics
- **teachers**: Teacher profiles
- **grades**: Grade records
- **attendance_records**: Attendance logs
- **notifications**: System notifications
- **reports**: Generated academic reports

## Features Included

✓ Role-based access control (Admin, Teacher, Student)
✓ Student profile management
✓ Grade encoding with weighted calculations
✓ Attendance monitoring
✓ AI performance prediction
✓ Report generation
✓ Email notifications
✓ Responsive design
✓ Secure password hashing
✓ Session management

## Notes

- All passwords are hashed using PHP's `password_hash()` function
- The application uses MySQLi with prepared statements for security
- Session-based authentication
- All user input is sanitized and validated

