<?php

    // Initialize PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require_once '../PHPMailer/src/Exception.php';
    require_once '../PHPMailer/src/PHPMailer.php';
    require_once '../PHPMailer/src/SMTP.php';

	// establish database connection
	include_once('../config/db_config.php');
    include_once('../config/email_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	// make sure email is valid
	$email = isset($_POST['email']) ? $_POST['email'] : "";
	$sql = "SELECT * from login WHERE email = '$email'";
	$result = $db->query($sql);
	$login;
	if ($result->num_rows === 1) {
		while($row = $result->fetch_assoc()) {
			$login = $row;
		}
	}

	if (isset($login)) {
		// update user table - set reset_token
		$token = bin2hex(random_bytes(16));
		$sql = "UPDATE login SET reset_token = '$token' WHERE email = '$email'";
		$db->query($sql);

		$save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'email_password_reset_link.php', 'update', ".$login['id'].")";
		$db->query($save_sql);
		
		$db->close();

		// generate reset link
		$protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
		$domain = $_SERVER['HTTP_HOST'];
		$url = "<a href='".$protocol.$domain."/reset_password.php?token=".$token."'>Reset Your Password</a>";

		// email me with reset link
	    $mail = new PHPMailer;
	    $mail->Host = $email_config['host'];
	    $mail->Port = $email_config['port'];
	    $mail->SMTPSecure = 'tls';
	    $mail->isSMTP();
	    $mail->SMTPAuth = true;
	    $mail->Username = $email_config['user'];
	    $mail->Password = $email_config['password'];
	    $mail->setFrom($mail->Username, "Gary, Dark Lord of Crabs");
	    $mail->addAddress($email);
	    $mail->Subject = 'CrabAgain.com - Password Reset Link';
	    $msg = "Follow the link below to reset your password:<br>".$url;
	    $mail->msgHTML($msg);
	    $mail->send();

        echo 1;
	} else {
		echo 0;
	}

?>