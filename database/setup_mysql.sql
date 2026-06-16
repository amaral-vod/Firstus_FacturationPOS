-- Configuration MySQL / MariaDB pour Firstus_FacturationPOS
-- Exécuter : sudo mysql < database/setup_mysql.sql

CREATE DATABASE IF NOT EXISTS firstus_pos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'firstus'@'localhost' IDENTIFIED BY 'firstus123';
ALTER USER 'firstus'@'localhost' IDENTIFIED BY 'firstus123';
GRANT ALL PRIVILEGES ON firstus_pos.* TO 'firstus'@'localhost';
FLUSH PRIVILEGES;
