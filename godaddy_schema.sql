-- Letters Max - GoDaddy MySQL Database Schema

-- 1. Table: questions
CREATE TABLE IF NOT EXISTS `questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `letter` VARCHAR(5) NOT NULL,
  `question` TEXT NOT NULL,
  `answer` VARCHAR(255) NOT NULL,
  INDEX (`letter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: admin_passcodes
CREATE TABLE IF NOT EXISTS `admin_passcodes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) UNIQUE NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: rooms
CREATE TABLE IF NOT EXISTS `rooms` (
  `room_code` VARCHAR(10) PRIMARY KEY,
  `admin_id` VARCHAR(100),
  `game_state` LONGTEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table: room_players
CREATE TABLE IF NOT EXISTS `room_players` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_code` VARCHAR(10) NOT NULL,
  `client_id` VARCHAR(100) NOT NULL,
  `name` VARCHAR(100),
  `team` VARCHAR(20),
  `is_admin` TINYINT(1) DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE (`room_code`, `client_id`),
  FOREIGN KEY (`room_code`) REFERENCES `rooms`(`room_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Admin Passcode (Valid for 1 year)
INSERT INTO `admin_passcodes` (`code`, `start_date`, `end_date`) 
VALUES ('1212', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))
ON DUPLICATE KEY UPDATE end_date = VALUES(end_date);
