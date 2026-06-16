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

/* Correct password: clear failed attempts */
$stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
$stmt->execute([$email]);

/* Generate OTP */
$otp = strval(random_int(100000, 999999));
$expires_at = date("Y-m-d H:i:s", time() + 5 * 60);

/* Invalidate old OTPs for this user */
$stmt = $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE user_id = ?");
$stmt->execute([$user["id"]]);

/* Save new OTP */
$stmt = $pdo->prepare(
  "INSERT INTO otp_codes (user_id, otp_code, expires_at)
   VALUES (?, ?, ?)"
);
$stmt->execute([$user["id"], $otp, $expires_at]);

/* Temporary session only, not fully logged in yet */
$_SESSION["pending_user_id"] = $user["id"];
$_SESSION["pending_username"] = $user["username"];
$_SESSION["pending_role"] = $user["role"];
$_SESSION["pending_email"] = $email;

write_security_log($pdo, $user["id"], $email, "OTP_SENT");

/*
For demo we return OTP in JSON.
Later we can send it by real email.
*/
echo json_encode([
  "message" => "password correct, OTP required",
  "otp_required" => true,
  "demo_otp" => $otp,
  "note" => "For demo, OTP is shown here. In real system it is sent by email."
]);