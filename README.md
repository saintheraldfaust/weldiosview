# weldios university Certificate Verification Portal

A modern, secure certificate verification system for weldios university built with PHP and MySQL.

## Features

### 🎓 Student Management
- Complete student enrollment system
- Certificate number generation
- Profile URL generation for QR codes
- Student data management (CRUD operations)

### 🔍 Certificate Verification
- Real-time certificate verification
- QR code generation for student profiles
- Beautiful, modern UI with glassmorphism design
- Mobile-responsive design

### 👨‍💼 Admin Panel
- Secure admin authentication
- Student records management
- Dashboard with statistics
- Modern admin interface

### 🎨 Modern UI/UX
- Glassmorphism design elements
- Gradient backgrounds and modern styling
- Bootstrap 5 integration
- Font Awesome icons
- Responsive design for all devices

## Installation

### Prerequisites
- XAMPP (Apache, MySQL, PHP)
- Web browser

### Setup Instructions

1. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database schema:
     - Go to the "Import" tab
     - Select the file: `database/setup.sql`
     - Click "Go" to import

3. **File Placement**
   - The project is already in the correct location: `c:\xampp\htdocs\weldios`

4. **Access the Application**
   - Verification Portal: http://localhost/weldios/
   - Admin Panel: http://localhost/weldios/admin/login.php

### Default Admin Credentials
- **Username:** admin
- **Password:** admin123

## Project Structure

```
weldios/
├── config/
│   └── config.php          # Database and app configuration
├── database/
│   └── setup.sql          # Database schema and sample data
├── admin/
│   ├── login.php          # Admin login page
│   ├── dashboard.php      # Admin dashboard
│   └── logout.php         # Admin logout
├── index.php              # Main verification portal
└── profile.php            # Student profile page
```

## Usage

### For Visitors (Certificate Verification)
1. Visit the main portal: http://localhost/weldios/
2. Enter a certificate number (e.g., WLD/2024/001)
3. Click "Verify Certificate"
4. View the verification results with student details
5. Scan the QR code to access the complete student profile

### For Administrators
1. Login at: http://localhost/weldios/admin/login.php
2. Use credentials: admin / admin123
3. Manage student records from the dashboard
4. Add new students with complete information
5. View student profiles and delete records as needed

## Student Data Fields

The system captures the following student information:

### Personal Information
- Surname, First Name, Middle Name
- Matriculation/Registration Number

### Academic Information
- Programme Type (Undergraduate, Diploma, Graduate, Certificate)
- Programme Title (e.g., Bachelor of Science in Computer Science)
- Department/Course
- Class of Degree (optional)
- Year of Graduation

### System Generated
- Certificate Number (unique identifier)
- Profile URL (for QR code generation)
- Timestamps (created/updated)

## Programme Types Supported

### Undergraduate Degrees
- BSc, BA, BBA, B.Eng

### Diplomas
- Diploma, Advanced Diploma, Postgraduate Diploma, Graduate Diploma

### Graduate Degrees
- PGD, MSc, MA, MBA, MPA, DBA, PhD, M.Phil.

### Certificates
- Proficiency Certificate, Professional Certificate, Certificate of Programme Completion, Graduate Certificate

## Security Features

- Password hashing for admin accounts
- Input sanitization and validation
- Session management
- CSRF protection
- SQL injection prevention using prepared statements

## Technical Details

### Technologies Used
- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** Bootstrap 5
- **Icons:** Font Awesome 6
- **QR Codes:** QRCode.js library

### Key Features
- Responsive design for mobile and desktop
- Modern glassmorphism UI design
- Real-time form validation
- Animated transitions and effects
- Print-friendly student profiles

## Sample Data

The system comes with sample student records for testing:

- **Certificate:** WLD/2024/001 - Michael David Johnson (Computer Science)
- **Certificate:** WLD/2024/002 - Sarah Jane Smith (MBA)
- **Certificate:** WLD/2023/003 - James Robert Brown (Mechanical Engineering)

## Customization

### Styling
- Modify CSS custom properties in the `:root` selector to change colors
- Update Bootstrap classes for layout modifications
- Customize gradients and effects in the stylesheets

### Database
- Add additional fields to the students table as needed
- Modify the forms and display logic accordingly
- Update validation rules in the PHP code

### Branding
- Update school name and logo references
- Modify headers and footer content
- Customize email templates and messages

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `config/config.php`
   - Verify database name exists

2. **Admin Login Issues**
   - Use default credentials: admin / admin123
   - Check if database tables exist
   - Verify session configuration

3. **Certificate Not Found**
   - Ensure sample data is imported
   - Check certificate number format
   - Verify database connection

### Support
For technical support or questions, please contact the development team.

## License

This project is developed for weldios university. All rights reserved.

---

**weldios university Certificate Verification Portal**  
Modern, Secure, and User-Friendly