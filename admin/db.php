<?php
// db.php — configure your database connection here.
// ⚠️ Replace credentials with yours.
$DB_HOST = '127.0.0.1';
$DB_PORT = '3306';
$DB_NAME = 'restaurant';
$DB_USER = 'root';
$DB_PASS = ''; 

// DSN + PDO options
$dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (Throwable $e) {
    http_response_code(500);
    die("DB error: " . htmlspecialchars($e->getMessage()));
}

// (optional) quick helper
function db()
{
    global $pdo;
    return $pdo;
}

/*
SQL quick start (run once)
---------------------------------
CREATE TABLE IF NOT EXISTS dishes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(150) NOT NULL,
  description TEXT,
  prix DECIMAL(10,2) NOT NULL DEFAULT 0,
  categorie ENUM('Entrée','Plat','Dessert','Boisson') NOT NULL,
  image_url VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL
);
-- insert demo (password = admin123)
INSERT IGNORE INTO admins(email,password_hash)
VALUES ('admin@example.com', '$2y$10$8vYh3k1yW7c0i9wTtZ3QX.WsY0i6z6vK7F9d2mQz6aKqQf0XCPbH6');
*/
