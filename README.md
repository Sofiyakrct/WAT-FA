# LearnHub – Online Learning Platform

A clean, modern online learning platform built with HTML, CSS, JavaScript, PHP and MySQL.

---

## 🚀 Quick Setup

### 1. Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache / Nginx with PHP support (or use `php -S localhost:8000`)

### 2. Database Setup

Open MySQL and run:
```sql
SOURCE /path/to/project/config/setup.sql;
```

Or paste the contents of `config/setup.sql` into phpMyAdmin.

### 3. Configure Database Connection

Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'learnhub');
```

### 4. Run the App

**With PHP built-in server:**
```bash
cd online-learning-platform
php -S localhost:8000
```

Then open http://localhost:8000

**With Apache/WAMP/XAMPP:**  
Place the folder in `htdocs/` or `www/` and visit `http://localhost/online-learning-platform/`

---

## 🔐 Demo Accounts

| Email           | Password    |
|-----------------|-------------|
| alex@demo.com   | password123 |
| sam@demo.com    | password123 |

---

## 📁 Project Structure

```
online-learning-platform/
├── index.php              # Home page
├── courses.php            # Course listing with search & filters
├── course-details.php     # Single course page + enroll button
├── login.php              # Authentication
├── register.php           # User registration
├── dashboard.php          # User dashboard with enrolled courses
├── enroll.php             # Enrollment handler (POST)
├── update-progress.php    # Progress update handler (POST)
├── logout.php             # Session destroy
├── config/
│   ├── db.php             # DB connection + helper functions
│   └── setup.sql          # Database schema + seed data
└── assets/
    └── css/
        └── style.css      # All styles
```

---

## ✨ Features

- **Authentication**: PHP session-based login, registration, logout
- **Course Listing**: Search by keyword, filter by category & level, sort options
- **Course Details**: Full page with curriculum, instructor info, enroll card
- **Enrollment**: One-click enroll stored in MySQL `enrollments` table
- **Dashboard**: View enrolled courses, track progress, continue learning
- **Progress Tracking**: Simulate course progress with "Continue" button
- **Responsive**: Mobile-friendly layout

---

## 🎨 Design

- Dark theme with purple/pink/green accent palette
- Syne (display) + DM Sans (body) font pairing
- Glassmorphism nav, gradient course thumbnails
- Smooth hover animations throughout
