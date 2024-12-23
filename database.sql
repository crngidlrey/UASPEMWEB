-- Membuat database
CREATE DATABASE library_db;

-- Gunakan database
USE library_db;

-- Membuat tabel students (formerly users)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    address TEXT NOT NULL,
    major VARCHAR(100) NOT NULL,
    entry_year YEAR NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Membuat tabel books dengan fields tambahan
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(50) NOT NULL UNIQUE,
    publication_year INT NOT NULL,
    genre VARCHAR(50) NOT NULL,
    publisher VARCHAR(100) NOT NULL,
    edition VARCHAR(50),
    pages INT NOT NULL,
    language VARCHAR(50) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    stock_quantity INT NOT NULL DEFAULT 1,
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);