<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/db.php";

$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at ASC");
$products = $stmt->fetchAll();

echo json_encode($products);