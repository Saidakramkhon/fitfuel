<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../../vendor/autoload.php";

function send_otp_email($toEmail, $otpCode) {
    $secret = require __DIR__ . "/mail_secret.php";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = $secret["smtp_username"];
        $mail->Password = $secret["smtp_password"];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("fitfuel.security@gmail.com", "FitFuel Security");
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = "Your FitFuel OTP Code";
        $mail->Body = "
            <h2>FitFuel Security Verification</h2>
            <p>Your OTP code is:</p>
            <h1>$otpCode</h1>
            <p>This code expires in 5 minutes.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}