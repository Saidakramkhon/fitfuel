<?php
require_once __DIR__ . "/../../config/cors.php";
session_start();

session_unset();
session_destroy();

echo json_encode(["message" => "logged out"]);