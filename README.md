# ScamShield 🕴

**Fraud Reporting & Awareness Hub**

ScamShield is a comprehensive web-based platform designed to help users report scams, share fraud experiences, and stay informed about the latest scam trends. The platform provides a centralized database of verified scam reports, educational resources, and a community-driven approach to fraud prevention. Regardless of whether you're a victim of a scam or simply want to stay informed, ScamShield empowers you to take action and protect yourself and others.

## Features

### For Users

- **Scam Reporting System**: Submit detailed scam reports with evidence and contact information
- **Anonymous Reporting**: Option to report scams anonymously while still contributing to the community
- **Scam Database**: Browse and search through verified scam reports with advanced filtering
- **Awareness Hub**: Access educational blog posts about fraud prevention and scam awareness
- **User Profiles**: Create an account to track your reports and contributions
- **Contact Support**: Reach out to the team for assistance or inquiries

### For Moderators

- **Report Management**: Review, verify, and update scam reports
- **Message Management**: Handle user inquiries and support requests
- **Blog Post Management**: Create and manage awareness content

### For Administrators

- **User Management**: Manage user accounts, roles, and permissions
- **Report Oversight**: Full control over scam reports and verification
- **Category Management**: Organize scam types and categories
- **Message Handling**: Prioritize and respond to contact messages
- **Blog Post Control**: Moderate and manage all awareness content
- **Activity Logs**: Track administrative actions and system changes

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7+
- **Database**: MySQL
- **Server**: Apache/Nginx with PHP support

## 📋 Database Schema

The application uses a comprehensive database with the following tables:

- `users` - User accounts with role-based access (user, moderator, admin)
- `scam_reports` - Detailed scam reports with evidence and status tracking
- `scam_categories` - Organized categories for different types of scams
- `report_evidence` - File attachments and evidence for reports
- `contact_messages` - User inquiries and support requests
- `blog_posts` - Educational content and awareness articles
- `notifications` - Newsletter subscriptions
- `admin_activity_logs` - Audit trail for administrative actions
- `report_status_history` - Track changes in report status
- `admin_notes` - Internal notes for reports, users, and messages

## 📦 Installation

### Prerequisites

- PHP 7.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- phpMyAdmin (optional, for database management)

### Setup Instructions

1. **Clone the repository**

   ```bash
   git clone https://github.com/munnaa0/ScamShield.git
   cd ScamShield
   ```

2. **Configure the database**

   - Create a new MySQL database named `scamshield_db`
   - Import the database schema:
     ```bash
     mysql -u root -p scamshield_db < models/scamshield_database.sql
     ```

3. **Configure database connection**

   - Open `models/config.php`
   - Update the database credentials:
     ```php
     $db_host = "localhost";
     $db_user = "root";
     $db_pass = "your_password";
     $db_name = "scamshield_db";
     ```

4. **Set up the web server**

   - Place the project in your web server's document root (e.g., `htdocs` for XAMPP)
   - Ensure proper permissions for the web server to read the files

5. **Access the application**
   - Navigate to `http://localhost/ScamShield` in your web browser
   - The application will automatically redirect to the home page

## 📁 Project Structure

```
ScamShield/
├── controllers/          # Backend logic and form handlers
│   ├── contact.php      # Contact form submission
│   ├── login.php        # User authentication
│   ├── logout.php       # Session termination
│   ├── register.php     # New user registration
│   └── report.php       # Scam report submission
├── models/              # Database and utilities
│   ├── config.php       # Database configuration
│   ├── session_helper.php  # Session management
│   ├── scamshield_database.sql  # Database schema
│   └── css/             # Stylesheets
│       ├── about.css
│       ├── admin_dashboard.css
│       ├── awareness.css
│       ├── contact.css
│       ├── database.css
│       ├── home.css
│       ├── login.css
│       ├── moderator_dashboard.css
│       ├── profile.css
│       ├── register.css
│       ├── report_detail.css
│       └── report.css
├── views/               # Frontend pages
│   ├── about.php        # About page
│   ├── awareness.php    # Blog and awareness content
│   ├── blog_detail.php  # Individual blog post view
│   ├── database.php     # Scam database browser
│   ├── home.php         # Landing page
│   ├── profile.php      # User profile
│   ├── report_detail.php  # Detailed scam report view
│   ├── admin/           # Admin panel
│   │   ├── admin_dashboard.php
│   │   ├── manage_blog_posts.php
│   │   ├── manage_categories.php
│   │   ├── manage_messages.php
│   │   ├── manage_reports.php
│   │   └── manage_users.php
│   └── moderator/       # Moderator panel
│       ├── moderator_dashboard.php
│       ├── manage_blog_posts.php
│       ├── manage_messages.php
│       └── manage_reports.php
├── images/              # Image assets
├── index.php            # Entry point
└── LICENSE              # Project license
```

## 🔒 Security Features

- **Password Hashing**: User passwords are securely hashed before storage
- **Role-Based Access Control**: Three-tier access system (User, Moderator, Admin)
- **Session Management**: Secure session handling with helper functions
- **Anonymous Reporting**: Privacy-focused reporting option
- **User Banning**: Administrative ability to ban malicious users
- **Activity Logging**: Comprehensive audit trail for administrative actions

## 🎯 Key Functionalities

### Scam Reporting

- Multi-step form with validation
- Support for various scam categories
- Evidence upload capability
- Financial loss tracking
- Location information (victim and scammer)
- External reporting tracking (police, FBI, FTC, etc.)

### Report Management

- Status workflow: Pending → Verified/Investigating → Resolved/Rejected
- Admin review and notes
- Status history tracking
- Evidence management

### User Roles

- **User**: Submit reports, view database, read blog posts
- **Moderator**: Review reports, manage messages, create blog content
- **Admin**: Full system access, user management, category management

### Search and Filter

- Full-text search across reports
- Filter by category, status, date range
- Pagination for large result sets
- Advanced filtering options

## 📊 Statistics Tracking

The platform tracks and displays:

- Total number of scam reports
- Number of verified reports
- Users protected by the platform
- Recent scam activity
- Category-wise distribution

## 🤝 Contributing

This project is part of a Software Engineering course. For contributions or suggestions, please contact the development team.

## 📝 License

See the [LICENSE](LICENSE) file for details.

## 👥 User Roles Overview

### User Features

- Create and manage personal account
- Submit scam reports (authenticated or anonymous)
- Browse verified scam database
- Read awareness blog posts
- Contact support team

### Moderator Features

- All user features
- Review and verify scam reports
- Update report status
- Manage contact messages
- Create awareness blog posts

### Admin Features

- All moderator features
- Manage user accounts and roles
- Ban/unban users
- Create and manage scam categories
- Full report oversight
- View activity logs
- System-wide content management

## 🚦 Getting Started

1. Install the application using the setup instructions above
2. Create an admin account by directly inserting into the database:
   ```sql
   INSERT INTO users (username, email, password_hash, role)
   VALUES ('admin', 'admin@scamshield.com', 'hashed_password', 'admin');
   ```
3. Log in with admin credentials
4. Add scam categories through the admin panel
5. Start accepting and verifying scam reports

## 📞 Support

For technical support or inquiries about the platform, use the contact form available at `/views/contact.php` or reach out through the admin dashboard.

## 🔮 Future Enhancements

- Email notification system
- Advanced analytics dashboard
- API for third-party integrations
- Mobile responsive design improvements
- Multi-language support
- Evidence file upload and management
- Real-time reporting statistics

---

**ScamShield** - Protecting communities through shared knowledge and verified information.
