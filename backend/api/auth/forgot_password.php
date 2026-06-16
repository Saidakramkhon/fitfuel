<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/security_log.php";

header("Content-Type: application/json; charset=UTF-8");

$input = json_decode(file_get_contents("php://input"), true);
$email = trim($input["email"] ?? "");

if ($email === "") {
  http_response_code(400);
  echo json_encode(["error" => "email required"]);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  echo json_encode(["message" => "If this email exists, a reset code was generated."]);
  exit;
}

$token = bin2hex(random_bytes(32));
$expires_at = date("Y-m-d H:i:s", time() + 15 * 60);

$stmt = $pdo->prepare(
  "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)"
);
$stmt->execute([$email, $token, $expires_at]);

write_security_log($pdo, $user["id"], $email, "PASSWORD_RESET_REQUESTED");

echo json_encode([
  "message" => "Password reset token generated",
  "reset_token" => $token,
  "note" => "For demo, copy this token. In a real system it would be sent by email."
]);