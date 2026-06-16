<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/security_log.php";

header("Content-Type: application/json; charset=UTF-8");

$input = json_decode(file_get_contents("php://input"), true);

$token = trim($input["token"] ?? "");
$newPassword = $input["new_password"] ?? "";

if ($token === "" || $newPassword === "") {
  http_response_code(400);
  echo json_encode(["error" => "token and new_password required"]);
  exit;
}

if (strlen($newPassword) < 6) {
  http_response_code(400);
  echo json_encode(["error" => "password must be at least 6 characters"]);
  exit;
}

$stmt = $pdo->prepare(
  "SELECT * FROM password_resets
   WHERE token = ?
   AND used = 0
   AND expires_at > NOW()
   LIMIT 1"
);
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
  http_response_code(400);
  echo json_encode(["error" => "invalid or expired token"]);
  exit;
}

$email = $reset["email"];
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->execute([$hashedPassword, $email]);

$stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
$stmt->execute([$reset["id"]]);

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$user_id = $user["id"] ?? null;

write_security_log($pdo, $user_id, $email, "PASSWORD_RESET_COMPLETED");

echo json_encode([
  "message" => "Password reset successful"
]);