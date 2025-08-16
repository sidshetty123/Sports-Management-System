-- Create the database
CREATE DATABASE IF NOT EXISTS sms;
USE sms;

-- Table: admin (no dependencies)
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Pre-fill admin credentials
INSERT INTO admin (username, password) VALUES ('sid', 'abc');

-- Table: coaches (no dependencies)
CREATE TABLE coaches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phonenum VARCHAR(15) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Table: departments (no dependencies)
CREATE TABLE departments (
    dept_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(255) NOT NULL
);

-- Insert Departments Data
INSERT INTO departments (dept_name) VALUES
('CSE Department'),
('Engineering Department'),
('IT & CA Department'),
('Management Department'),
('Pharmacy Department'),
('Science Department'),
('Arts Department');

-- Table: courses (depends on departments)
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    dept_id INT NOT NULL,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE CASCADE
);

-- Insert Courses Data
INSERT INTO courses (course_name, duration, dept_id) VALUES
-- CSE Department
('CSE', '4 Years', 1),
('AI & ML', '4 Years', 1),
('Cyber Security', '4 Years', 1),
('Data Science', '4 Years', 1),
-- Engineering Department
('Biotechnology', '4 Years', 2),
('Civil Engineering', '4 Years', 2),
('Mechanical Engineering', '4 Years', 2),
('EEE', '4 Years', 2),
('Food Technology', '4 Years', 2),
('Chemical Engineering', '4 Years', 2),
('Biomedical Engineering', '4 Years', 2),
('Agricultural Engineering', '4 Years', 2),
('Automobile Engineering', '4 Years', 2),
('Bioinformatics', '4 Years', 2),
('ECE', '4 Years', 2),
('Petroleum Engineering', '4 Years', 2),
('Textile Technology', '4 Years', 2),
-- IT & CA Department
('Information Technology', '4 Years', 3),
('MCA', '3 Years', 3),
('BCA', '3 Years', 3),
-- Management Department
('MBA', '2 Years', 4),
('BBA', '3 Years', 4),
-- Pharmacy Department
('B.Pharmacy', '4 Years', 5),
-- Science Department
('B.Sc in Computer Science', '3 Years', 6),
('B.Sc in Statistics', '3 Years', 6),
('B.Sc in Mathematics', '3 Years', 6),
-- Arts Department
('Ph.D in Management', '3 Years', 7),
('Ph.D in English', '3 Years', 7),
('Ph.D in Engineering', '3 Years', 7),
('Ph.D in Science', '3 Years', 7);

-- Table: students (depends on departments and courses)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rollnum VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phonenum VARCHAR(15) NOT NULL,
    dept_id INT NOT NULL,
    course_id INT NOT NULL,
    sec VARCHAR(10) NOT NULL,
    year INT NOT NULL,
    password VARCHAR(255) NOT NULL,
    security_question VARCHAR(255) NOT NULL,
    security_answer VARCHAR(255) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

-- Table: sports_events (depends on coaches)
CREATE TABLE sports_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sport_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    rewards TEXT,
    coach_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_group_required BOOLEAN DEFAULT FALSE,
    max_group_members INT DEFAULT NULL,
    women BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (coach_id) REFERENCES coaches(id)
);

-- Insert Sports Events Data
INSERT INTO sports_events (name, sport_name, description, rewards, coach_id, start_date, end_date) VALUES 
('Inter-Departmental Football Championship', 'Football', 'A football championship between departments to promote teamwork and sportsmanship.', 'Trophy, Medals, Certificates', 1, '2024-01-15', '2024-01-20'),
('Annual Basketball Tournament', 'Basketball', 'An annual basketball tournament for students to showcase their skills.', 'Trophy, Medals, Certificates', 1, '2024-02-01', '2024-02-05'),
('Volleyball Winter Fest', 'Volleyball', 'A volleyball competition during the winter fest.', 'Gift Vouchers, Medals', 1, '2024-01-25', '2024-01-27'),
('Table Tennis League', 'Table Tennis', 'An intense table tennis league for singles and doubles.', 'Cash Prize, Certificates', 1, '2024-02-10', '2024-02-12'),
('Athletics Meet', 'Athletics', 'A multi-sport athletic event including running, long jump, and shot put.', 'Medals, Certificates', 1, '2024-03-01', '2024-03-03');

-- Table: achievements (depends on students)
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_rollnum VARCHAR(10) NOT NULL,
    event_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    medal VARCHAR(50),
    date DATE NOT NULL,
    FOREIGN KEY (student_rollnum) REFERENCES students(rollnum)
);

-- Table: event_results (depends on students)
CREATE TABLE event_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    student_rollnum VARCHAR(10),
    result TEXT NOT NULL,
    medal ENUM('gold', 'silver', 'bronze') DEFAULT NULL,
    event_date DATE NOT NULL,
    FOREIGN KEY (student_rollnum) REFERENCES students(rollnum)
);

-- Table: bmi (depends on students, though not explicitly via FK)
CREATE TABLE bmi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rollnum VARCHAR(10) NOT NULL,
    name VARCHAR(100) NOT NULL,
    branch VARCHAR(100) NOT NULL,
    section VARCHAR(10) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    scholar_status ENUM('Dayscholar', 'Hosteler') NOT NULL,
    year INT NOT NULL,
    height_cm DECIMAL(5, 2) NOT NULL,
    weight_kg DECIMAL(5, 2) NOT NULL,
    bmi_percentage DECIMAL(5, 2) NOT NULL,
    bmi_status VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: individual_event_registrations (depends on students and sports_events)
CREATE TABLE individual_event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_rollnum VARCHAR(10) NOT NULL,
    event_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_rollnum) REFERENCES students(rollnum),
    FOREIGN KEY (event_id) REFERENCES sports_events(id)
);

-- Table: group_event_registrations (depends on sports_events)
CREATE TABLE group_event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    team_name VARCHAR(50) NOT NULL UNIQUE,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES sports_events(id)
);

-- Table: group_event_participants (depends on group_event_registrations and students)
CREATE TABLE group_event_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    student_rollnum VARCHAR(10) NOT NULL,
    FOREIGN KEY (registration_id) REFERENCES group_event_registrations(id),
    FOREIGN KEY (student_rollnum) REFERENCES students(rollnum)
);

-- Table: event_fixtures (depends on sports_events)
CREATE TABLE event_fixtures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    participant1 VARCHAR(255) NOT NULL,
    participant2 VARCHAR(255) NOT NULL,
    round INT NOT NULL,
    FOREIGN KEY (event_id) REFERENCES sports_events(id)
);

-- Table: inventory (no dependencies)
CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL
);

-- Table: issued_items (no explicit FK dependencies, but logically tied to inventory)
CREATE TABLE issued_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_num VARCHAR(50) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    issue_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    return_time DATETIME DEFAULT NULL,
    is_returned BOOLEAN DEFAULT FALSE
);

-- Table: bill (no dependencies)
CREATE TABLE bill (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_no VARCHAR(255) UNIQUE,
    bill_description TEXT,
    amount DECIMAL(10, 2),
    date_of_expense DATE,
    payment_type ENUM('cash', 'online'),
    image_path VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
