<?php
require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/db.php";

session_start();

$input = json_decode(file_get_contents("php://input"), true);

$email = trim($input["email"] ?? "");
$password = $input["password"] ?? "";

if ($email === "" || $password === "") {
  http_response_code(400);
  echo json_encode(["error" => "email and password required"]);
  exit;
}

$stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password_hash"])) {
  http_response_code(401);
  echo json_encode(["error" => "invalid credentials"]);
  exit;
}

// Save user in session
$_SESSION["user_id"] = $user["id"];
$_SESSION["role"] = $user["role"];
$_SESSION["username"] = $user["username"];

echo json_encode([
  "message" => "login successful",
  "user" => [
    "id" => $user["id"],
    "username" => $user["username"],
    "role" => $user["role"]
  ]
]);