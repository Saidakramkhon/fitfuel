<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/backend/config/db.php";

$newPassword = password_hash("Admin123", PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE role = 'ADMIN'");
$stmt->execute([$newPassword]);

echo "Admin password reset to Admin123";
?>