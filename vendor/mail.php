<?php
session_start();
require 'PHPMailer/PHPMailerAutoload.php';

if (!isset($_SESSION['mail_data'])) {
    header("Location: ../reset.php");
    exit();
}

$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your mail';
$mail->Password = 'your mail app pwd';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$data = $_SESSION['mail_data'];
unset($_SESSION['mail_data']);

$mail->setFrom('no-reply@yourdomain.com');
$mail->addAddress($data['to']);
$mail->Subject = $data['subject'];
$mail->Body = $data['message'];

if ($mail->send()) {
    $_SESSION['success'] = "Reset link sent to your email!";
} else {
    $_SESSION['error'] = "Failed to send email: " . $mail->ErrorInfo;
}

header("Location: ../reset.php");
exit();
?>