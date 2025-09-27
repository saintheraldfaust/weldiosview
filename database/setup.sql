-- weldios university Result Verification Portal Database Setup

CREATE DATABASE IF NOT EXISTS weldios_portal;
USE weldios_portal;

-- Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    surname VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    programme_type ENUM('undergraduate', 'diploma', 'graduate', 'certificate') NOT NULL,
    programme_title VARCHAR(200) NOT NULL,
    department VARCHAR(200) NOT NULL,
    class_of_degree VARCHAR(100),
    year_of_graduation YEAR NOT NULL,
    matriculation_number VARCHAR(50) UNIQUE NOT NULL,
    profile_url VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password, email) VALUES 
('admin', '$2y$10$x2ddkmTe0H6olxcQIL18OuNqObdFaLQQVmIYPgJ0ifP8iigM862x2', 'admin@weldios.university');

-- Sample students data
INSERT INTO students (certificate_number, surname, first_name, middle_name, programme_type, programme_title, department, class_of_degree, year_of_graduation, matriculation_number, profile_url) VALUES
('WLD/2024/001', 'Johnson', 'Michael', 'David', 'undergraduate', 'Bachelor of Science (Computer Science)', 'Computer Science', 'First Class', 2024, 'WLD/CS/2020/001', 'profile_647b2c8a9f1e3'),
('WLD/2024/002', 'Smith', 'Sarah', 'Jane', 'graduate', 'Master of Business Administration', 'Business Administration', 'Distinction', 2024, 'WLD/MBA/2022/001', 'profile_647b2c8a9f2e4'),
('WLD/2023/003', 'Brown', 'James', 'Robert', 'diploma', 'Advanced Diploma in Engineering', 'Mechanical Engineering', 'Upper Credit', 2023, 'WLD/ME/2021/002', 'profile_647b2c8a9f3e5');