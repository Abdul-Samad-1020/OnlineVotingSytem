-- Create database
CREATE DATABASE IF NOT EXISTS voting_system;
USE voting_system;

-- Drop existing tables if they exist (in reverse order of dependencies)
DROP TABLE IF EXISTS votes;
DROP TABLE IF EXISTS candidates;
DROP TABLE IF EXISTS elections;
DROP TABLE IF EXISTS users;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('voter', 'admin') DEFAULT 'voter',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Elections table
CREATE TABLE elections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('upcoming', 'active', 'completed') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Candidates table
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    bio TEXT,
    photo VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
);

-- Votes table
CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    candidate_id INT NOT NULL,
    election_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (user_id, election_id)
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, password, email, role) 
VALUES ('admin', '$2y$10$8KzO3LOgpxoKSx0.jJAuCOPYwYqgQjAMoVviLHU8YC7.WyPy0/V9G', 'admin@example.com', 'admin');

-- Insert sample voter
INSERT INTO users (username, password, email, role)
VALUES ('voter1', '$2y$10$8KzO3LOgpxoKSx0.jJAuCOPYwYqgQjAMoVviLHU8YC7.WyPy0/V9G', 'voter1@example.com', 'voter');

-- Insert sample elections
INSERT INTO elections (title, description, start_date, end_date, status)
VALUES 
('Student Council Election 2023', 'Election for the student council representatives for the academic year 2023-2024.', 
 NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'active'),
('Department Head Election', 'Election for the new department head position.', 
 DATE_ADD(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 20 DAY), 'upcoming'),
('Faculty Board Election', 'Annual election for faculty board members.', 
 DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), 'completed');

-- Insert sample candidates
INSERT INTO candidates (election_id, name, position, bio, photo)
VALUES 
(1, 'John Smith', 'President', 'John is a third-year student with experience in leadership roles.', 'default.jpg'),
(1, 'Sarah Johnson', 'President', 'Sarah has been active in student organizations for two years.', 'default.jpg'),
(1, 'Michael Brown', 'Vice President', 'Michael is passionate about improving student facilities.', 'default.jpg'),
(2, 'Dr. Emily Wilson', 'Department Head', 'Dr. Wilson has 15 years of experience in the field.', 'default.jpg'),
(2, 'Dr. Robert Chen', 'Department Head', 'Dr. Chen is focused on curriculum innovation.', 'default.jpg'),
(3, 'Prof. James Taylor', 'Board Member', 'Prof. Taylor has served on multiple academic committees.', 'default.jpg'),
(3, 'Prof. Lisa Garcia', 'Board Member', 'Prof. Garcia specializes in educational policy.', 'default.jpg');

-- Insert sample votes for the completed election
INSERT INTO votes (user_id, candidate_id, election_id)
VALUES (2, 6, 3);