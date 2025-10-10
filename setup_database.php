<?php
$host = 'localhost';
$dbname = 'u420143207_LM_landing';
$username = 'u420143207_lmlanding';
$password = 'Levelminds@2024';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created or already exists.\n";

    $pdo->exec("USE `$dbname`");

    $pdo->exec("DROP TABLE IF EXISTS blog_likes, blog_posts, admin_users, newsletter_subscribers, contact_messages, registrations");
    echo "Existing tables dropped.\n";

    $pdo->exec(<<<SQL
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','editor') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    $pdo->exec(<<<SQL
CREATE TABLE blog_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NOT NULL DEFAULT 'LevelMinds Team',
  summary VARCHAR(500) NOT NULL,
  content LONGTEXT NOT NULL,
  media_type ENUM('photo','video') NOT NULL DEFAULT 'photo',
  media_url VARCHAR(500) NOT NULL,
  category ENUM('teachers','schools','general') NOT NULL DEFAULT 'general',
  views INT NOT NULL DEFAULT 0,
  likes INT NOT NULL DEFAULT 0,
  responses INT NOT NULL DEFAULT 0,
  status ENUM('draft','published') DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  INDEX idx_media_type (media_type),
  INDEX idx_status (status),
  INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS blog_likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  visitor_token VARCHAR(64) NOT NULL,
  email VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_like (post_id, visitor_token),
  KEY idx_token (visitor_token),
  KEY idx_email (email),
  CONSTRAINT fk_blog_likes_post
    FOREIGN KEY (post_id)
    REFERENCES blog_posts(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    $pdo->exec(<<<SQL
CREATE TABLE registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  mobile VARCHAR(20) NOT NULL,
  role ENUM('technician','business') NOT NULL,
  specialization VARCHAR(100) NULL,
  company VARCHAR(255) NULL,
  technician_count ENUM('1-5','6-15','16-50','50+') NULL,
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    $pdo->exec(<<<SQL
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    $pdo->exec(<<<SQL
CREATE TABLE newsletter_subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  status ENUM('active','unsubscribed') DEFAULT 'active',
  subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  unsubscribed_at TIMESTAMP NULL,
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    $stmt = $pdo->prepare('INSERT IGNORE INTO admin_users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $stmt->execute(['support@levelminds.in', 'support@levelminds.in', password_hash('Adminpassword@Levelminds@2023', PASSWORD_DEFAULT), 'admin']);
    echo "Default admin ensured.\n";

    echo "Setup complete.\n";
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database setup failed: ' . $e->getMessage();
}
?>








