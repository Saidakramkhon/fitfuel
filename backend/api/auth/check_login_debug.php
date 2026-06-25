<?php
require_once __DIR__ . "/../../config/db.php";

header("Content-Type: application/json; charset=UTF-8");

$input = json_decode(file_get_contents("php://input"), true);

$email = trim($input["email"] ?? "");
$password = $input["password"] ?? "";

$stmt = $pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  echo json_encode([
    "found" => false,
    "email_received" => $email,
    "password_received_length" => strlen($password)
  ]);
  exit;
}

echo json_encode([
  "found" => true,
  "email_received" => $email,
  "password_received_length" => strlen($password),
  "hash" => $user["password_hash"],
  "verify" => password_verify($password, $user["password_hash"])
]);