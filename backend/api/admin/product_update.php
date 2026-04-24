<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/admin_guard.php";
require_once __DIR__ . "/../../config/db.php";

$input = json_decode(file_get_contents("php://input"), true);

$id = $input["id"] ?? null;
$name = trim($input["name"] ?? "");
$description = trim($input["description"] ?? "");
$image_url = trim($input["image_url"] ?? "");
$price = $input["price"] ?? null;
$calories = $input["calories"] ?? null;
$protein = $input["protein"] ?? null;
$category = $input["category"] ?? "";

if ($id === null) {
  http_response_code(400);
  echo json_encode(["error" => "id is required"]);
  exit;
}

if ($name === "" || $price === null || $calories === null || $protein === null || $category === "") {
  http_response_code(400);
  echo json_encode(["error" => "missing fields"]);
  exit;
}

// Check product exists
$check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$check->execute([$id]);

if (!$check->fetch()) {
  http_response_code(404);
  echo json_encode(["error" => "product not found"]);
  exit;
}

// Update
$stmt = $pdo->prepare(
    "UPDATE products
     SET name = ?, description = ?, image_url = ?, price = ?, calories = ?, protein = ?, category = ?
     WHERE id = ?"
  );
  $stmt->execute([$name, $description, $image_url, $price, $calories, $protein, $category, $id]);

echo json_encode(["message" => "product updated"]);