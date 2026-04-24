<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/admin_guard.php";
require_once __DIR__ . "/../../config/db.php";

$input = json_decode(file_get_contents("php://input"), true);
$id = $input["id"] ?? null;

if ($id === null) {
  http_response_code(400);
  echo json_encode(["error" => "id is required"]);
  exit;
}

// Check exists
$check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$check->execute([$id]);

if (!$check->fetch()) {
  http_response_code(404);
  echo json_encode(["error" => "product not found"]);
  exit;
}

// Delete
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(["message" => "product deleted"]);