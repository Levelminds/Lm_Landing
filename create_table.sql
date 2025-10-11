CREATE DATABASE IF NOT EXISTS LM_landing
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE LM_landing;

DROP TABLE IF EXISTS blog_posts;
DROP TABLE IF EXISTS admin_users;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS registrations;

CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','editor') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE blog_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NOT NULL DEFAULT 'LevelMinds Team',
  summary VARCHAR(500) NOT NULL,
  content LONGTEXT NOT NULL,
  media_type ENUM('photo','video') NOT NULL DEFAULT 'photo',
  media_url VARCHAR(500) NOT NULL,
  views INT NOT NULL DEFAULT 0,
  likes INT NOT NULL DEFAULT 0,
  responses INT NOT NULL DEFAULT 0,
  status ENUM('draft','published') DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  INDEX idx_media_type (media_type),
  INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  mobile VARCHAR(20) NOT NULL,
  role ENUM('technician', 'business') NOT NULL,
  specialization VARCHAR(100) NULL,
  company VARCHAR(255) NULL,
  technician_count ENUM('1-5', '6-15', '16-50', '50+') NULL,
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role)
) ENGINE=InnoDB;

CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  subject VARCHAR(500) NULL,
  message TEXT NOT NULL,
  status ENUM('new','read','replied','closed') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE newsletter_subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  status ENUM('active','unsubscribed') DEFAULT 'active',
  subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  unsubscribed_at TIMESTAMP NULL,
  INDEX idx_status (status)
) ENGINE=InnoDB;

INSERT INTO admin_users (username, email, password_hash, role)
VALUES ('support@levelminds.in', 'support@levelminds.in', SHA2('Adminpassword@Levelminds@2023', 256), 'admin');


