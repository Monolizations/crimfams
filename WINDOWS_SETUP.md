# Windows Setup Guide for CRIM Faculty Attendance and Monitoring System

This guide will help you set up the CRIM Faculty Attendance and Monitoring System on Windows using XAMPP.

## Prerequisites

- Windows 10 or later
- XAMPP (Apache, MySQL, PHP)

## Installation Steps

### 1. Install XAMPP

1. Download XAMPP from the official website: https://www.apachefriends.org/download.html
2. Choose the Windows version and download the installer
3. Run the installer and follow the installation wizard
4. Install XAMPP in the default location: `C:\xampp\`

### 2. Start XAMPP Services

1. Open XAMPP Control Panel from the Start menu
2. Start the following services:
   - Apache (web server)
   - MySQL (database server)

### 3. Deploy the Application

1. Copy the entire project folder to `C:\xampp\htdocs\`
2. Rename the folder to something simple, e.g., `fams` (so the path becomes `C:\xampp\htdocs\fams`)

### 4. Set Up the Database

1. Open your web browser and go to: http://localhost/phpmyadmin
2. Click on "New" in the left sidebar
3. Create a new database named `fams`
4. Select the `fams` database from the left sidebar
5. Click on the "Import" tab
6. Click "Choose File" and select the `database/database.sql` file from your project
7. Click "Go" to import the database schema and sample data

### 5. Access the Application

1. Open your web browser
2. Navigate to: http://localhost/fams/public/
3. The application should now be running

## Default Login Credentials

The database comes with pre-configured test users:

- **Admin**: username `admin`, password `admin123`
- **Faculty**: username `faculty1`, password `password`
- **Secretary**: username `secretary1`, password `password`
- **Program Head**: username `programhead1`, password `password`

## Troubleshooting

### Apache Won't Start
- Make sure no other web servers (like IIS) are running on port 80
- Check if Skype or other applications are using port 80

### MySQL Won't Start
- Make sure no other MySQL instances are running
- Check if port 3306 is available

### Database Connection Issues
- Verify the database name is `fams`
- Check that MySQL service is running
- Ensure the database was imported correctly

### Permission Issues
- Make sure XAMPP is run as Administrator
- Check that the project files are in the correct location

## Features

This system includes:
- User authentication and role-based access
- Faculty schedule management
- Attendance tracking
- Leave request system
- QR code generation and scanning
- Reports and analytics
- Dashboard for different user roles

## Support

If you encounter any issues during setup, please check:
1. XAMPP control panel for service status
2. Apache error logs in `C:\xampp\apache\logs\error.log`
3. MySQL error logs in `C:\xampp\mysql\data\mysql_error.log`