<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/security_log.php";

session_start();

header("Content-Type: application/json; charset=UTF-8");

$input = json_decode(file_get_contents("php://input"), true);
$otp = trim($input["otp"] ?? "");

if ($otp === "") {
  http_response_code(400);
  echo json_encode(["error" => "OTP required"]);
  exit;
}

$user_id = $_SESSION["pending_user_id"] ?? null;
$username = $_SESSION["pending_username"] ?? null;
$role = $_SESSION["pending_role"] ?? null;
$email = $_SESSION["pending_email"] ?? null;

if (!$user_id) {
  http_response_code(401);
  echo json_encode(["error" => "No pending login"]);
  exit;
}

$stmt = $pdo->prepare(
  "SELECT * FROM otp_codes
   WHERE user_id = ?
   AND otp_code = ?
   AND used = 0
   AND expires_at > NOW()
   ORDER BY id DESC
   LIMIT 1"
);
$stmt->execute([$user_id, $otp]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  write_security_log($pdo, $user_id, $email, "OTP_FAILED");

  http_response_code(401);
  echo json_encode(["error" => "Invalid or expired OTP"]);
  exit;
}

$stmt = $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?");
$stmt->execute([$row["id"]]);

$_SESSION["phrase_pending_user_id"] = $user_id;
$_SESSION["phrase_pending_username"] = $username;
$_SESSION["phrase_pending_role"] = $role;
$_SESSION["phrase_pending_email"] = $email;

write_security_log($pdo, $user_id, $email, "OTP_VERIFIED");

echo json_encode([
  "message" => "OTP verified, security phrase required",
  "security_phrase_required" => true
]);