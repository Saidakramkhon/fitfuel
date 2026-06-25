<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/db.php";

header("Content-Type: application/json; charset=UTF-8");

$input = json_decode(file_get_contents("php://input"), true);

$username = trim($input["username"] ?? "");
$email = trim($input["email"] ?? "");
$password = $input["password"] ?? "";
$securityPhrase = trim($input["security_phrase"] ?? "");

if ($username === "" || $email === "" || $password === "" || $securityPhrase === "") {
  http_response_code(400);
  echo json_encode(["error" => "username, email, password and security phrase are required"]);
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

if (strlen($securityPhrase) < 3) {
  http_response_code(400);
  echo json_encode(["error" => "security phrase must be at least 3 characters"]);
  exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$phraseHash = password_hash(strtolower($securityPhrase), PASSWORD_DEFAULT);

try {
  $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $check->execute([$email]);

  if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(["error" => "email already registered"]);
    exit;
  }

  $pdo->beginTransaction();

  $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
  $stmt->execute([$username, $email, $passwordHash]);

  $userId = $pdo->lastInsertId();

  $stmt = $pdo->prepare("INSERT INTO security_phrases (user_id, phrase_hash) VALUES (?, ?)");
  $stmt->execute([$userId, $phraseHash]);

  $pdo->commit();

  echo json_encode(["message" => "registered successfully"]);

} catch (Exception $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  http_response_code(500);
  echo json_encode(["error" => "server error"]);
}