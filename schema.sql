-- Health Logger schema (MySQL)
CREATE DATABASE IF NOT EXISTS health_logger CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE health_logger;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS health_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  mood TINYINT NOT NULL,
  note TEXT NULL,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  sleep_bed_time TIME NULL,
  sleep_wake_time TIME NULL,
  symptom_intensity TINYINT DEFAULT 0,
  symptom_duration VARCHAR(50) NULL,
  symptom_locations TEXT NULL,
  medication_log TEXT NULL,
  diet_log TEXT NULL,
  water_intake_oz INT DEFAULT 0,
  exercise_type VARCHAR(50) DEFAULT NULL,
  exercise_duration INT DEFAULT 0,
  CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- admin / admin (bcrypt hash)
INSERT INTO users (username, password_hash, role)
VALUES ('admin', '$2y$10$3vcd5QTvgl5yUT6p28L0Tuhfdn5qLIRX28vLkpgH0o6Xg8B.0j4li', 'admin')
ON DUPLICATE KEY UPDATE username=username;
