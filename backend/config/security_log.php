<?php

function write_security_log($pdo, $user_id, $email, $event_type) {
    $ip_address = $_SERVER["REMOTE_ADDR"] ?? null;
    $user_agent = $_SERVER["HTTP_USER_AGENT"] ?? null;

    $stmt = $pdo->prepare(
        "INSERT INTO security_logs (user_id, email, event_type, ip_address, user_agent)
         VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $user_id,
        $email,
        $event_type,
        $ip_address,
        $user_agent
    ]);
}