<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

header("Content-Type: application/json; charset=UTF-8");

/**
 * Works with BOTH session styles:
 * - $_SESSION["user"] = ["id"=>..., "role"=>...]
 * - $_SESSION["user_id"] = ...
 */
function current_user_id(): ?int {
  if (isset($_SESSION["user"]) && is_array($_SESSION["user"]) && isset($_SESSION["user"]["id"])) {
    return intval($_SESSION["user"]["id"]);
  }
  if (isset($_SESSION["user_id"])) {
    return intval($_SESSION["user_id"]);
  }
  if (isset($_SESSION["id"])) {
    return intval($_SESSION["id"]);
  }
  return null;
}

function require_login(): void {
  if (!current_user_id()) {
    http_response_code(401);
    echo json_encode(["error" => "Not authenticated"]);
    exit;
  }
}

function require_admin(): void {
  require_login();
  $role = $_SESSION["user"]["role"] ?? null;

  // If role is not stored, treat as NOT admin
  if ($role !== "ADMIN") {
    http_response_code(403);
    echo json_encode(["error" => "Admin only"]);
    exit;
  }
}