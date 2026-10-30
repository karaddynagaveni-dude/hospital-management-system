-- ==========================================================
-- Hospital Management System - Database Schema
-- ==========================================================
-- Import this file in phpMyAdmin (or run via MySQL CLI) BEFORE
-- running the application. It creates the database, all tables,
-- and one default admin account.
-- ==========================================================

CREATE DATABASE IF NOT EXISTS hms_db;
USE hms_db;

-- --------------------------------------------------------
-- Table: admins  (Admin login)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin login -> username: admin  |  password: admin123
-- (password is hashed with PHP password_hash - bcrypt)
INSERT INTO admins (username, password, full_name)
VALUES ('admin', '$2b$10$b3ux33UHEFtQIcg0mfw4We/RkmWQwgW57t.MPoiUC.mfKE0QmeVzu', 'System Administrator')
ON DUPLICATE KEY UPDATE username = username;

-- --------------------------------------------------------
-- Table: patients (User login + patient records)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT,
    gender ENUM('Male','Female','Other'),
    address VARCHAR(255),
    contact_no VARCHAR(20),
    disease VARCHAR(150),
    admission_date DATE NULL,
    discharge_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Table: doctors
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100),
    contact_no VARCHAR(20),
    department VARCHAR(100),
    availability ENUM('Available','On Leave','Busy') DEFAULT 'Available'
);

-- --------------------------------------------------------
-- Table: staff
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    department VARCHAR(100),
    contact_no VARCHAR(20),
    shift_timing VARCHAR(50)
);

-- --------------------------------------------------------
-- Table: appointments
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
);

-- --------------------------------------------------------
-- Table: beds  (Bed / Ward allocation)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS beds (
    bed_id INT AUTO_INCREMENT PRIMARY KEY,
    ward_type VARCHAR(50) NOT NULL,
    status ENUM('Available','Occupied') DEFAULT 'Available',
    patient_id INT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE SET NULL
);

-- Seed a few beds so the Bed Management screen isn't empty on first run
INSERT INTO beds (ward_type, status) VALUES
('General Ward', 'Available'),
('General Ward', 'Available'),
('General Ward', 'Available'),
('ICU', 'Available'),
('ICU', 'Available'),
('Private Room', 'Available'),
('Private Room', 'Available'),
('Private Room', 'Available');

-- --------------------------------------------------------
-- Table: billing
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS billing (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    treatment_cost DECIMAL(10,2) DEFAULT 0,
    room_charges DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) GENERATED ALWAYS AS (treatment_cost + room_charges) STORED,
    payment_status ENUM('Paid','Unpaid','Partial') DEFAULT 'Unpaid',
    bill_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
);
