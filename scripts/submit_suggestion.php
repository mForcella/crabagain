<?php

    // Initialize PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require_once 'PHPMailer/src/Exception.php';
    require_once 'PHPMailer/src/PHPMailer.php';
    require_once 'PHPMailer/src/SMTP.php';
    include_once('../config/email_config.php');

	// email me with reset link
    $mail = new PHPMailer;
    $mail->Host = $email_config['host'];
    $mail->Port = $email_config['port'];
    $mail->SMTPSecure = 'tls';
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Username = $email_config['user'];
    $mail->Password = $email_config['password'];
    $mail->setFrom($mail->Username, "Gary Gygax, Dark Lord of Crabs");
    $mail->addAddress('michael.forcella@gmail.com');
    $mail->Subject = 'CrabAgain.com - User Suggestion';
    $mail->msgHTML($_POST['message']);
    $mail->send();

	echo 'ok';

?>