<?php
require_once __DIR__ . "/auth_guard.php";

if ($_SESSION["role"] !== "ADMIN") {
  http_response_code(403);
  echo json_encode(["error" => "admin only"]);
  exit;
}