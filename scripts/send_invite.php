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
	$email = $_POST['email'];
	$campaign_id = $_POST['campaign_id'];
	$login_id = $_POST['login_id'];

	// get login (sender) email address
	$sql = "SELECT email FROM login WHERE id = $login_id";
	$result = $db->query($sql);
	$login = "";
	if ($result->num_rows === 1) {
		while($row = $result->fetch_assoc()) {
			$login = $row['email'];
		}
	}

	// create new invitation entry
	$invite_code = bin2hex(random_bytes(16));
	$sql = "INSERT INTO invitation (invite_code, email, campaign_id) VALUES ('$invite_code', '$email', $campaign_id)";
	$db->query($sql);

	// generate invite link
	$protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
	$domain = $_SERVER['HTTP_HOST'];
	$url = "<a href='".$protocol.$domain."/register.php?invite=".$invite_code."'>Join the Campaign!</a>";

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
    $mail->Subject = 'CrabAgain.com - Invitation to Join Campaign';
    $msg = "You have been invited by ".$login." to join their campaign. To join, please register using the link below:<br>".$url;
    $mail->msgHTML($msg);
    $mail->send();

    echo 1;

?>