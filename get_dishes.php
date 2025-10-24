<?php
header('Content-Type: application/json; charset=utf-8');

// Use admin/db.php if you prefer a single connection point
$adminDb = __DIR__ . '/admin/db.php';
if (file_exists($adminDb)) {
    require $adminDb;
} else {
    // fallback: minimal connection (edit if you keep this path)
    $DB_HOST = '127.0.0.1';
    $DB_PORT = '3307';
    $DB_NAME = 'restaurant';
    $DB_USER = 'root';
    $DB_PASS = '';
    $dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}

try {
    $stmt = $pdo->query("SELECT id, nom, description, prix, categorie, image_url FROM dishes ORDER BY FIELD(categorie,'Entrée','Plat','Dessert','Boisson'), nom");
    echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    // On error, return empty list so frontend uses demo data
    echo json_encode([]);
}
