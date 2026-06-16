<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/security_log.php";

session_start();

header("Content-Type: application/json; charset=UTF-8");

$input = json_decode(file_get_contents("php://input"), true);

$email = trim($input["email"] ?? "");
$password = $input["password"] ?? "";

if ($email === "" || $password === "") {
  http_response_code(400);
  echo json_encode(["error" => "email and password required"]);
  exit;
}

/* Check failed attempts in last 15 minutes */
$stmt = $pdo->prepare(
  "SELECT COUNT(*) FROM login_attempts
   WHERE email = ?
   AND attempt_time >= (NOW() - INTERVAL 15 MINUTE)"
);
$stmt->execute([$email]);
$failedAttempts = (int)$stmt->fetchColumn();

if ($failedAttempts >= 5) {
  write_security_log($pdo, null, $email, "ACCOUNT_LOCKED");

  http_response_code(429);
  echo json_encode(["error" => "Too many failed attempts. Try again after 15 minutes."]);
  exit;
}

$stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user["password_hash"])) {
  $stmt = $pdo->prepare("INSERT INTO login_attempts (email) VALUES (?)");
  $stmt->execute([$email]);

  write_security_log($pdo, null, $email, "LOGIN_FAILED");

  http_response_code(401);
  echo json_encode(["error" => "invalid credentials"]);
  exit;
}

/* Correct login: clear failed attempts */
$stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
$stmt->execute([$email]);

$_SESSION["user_id"] = $user["id"];
$_SESSION["role"] = $user["role"];
$_SESSION["username"] = $user["username"];

$_SESSION["user"] = [
  "id" => $user["id"],
  "username" => $user["username"],
  "role" => $user["role"]
];

write_security_log($pdo, $user["id"], $email, "LOGIN_SUCCESS");

echo json_encode([
  "message" => "login successful",
  "user" => [
    "id" => $user["id"],
    "username" => $user["username"],
    "role" => $user["role"]
  ]
]);