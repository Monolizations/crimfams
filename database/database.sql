-- Faculty Attendance and Monitoring System Database Schema

CREATE DATABASE IF NOT EXISTS fams;

USE fams;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'FACULTY', 'SECRETARY', 'PROGRAM_HEAD') NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT,
    course_code VARCHAR(50),
    room_number VARCHAR(50),
    start_time TIME,
    end_time TIME,
    date DATE,
    FOREIGN KEY (faculty_id) REFERENCES users(id)
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT,
    faculty_id INT,
    status ENUM('Present', 'Absent', 'Upcoming'),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id),
    FOREIGN KEY (faculty_id) REFERENCES users(id)
);

CREATE TABLE leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT,
    start_date DATE,
    end_date DATE,
    reason TEXT,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    admin_note TEXT,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES users(id)
);

-- Insert default users for testing
-- Password: admin123 for admin, password for others
INSERT INTO users (username, password, role, email) VALUES ('admin', '$2y$10$l8f4nJCvQjPNO0pBgSfAHuxbdsd8UHaCGGdcrbnQhplc9U2Xai0La', 'ADMIN', 'admin@fams.edu');
INSERT INTO users (username, password, role, email) VALUES ('faculty1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'FACULTY', 'faculty1@fams.edu');
INSERT INTO users (username, password, role, email) VALUES ('secretary1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SECRETARY', 'secretary1@fams.edu');
INSERT INTO users (username, password, role, email) VALUES ('programhead1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PROGRAM_HEAD', 'programhead1@fams.edu');

-- Sample schedules
INSERT INTO schedules (faculty_id, course_code, room_number, start_time, end_time, date) VALUES
(2, 'CS101', 'Room 101', '09:00:00', '10:30:00', CURDATE()),
(2, 'CS102', 'Room 102', '11:00:00', '12:30:00', CURDATE()),
(4, 'CS201', 'Room 201', '09:00:00', '10:30:00', CURDATE());

-- Sample leave requests
INSERT INTO leave_requests (faculty_id, start_date, end_date, reason, status) VALUES
(2, DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 16 DAY), 'Family vacation', 'Pending'),
(4, DATE_ADD(CURDATE(), INTERVAL 21 DAY), DATE_ADD(CURDATE(), INTERVAL 23 DAY), 'Medical appointment', 'Approved');