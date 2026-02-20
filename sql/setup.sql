SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `domains` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `domain_name` VARCHAR(255) NOT NULL UNIQUE,
  `owner_email` VARCHAR(255) NOT NULL,
  `expiry_date` DATE NOT NULL,              
  `plan_type` ENUM('alap', 'pro', 'vps') DEFAULT 'alap', 
  `status` ENUM('aktív', 'lejárt', 'függőben') DEFAULT 'aktív',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `domains` (`domain_name`, `owner_email`, `expiry_date`, `plan_type`, `status`) VALUES 
('peldadomain.hu', 'info@peldadomain.hu', '2027-05-12', 'pro', 'aktív'),
('expired-test.com', 'user@test.hu', '2026-01-10', 'alap', 'lejárt');

COMMIT;