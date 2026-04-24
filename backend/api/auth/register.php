<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/db.php";

$input = json_decode(file_get_contents("php://input"), true);

$username = trim($input["username"] ?? "");
$email = trim($input["email"] ?? "");
$password = $input["password"] ?? "";

if ($username === "" || $email === "" || $password === "") {
  http_response_code(400);
  echo json_encode(["error" => "username, email and password are required"]);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(["error" => "invalid email"]);
  exit;
}

if (strlen($password) < 6) {
  http_response_code(400);
  echo json_encode(["error" => "password must be at least 6 characters"]);
  exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
  $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $check->execute([$email]);

  if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(["error" => "email already registered"]);
    exit;
  }

  $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
  $stmt->execute([$username, $email, $hash]);

  echo json_encode(["message" => "registered successfully"]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["error" => "server error"]);
}