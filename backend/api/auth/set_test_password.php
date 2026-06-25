<?php
require_once __DIR__ . "/../../config/db.php";

header("Content-Type: application/json; charset=UTF-8");

$email = "khdsnd06d23z259g@studenti.unime.it";
$newPassword = "123456";

$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->execute([$hash, $email]);

echo json_encode([
  "message" => "Password updated",
  "email" => $email,
  "new_password" => $newPassword,
  "verify" => password_verify($newPassword, $hash)
]);