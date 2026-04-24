<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/db.php";      // gives $pdo
require_once __DIR__ . "/../../config/auth_guard.php";   // if you use it for ADMIN check (keep)
require_admin();
$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid JSON body"]);
  exit;
}

$name = trim($input["name"] ?? "");
$description = trim($input["description"] ?? "");
$image_url = trim($input["image_url"] ?? "");
$category = strtolower(trim($input["category"] ?? ""));

$price = $input["price"] ?? null;
$calories = $input["calories"] ?? null;
$protein = $input["protein"] ?? null;

if ($name === "" || $category === "" || $price === null || $calories === null || $protein === null) {
  http_response_code(400);
  echo json_encode(["error" => "name, category, price, calories, protein are required"]);
  exit;
}

try {
  $stmt = $pdo->prepare(
    "INSERT INTO products (name, description, image_url, price, calories, protein, category)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
  );

  $stmt->execute([
    $name,
    $description,
    $image_url,
    floatval($price),
    intval($calories),
    intval($protein),
    $category
  ]);

  echo json_encode([
    "message" => "Product created",
    "id" => intval($pdo->lastInsertId())
  ]);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    "error" => "Server error while creating product",
    "details" => $e->getMessage()
  ]);
  exit;
}