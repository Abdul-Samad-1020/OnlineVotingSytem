-- Create database
CREATE DATABASE fitness_tracker;
USE fitness_tracker;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Activities table
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    duration_minutes INT NOT NULL,
    distance_km DECIMAL(5,2),
    calories INT,
    activity_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Workouts table
CREATE TABLE workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    workout_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Exercises table
CREATE TABLE exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workout_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    sets INT,
    reps INT,
    weight_kg DECIMAL(5,2),
    duration_minutes INT,
    notes TEXT,
    FOREIGN KEY (workout_id) REFERENCES workouts(id)
);

-- Meals table
CREATE TABLE meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    meal_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    calories INT NOT NULL,
    protein_g DECIMAL(5,2),
    carbs_g DECIMAL(5,2),
    fat_g DECIMAL(5,2),
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Sleep records table
CREATE TABLE sleep (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    quality TINYINT(1) DEFAULT 3,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Body measurements table
CREATE TABLE body_measurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    record_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    weight_kg DECIMAL(5,2),
    height_cm DECIMAL(5,2),
    chest_cm DECIMAL(5,2),
    waist_cm DECIMAL(5,2),
    hips_cm DECIMAL(5,2),
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);