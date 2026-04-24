<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/db.php";

// If you already have an auth guard, you can keep it later.
// For now we do a simple session check so it won't crash because of missing includes.
$user_id = $_SESSION["user_id"] ?? null;
if (!$user_id) {
  http_response_code(401);
  echo json_encode(["error" => "Not authenticated"]);
  exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$items = $input["items"] ?? [];

if (!is_array($items) || count($items) === 0) {
  http_response_code(400);
  echo json_encode(["error" => "Cart is empty"]);
  exit;
}

// Clean items
$clean = [];
foreach ($items as $it) {
  $pid = intval($it["product_id"] ?? 0);
  $qty = intval($it["qty"] ?? 0);
  if ($pid <= 0 || $qty <= 0) continue;
  if (!isset($clean[$pid])) $clean[$pid] = 0;
  $clean[$pid] += $qty;
}

if (count($clean) === 0) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid items"]);
  exit;
}

$productIds = array_keys($clean);

// Get prices from DB (server decides total)
$placeholders = implode(",", array_fill(0, count($productIds), "?"));
$stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
$stmt->execute($productIds);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$priceMap = [];
foreach ($rows as $r) {
  $priceMap[intval($r["id"])] = floatval($r["price"]);
}

// Ensure all products exist
foreach ($productIds as $pid) {
  if (!isset($priceMap[$pid])) {
    http_response_code(400);
    echo json_encode(["error" => "Product not found: $pid"]);
    exit;
  }
}

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, 0, 'NEW')");
  $stmt->execute([$user_id]);
  $orderId = intval($pdo->lastInsertId());

  $itemStmt = $pdo->prepare(
    "INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)"
  );

  $total = 0.0;

  foreach ($clean as $pid => $qty) {
    $price = $priceMap[$pid];
    $total += $price * $qty;
    $itemStmt->execute([$orderId, $pid, $qty, $price]);
  }

  $up = $pdo->prepare("UPDATE orders SET total = ? WHERE id = ?");
  $up->execute([number_format($total, 2, ".", ""), $orderId]);

  $pdo->commit();

  echo json_encode([
    "message" => "Order created",
    "order_id" => $orderId,
    "total" => number_format($total, 2, ".", "")
  ]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode([
    "error" => "Failed to create order",
    "details" => $e->getMessage()
  ]);
}