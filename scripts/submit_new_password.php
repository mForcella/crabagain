<?php

    // Initialize PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require_once 'PHPMailer/src/Exception.php';
    require_once 'PHPMailer/src/PHPMailer.php';
    require_once 'PHPMailer/src/SMTP.php';

	// establish database connection
	include_once('../config/db_config.php');
    include_once('../config/email_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	// set new password and clear reset token
	$hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	$sql = "UPDATE user SET password = '".$hashed_password."', reset_token = 'NULL' WHERE id = ".$_POST['user_id'];
	$db->query($sql);
	echo $db->error == "" ? 'ok' : $db->error;

	// check if email is empty - send email
	$email = $_POST['email'];
	if ($email != "" && $db->error == "") {
	    $mail = new PHPMailer;
	    $mail->Host = $email_config['host'];
	    $mail->Port = $email_config['port'];
	    $mail->SMTPSecure = 'tls';
	    $mail->isSMTP();
	    $mail->SMTPAuth = true;
	    $mail->Username = $email_config['user'];
	    $mail->Password = $email_config['password'];
	    $mail->setFrom($mail->Username, "Gary Gygax");
	    $mail->addAddress($email);
	    $mail->Subject = 'CrabAgain.com - Your New Password';
	    $msg = "Hey nerd, here's your new password:<br>".$_POST['password'];
	    $mail->msgHTML($msg);
	    $mail->send();
	}

?>