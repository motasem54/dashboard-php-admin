# 📊 Dashboard PHP Admin

لوحة تحكم إدارية بلغة PHP مع MySQL ونظام تسجيل دخول آمن وثيم داكن.

Admin Dashboard built with pure PHP and MySQL featuring secure authentication and dark theme interface.

## ✨ Features | الميزات

- 🔐 **Secure Authentication** - نظام مصادقة آمن
  - Session-based authentication
  - Password hashing with PHP password_hash()
  - SQL injection prevention with PDO prepared statements
  - XSS protection

- 👥 **User Management** - إدارة المستخدمين
  - Add, edit, delete users
  - Role-based access (Admin/User)
  - Real-time statistics
  - User activity tracking

- 📝 **Data Logs** - سجلات البيانات
  - Comprehensive activity logging
  - Login/logout tracking
  - IP address and user agent recording
  - Action filtering and search

- 👤 **User Profile** - الملف الشخصي
  - View profile information
  - Change password
  - Account details

- ⚙️ **System Settings** - إعدادات النظام
  - System statistics
  - Database information
  - Quick actions panel
  - Application info

- 🎨 **Modern Dark Theme** - ثيم داكن عصري
  - Professional dark color scheme
  - Responsive design
  - RTL support for Arabic
  - Beautiful gradient effects

- ⚡ **Performance** - الأداء
  - Pure PHP (no frameworks needed)
  - Optimized MySQL queries with indexes
  - PDO for database operations
  - Lightweight and fast

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Frontend**: Pure CSS (no frameworks)
- **Security**: PDO, password_hash, sessions

## 🚀 Quick Start

### Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- mod_rewrite enabled (Apache)

### 1. Clone Repository

```bash
git clone https://github.com/motasem54/dashboard-php-admin.git
cd dashboard-php-admin
```

### 2. Database Setup

#### Option A: Automatic (Recommended)
The database tables will be created automatically when you first access the application.

#### Option B: Manual
Import the SQL file:

```bash
mysql -u root -p < database.sql
```

Or use phpMyAdmin to import `database.sql`

### 3. Configuration

Edit `config/database.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'dashboard_db');
```

### 4. Access the Dashboard

Open your browser and navigate to:
```
http://localhost/dashboard-php-admin
```

## 🔑 Default Login Credentials

```
Username: admin
Password: admin123
```

**⚠️ مهم | Important**: Change the default password after first login!

## 📊 Available Pages

### Public Pages
- **login.php** - Login page
- **logout.php** - Logout handler

### User Pages (Requires Authentication)
- **dashboard.php** - Main dashboard with statistics
- **profile.php** - User profile and password change

### Admin Pages (Requires Admin Role)
- **users.php** - User management (list, delete)
- **user-add.php** - Add new user
- **user-edit.php** - Edit existing user
- **settings.php** - System settings and statistics

## 📁 Project Structure

```
dashboard-php-admin/
├── config/
│   ├── database.php       # Database configuration
│   └── init.php           # Application initialization
├── includes/
│   ├── auth.php           # Authentication functions
│   ├── logger.php         # Activity logging functions
│   └── users.php          # User management functions
├── assets/
│   └── css/
│       └── style.css      # Dark theme styles
├── login.php              # Login page
├── logout.php             # Logout handler
├── dashboard.php          # Main dashboard
├── profile.php            # User profile
├── users.php              # User management
├── user-add.php           # Add user form
├── user-edit.php          # Edit user form
├── settings.php           # System settings
├── index.php              # Entry point
├── database.sql           # Database schema
├── .htaccess              # Apache configuration
└── README.md
```

## 📊 Dashboard Features

### Statistics Cards
- 👥 Total users count
- 📊 Total logs count
- ✅ Successful login attempts
- ❌ Failed login attempts

### Users Table
- User ID
- Username
- Email address
- Role (Admin/User) with badges
- Account creation date
- Actions (Edit/Delete) for admins

### Data Logs Table
- Log ID
- Associated username
- Action type (with color-coded badges)
- Description in Arabic
- IP address
- Full timestamp

### User Profile
- View account information
- Change password securely
- Account creation and update dates
- Role display

### System Settings (Admin Only)
- Database size and statistics
- Total users and logs count
- Recent activity (24h, 7 days)
- PHP version and timezone
- Database tables information
- Quick action links

## 🔒 Security Features

- **Password Hashing**: Using PHP's `password_hash()` with bcrypt
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: `htmlspecialchars()` for output
- **Session Security**: HTTP-only cookies, secure session handling
- **CSRF Protection**: Can be added (not included by default)
- **Input Validation**: Server-side validation
- **Role-Based Access**: Admin and user roles

## 🎨 Customization

### Theme Colors

Edit `assets/css/style.css` and modify the CSS variables:

```css
:root {
    --bg-primary: #0a0a0a;
    --bg-secondary: #141414;
    --accent: #3b82f6;
    /* ... more colors */
}
```

### Database Configuration

Edit `config/database.php` to change database settings.

### Application Name

Edit `config/init.php`:

```php
define('APP_NAME', 'لوحة التحكم الإدارية');
```

## 📦 Deployment

### Shared Hosting (cPanel)

1. Upload files via FTP to `public_html` or subdirectory
2. Import `database.sql` via phpMyAdmin
3. Edit `config/database.php` with your credentials
4. Access your domain

### VPS/Dedicated Server

1. Clone repository to `/var/www/html/`
2. Set proper permissions:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/dashboard-php-admin
   sudo chmod -R 755 /var/www/html/dashboard-php-admin
   ```
3. Import database
4. Configure virtual host
5. Enable SSL (recommended)

### Production Checklist

- [ ] Change default admin password
- [ ] Update database credentials in `config/database.php`
- [ ] Disable error reporting in `config/init.php`
- [ ] Enable HTTPS/SSL
- [ ] Set secure session cookies
- [ ] Regular database backups
- [ ] Keep PHP and MySQL updated

## 🐛 Troubleshooting

### Database Connection Error

- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

### Login Not Working

- Clear browser cache and cookies
- Check if sessions are enabled in PHP
- Verify admin user exists in database

### Styling Issues

- Clear browser cache
- Check if `assets/css/style.css` is accessible
- Verify correct path in HTML files

### Permission Denied

- Check file permissions (755 for directories, 644 for files)
- Ensure web server user has access

## 📝 Database Schema

### Users Table

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| username | VARCHAR(50) | Unique username |
| email | VARCHAR(100) | User email |
| password | VARCHAR(255) | Hashed password |
| role | VARCHAR(20) | User role (admin/user) |
| created_at | TIMESTAMP | Account creation time |
| updated_at | TIMESTAMP | Last update time |

### Data Logs Table

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user_id | INT | Foreign key to users |
| action | VARCHAR(100) | Action type |
| description | TEXT | Action description |
| ip_address | VARCHAR(45) | User IP address |
| user_agent | TEXT | Browser user agent |
| created_at | TIMESTAMP | Log creation time |

## 📝 License

MIT License - Free to use for personal and commercial projects!

## 👤 Author

**Motasem**
- GitHub: [@motasem54](https://github.com/motasem54)

## 🚀 Support

If you find this project helpful, please give it a ⭐️ on GitHub!

## 📝 Notes

- Dashboard uses RTL (Right-to-Left) layout for Arabic
- All UI text is in Arabic by default
- Compatible with PHP 7.4, 8.0, 8.1, 8.2
- Tested on Apache and Nginx
- Works on shared hosting and VPS

---

Made with ❤️ using PHP & MySQL
