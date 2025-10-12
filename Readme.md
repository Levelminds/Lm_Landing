# TechvReach - Connect. Work. Grow.

A modern platform connecting skilled technicians with business opportunities and vendors with job opportunities for their technician teams.

> **Workspace rebuild update:** This branch refreshes the local environment setup notes to ensure contributors can reliably recreate the project workspace from the repository contents.

## 🚀 Quick Start (Pre-configured for Hostinger)

**All files are ready to deploy! Just:**
1. Upload all files to your Hostinger `public_html` folder
2. Run the SQL script in phpMyAdmin (copy from `create_table.sql`)
3. Your site is live!

**Database Settings (Already Configured):**
- Database: `u562321019_techvreach_db2`
- Username: `u562321019_techvreach2`
- Password: `9Zi7$0CkJ0`

## Business Model

### Two User Types:

**🔧 Technicians**
- Individual skilled technicians looking for job opportunities
- Register with their specialization and skills
- Get matched with relevant job opportunities

**🏢 Businesses (Vendors)**
- Companies/vendors with multiple technicians under them
- Register their company and number of technicians
- Get bulk job opportunities for their technician teams

## Design & Features

### Color Scheme
- **Primary Green**: #08503F
- **Primary Teal**: #0A6765  
- **Accent Lime**: #C0E919
- **Main Gradient**: #08503F to #0A6765 (Green to Teal)
- **Background Gradient**: #252C2A to #8B9092

### Typography
- **Font Family**: Chakra Petch (Google Fonts)
- Applied to all headings, buttons, and navigation elements

### Key Features
- **Unified Registration**: Both user types have similar forms with role-specific fields
- **Specialization-based Matching**: All users specify their technical specialization
- **Responsive Design**: Works perfectly on all devices
- **Fixed White Header**: Always visible navigation with single logo (no scroll animations)
- **Navigation**: Dark text (#252C2A) on white background for better readability
- **Modern UI**: Clean design with custom gradient backgrounds using green-teal theme
- **Admin Panel**: View and manage registration data with status tracking

### Form Fields

**Common Fields (Both Roles):**
- Full Name
- Email Address
- Mobile Number
- Technical Specialization

**Business-Specific Additional Fields:**
- Company Name
- Number of Technicians (1-5, 6-15, 16-50, 50+)

# Complete Hostinger Deployment Guide

## Step 1: Prepare Your Files

1. **Download all files** from your project
2. **Create a ZIP file** containing all website files:
   - index.html
   - register.php
   - admin.php
   - export.php
   - create_table.sql
   - assets/ folder (with all CSS, JS, images)

## Step 2: Hostinger Account Setup

1. **Purchase Hostinger hosting plan** (Premium or Business recommended)
2. **Register domain** or connect existing domain
3. **Access Hostinger control panel** (hPanel)

## Step 3: Upload Website Files

1. **Login to hPanel**
2. **Go to File Manager**
3. **Navigate to public_html folder**
4. **Upload your ZIP file**
5. **Extract the ZIP file** in public_html
6. **Ensure index.html is in the root** of public_html

## Step 4: Database Setup

### Database Configuration:
**Your Hostinger Database Details:**
- **Database name**: `u562321019_techvreach_db2`
- **Username**: `u562321019_techvreach2`
- **Password**: `9Zi7$0CkJ0`
- **Host**: `localhost`

### Create Table:
1. **Go to phpMyAdmin** (in Databases section)
2. **Select your database**: `u562321019_techvreach_db2`
3. **Go to SQL tab**
4. **Copy and paste this SQL**:

```sql
-- Use the correct database
USE u562321019_techvreach_db2;

CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mobile VARCHAR(20) NOT NULL,
    role ENUM('technician', 'business') NOT NULL,
    
    -- Common field for both roles
    specialization VARCHAR(100) NULL COMMENT 'Technical specialization: it-support, network, security, maintenance, cloud, hardware, software, other',
    
    -- Business specific fields
    company VARCHAR(255) NULL COMMENT 'Company name for business registrations',
    technician_count ENUM('1-5', '6-15', '16-50', '50+') NULL COMMENT 'Number of technicians under business',
    
    -- Common optional field
    message TEXT NULL COMMENT 'Additional message/requirements from user',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes for better performance
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_specialization (specialization),
    INDEX idx_technician_count (technician_count),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User registrations for technicians and businesses';

-- Verify table creation
DESCRIBE registrations;
```

5. **Click "Go" to execute**

## Step 5: Configure PHP Files

### Update register.php:
```php
// Database configuration - Use these exact credentials
$host = 'localhost';
$dbname = 'u562321019_techvreach_db2';
$username = 'u562321019_techvreach2';
$password = '9Zi7$0CkJ0';
```

### Update admin.php:
```php
// Database configuration - Use these exact credentials
$host = 'localhost';
$dbname = 'u562321019_techvreach_db2';
$username = 'u562321019_techvreach2';
$password = '9Zi7$0CkJ0';
```

### Update setup_database.php:
```php
// Database configuration - Use these exact credentials
$host = 'localhost';
$dbname = 'u562321019_techvreach_db2';
$username = 'u562321019_techvreach2';
$password = '9Zi7$0CkJ0';
```

## Step 6: Test Your Website

1. **Visit your domain**: `https://yourdomain.com`
2. **Test registration**: Try registering as both technician and business
3. **Check admin panel**: Visit `https://yourdomain.com/admin.php`
4. **Verify database**: Check if registrations are being saved

## Step 7: Security & Maintenance

### Secure Admin Panel:
1. **Rename admin.php** to something unique like `admin_panel_xyz123.php`
2. **Add password protection** to admin files
3. **Use HTTPS** (Hostinger provides free SSL)

### Regular Backups:
1. **Enable automatic backups** in Hostinger
2. **Download database backups** regularly
3. **Export registration data** periodically

## Troubleshooting

### Common Issues:

**Database Connection Error:**
- Check database credentials
- Ensure database exists
- Verify user permissions

**Registration Not Working:**
- Check PHP error logs in hPanel
- Verify table structure
- Test database connection

**Admin Panel Not Loading:**
- Check file permissions
- Verify PHP version compatibility
- Check error logs

## Quick Setup (All Files Pre-configured)

**All files are already configured with the correct database settings:**
- Database: `u562321019_techvreach_db2`
- Username: `u562321019_techvreach2`
- Password: `9Zi7$0CkJ0`

**Just upload and run the SQL script - no configuration needed!**

## File Structure After Upload

```
public_html/
├── index.html              # Main website
├── register.php           # Registration API (✅ Pre-configured)
├── admin.php              # Admin panel (✅ Pre-configured)
├── setup_database.php     # Automated database setup (✅ Pre-configured)
├── create_table.sql       # Manual database setup (✅ Pre-configured)
└── assets/
    ├── css/
    │   ├── style.css      # (✅ Header updated to black)
    │   └── custom.css
    ├── js/
    └── images/
```

## Access Points

- **Website**: `https://yourdomain.com`
- **Admin Panel**: `https://yourdomain.com/admin.php`
- **Registration API**: `https://yourdomain.com/register.php`
- **CSV Export**: `https://yourdomain.com/export.php`

## Support

If you encounter issues:
1. Check Hostinger knowledge base
2. Contact Hostinger support
3. Check PHP error logs in hPanel
4. Verify database connection and permissions

## Contact Information

- **Email**: support@techvreach.com
- **Phone**: +1 (800) TECHVREACH
- **Address**: 🇺🇸 3380 Country Village Road, Riverside, CA 92509 #5312, United States
- **Company**: TechvReach

## Developer Utilities

### Reset workspace cache

If you need to clear cached artifacts while working locally, run:

```bash
php scripts/reset_workspace_cache.php
```

The helper script removes files inside common cache directories (if they exist) and rewrites `data/blogs.json` to an empty array so subsequent requests start from a clean state.
