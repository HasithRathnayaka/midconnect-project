# MidConnect - Digital Midwife Activity Tracking System

## 📋 Project Overview

MidConnect is a comprehensive web-based system designed to streamline the tracking and management of midwife activities in Sri Lanka's healthcare system. The system addresses the challenges of manual, paper-based reporting by providing a centralized digital platform for recording daily activities, generating reports, and monitoring performance.

## 🎯 Key Features

### For Midwives
- **Activity Logging**: Digital recording of home visits, vaccinations, counseling sessions, and other activities
- **Schedule Management**: View and manage daily schedules and appointments
- **Patient Records**: Maintain and update patient information
- **Report Generation**: Create and submit activity reports
- **Performance Tracking**: Monitor personal performance metrics

### For MOH Administrators
- **Dashboard Overview**: Real-time statistics and key performance indicators
- **Midwife Management**: Monitor and manage midwife activities and performance
- **Activity Monitoring**: Track all activities across the jurisdiction
- **Report Analytics**: Generate comprehensive reports and analytics
- **Performance Evaluation**: Assess midwife performance and set goals
- **Notification System**: Receive alerts and important updates

## 🏗️ System Architecture

### Frontend
- **HTML5**: Semantic markup and structure
- **CSS3**: Professional medical-themed styling with responsive design
- **JavaScript (ES6+)**: Interactive functionality and client-side validation
- **Chart.js**: Data visualization and reporting charts

### Backend
- **PHP 8+**: Server-side logic and API endpoints
- **MySQL**: Relational database for data storage
- **PDO**: Database abstraction layer for security

### Security Features
- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session management with timeout
- CSRF protection
- Audit logging for all activities

## 📁 Project Structure

```
MidConnect/
├── index.html                 # Landing page
├── midwife-login.html        # Midwife login page
├── admin-login.html          # Admin login page
├── css/
│   └── main.css              # Main stylesheet
├── js/
│   ├── main.js               # Core JavaScript functions
│   └── auth.js               # Authentication handling
├── php/
│   ├── config.php            # Database configuration
│   ├── midwife_login.php     # Midwife authentication
│   ├── admin_login.php       # Admin authentication
│   ├── create_activity.php   # Activity creation
│   └── get_activities.php    # Activity retrieval
├── database/
│   └── midconnect_schema.sql # Database schema
├── admin/
│   └── dashboard.html        # Admin dashboard
├── midwife/
│   └── dashboard.html        # Midwife dashboard
└── images/                   # Image assets
```

## 🚀 Installation Guide

### Prerequisites
- **Web Server**: Apache/Nginx with PHP support
- **PHP**: Version 8.0 or higher
- **MySQL**: Version 8.0 or higher
- **Extensions**: PDO, PDO_MySQL, JSON

### Step 1: Database Setup
1. Create a MySQL database named `midconnect_db`
2. Import the database schema:
   ```sql
   mysql -u root -p midconnect_db < database/midconnect_schema.sql
   ```

### Step 2: Configuration
1. Update database credentials in `php/config.php`:
   ```php
   private $host = 'localhost';
   private $dbname = 'midconnect_db';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

### Step 3: Web Server Setup
1. Copy project files to your web server document root
2. Ensure proper permissions:
   ```bash
   chmod 755 /path/to/midconnect
   chmod 644 /path/to/midconnect/* -R
   ```

### Step 4: Testing
1. Access the application: `http://localhost/midconnect/`
2. Use demo credentials:
   - **Midwife Login**: Employee ID: `MW001`, Password: `password123`
   - **Admin Login**: Username: `admin001`, Password: `admin123456`

## 🔐 Default Users

### Midwives
| Employee ID | Password | Name | Area |
|-------------|----------|------|------|
| MW001 | password123 | Madhavi Perera | Colombo Central |
| MW002 | password123 | Kumari Silva | Colombo North |
| MW003 | password123 | Anura Fernando | Colombo South |

### Administrators
| Username | Password | Name | MOH Office |
|----------|----------|------|------------|
| admin001 | admin123456 | Dr. Sarah Johnson | MOH Colombo 01 |
| admin002 | admin123456 | Dr. Kumara Silva | MOH Gampaha |

## 📊 Database Schema

### Core Tables
- **moh_admins**: MOH administrator users
- **midwives**: Midwife users and profiles
- **activities**: Daily activity records
- **activity_types**: Predefined activity categories
- **schedules**: Appointment and schedule management
- **patients**: Patient information
- **reports**: Generated reports
- **performance_metrics**: Performance tracking data
- **notifications**: System notifications
- **audit_logs**: Activity audit trail

### Key Relationships
- Midwives belong to MOH offices and have supervisors
- Activities are linked to midwives and activity types
- Patients are assigned to midwives
- Reports track midwife performance over time

## 🎨 Design System

### Color Palette
- **Primary Blue**: #0056b3 (Medical professionalism)
- **Secondary Green**: #28a745 (Health and growth)
- **Accent Teal**: #17a2b8 (Trust and reliability)
- **Warning Orange**: #fd7e14 (Attention)
- **Danger Red**: #dc3545 (Alerts)

### Typography
- **Primary Font**: Segoe UI, system fonts
- **Headings**: Semi-bold (600)
- **Body Text**: Regular (400)

## 🔧 API Endpoints

### Authentication
- `POST /php/midwife_login.php` - Midwife login
- `POST /php/admin_login.php` - Administrator login
- `POST /php/logout.php` - User logout

### Activities
- `POST /php/create_activity.php` - Create new activity
- `GET /php/get_activities.php` - Retrieve activities
- `PUT /php/update_activity.php` - Update activity
- `DELETE /php/delete_activity.php` - Delete activity

### Reports
- `POST /php/generate_report.php` - Generate reports
- `GET /php/get_reports.php` - Retrieve reports

## 📱 Mobile Responsiveness

The system is fully responsive and optimized for:
- **Desktop**: Full-featured dashboard experience
- **Tablet**: Touch-optimized navigation
- **Mobile**: Streamlined interface for field use

## 🛡️ Security Features

### Data Protection
- All user inputs are sanitized and validated
- Passwords are hashed using bcrypt
- SQL injection prevention with prepared statements
- XSS protection with output encoding

### Session Security
- Automatic session timeout
- Session ID regeneration on login
- Secure session cookies
- Protection against session fixation

### Audit Trail
- All user actions are logged
- IP address and user agent tracking
- Failed login attempt monitoring
- Data change tracking

## 🔍 Performance Optimization

- Database indexes for optimal query performance
- Prepared statements for efficiency
- Image optimization and caching
- Minified CSS and JavaScript (production ready)

## 📈 Reporting Features

### Built-in Reports
- Daily activity summaries
- Weekly performance reports
- Monthly statistics
- Custom date range reports
- Midwife performance comparisons

### Export Options
- PDF reports for official documentation
- CSV exports for data analysis
- Real-time dashboard widgets

## 🌐 Browser Compatibility

- **Chrome**: Full support (recommended)
- **Firefox**: Full support
- **Safari**: Full support
- **Edge**: Full support
- **Mobile Browsers**: Optimized experience

## 🚀 Future Enhancements

### Planned Features
- Mobile application (iOS/Android)
- GPS tracking integration
- Offline capability with sync
- Advanced analytics and ML insights
- Multi-language support (Sinhala/Tamil)
- Integration with national health systems

### Technical Improvements
- API rate limiting
- Advanced caching strategies
- Real-time notifications (WebSockets)
- Automated backup systems
- Load balancing support

## 📝 License

This project is developed for the Sri Lankan Ministry of Health as part of the digital transformation initiative for maternal and child healthcare services.

## 👥 Support

For technical support or questions:
- **Email**: support@midconnect.lk
- **Phone**: +94 11 2 691 757
- **Documentation**: Available in the system help section

## 🏥 Ministry of Health Integration

This system is designed to integrate with existing MOH systems and follows Sri Lankan healthcare data standards and protocols. All data handling complies with local healthcare regulations and privacy requirements.

---

**MidConnect** - Empowering Sri Lankan midwives with digital tools for better community healthcare delivery.