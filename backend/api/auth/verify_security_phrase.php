<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/security_log.php";

session_start();

header("Content-Type: application/json; charset=UTF-8");

$input = json_decode(file_get_contents("php://input"), true);
$phrase = strtolower(trim($input["security_phrase"] ?? ""));

if ($phrase === "") {
  http_response_code(400);
  echo json_encode(["error" => "Security phrase required"]);
  exit;
}

$user_id = $_SESSION["phrase_pending_user_id"] ?? null;
$username = $_SESSION["phrase_pending_username"] ?? null;
$role = $_SESSION["phrase_pending_role"] ?? null;
$email = $_SESSION["phrase_pending_email"] ?? null;

if (!$user_id) {
  http_response_code(401);
  echo json_encode(["error" => "No pending security phrase verification"]);
  exit;
}

$stmt = $pdo->prepare("SELECT phrase_hash FROM security_phrases WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || !password_verify($phrase, $row["phrase_hash"])) {
  write_security_log($pdo, $user_id, $email, "SECURITY_PHRASE_FAILED");

  http_response_code(401);
  echo json_encode(["error" => "Invalid security phrase"]);
  exit;
}

$_SESSION["user_id"] = $user_id;
$_SESSION["role"] = $role;
$_SESSION["username"] = $username;

$_SESSION["user"] = [
  "id" => $user_id,
  "username" => $username,
  "role" => $role
];

unset($_SESSION["pending_user_id"]);
unset($_SESSION["pending_username"]);
unset($_SESSION["pending_role"]);
unset($_SESSION["pending_email"]);

unset($_SESSION["phrase_pending_user_id"]);
unset($_SESSION["phrase_pending_username"]);
unset($_SESSION["phrase_pending_role"]);
unset($_SESSION["phrase_pending_email"]);

write_security_log($pdo, $user_id, $email, "SECURITY_PHRASE_VERIFIED");
write_security_log($pdo, $user_id, $email, "LOGIN_SUCCESS");

echo json_encode([
  "message" => "Security phrase verified, login successful",
  "user" => [
    "id" => $user_id,
    "username" => $username,
    "role" => $role
  ]
]);