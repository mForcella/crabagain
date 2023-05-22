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

	// get hashed password from database
	$user_id = $_POST['user_id'];
	$sql = "SELECT * from user WHERE id = ".$user_id;
	$result = $db->query($sql);
	$user = [];
	if ($result->num_rows === 1) {
		while($row = $result->fetch_assoc()) {
			$user = $row;
		}
	}
	$character = $user['character_name'];

	// update user table - set reset_token
	$token = bin2hex(random_bytes(16));
	$sql = "UPDATE user SET reset_token = '".$token."' WHERE id = ".$user_id;
	$db->query($sql);

	// generate reset link
	$url = "https://crabagain.com/reset_password.php?token=".$token;

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
    $mail->addAddress($user['email'] == null ? 'michael.forcella@gmail.com' : $user['email']);
    $mail->Subject = 'CrabAgain.com - Password Reset Request';
    $msg = "New password reset request for ".$character."<br>
    Password Reset Link:<br>".$url;
    $mail->msgHTML($msg);
    $mail->send();
?>