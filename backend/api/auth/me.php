<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/db.php"; // provides $pdo

// Case 1: your login already stores full user
if (isset($_SESSION["user"]) && is_array($_SESSION["user"])) {
  echo json_encode(["user" => $_SESSION["user"]]);
  exit;
}

// Case 2: your login stores only user_id (common)
$user_id = $_SESSION["user_id"] ?? ($_SESSION["id"] ?? null);

if ($user_id) {
  $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user) {
    // normalize session so rest of app works
    $_SESSION["user"] = $user;
    echo json_encode(["user" => $user]);
    exit;
  }
}

// Not logged in
http_response_code(401);
echo json_encode(["error" => "Not authenticated"]);
exit;