<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(!function_exists('sendEmail')){
    function sendEmail($emailConfig){
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION');
        $mail->Port = env('MAIL_PORT');
        $mail->setFrom($emailConfig['mail_from_email'], $emailConfig['mail_from_name']);
        $mail->addAddress($emailConfig['mail_from_email'], $emailConfig['mail_recipient_name']);
        $mail->isHTML(true);
        $mail->Subject = $emailConfig['mail_subject'];
        $mail->Body = $emailConfig['mail_body'];

        if($mail->send()) {
            return true;
        }

        return false;
    }
}
