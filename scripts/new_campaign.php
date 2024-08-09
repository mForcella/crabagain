<?php

    // Initialize PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require_once '../PHPMailer/src/Exception.php';
    require_once '../PHPMailer/src/PHPMailer.php';
    require_once '../PHPMailer/src/SMTP.php';

	// establish database connection
	include_once('../config/keys.php');
	include_once('../config/db_config.php');
    include_once('../config/email_config.php');

	// check the secret word
	$secret_word = $_POST['secret_word'];
	if ($secret_word != $keys['nerd_test']) {
		echo 0;
		return;
	}

	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	// create campaign
	$sql = "INSERT into campaign (name) VALUES ('".$_POST['name']."')";
	$db->query($sql);
	$campaign_id = $db->insert_id;
	echo $campaign_id;

	// make campaign creator admin
	$sql = "INSERT into login_campaign (campaign_id, login_id, campaign_role) VALUES ($campaign_id, ".$_POST['admin_id'].", 1)";
	$db->query($sql);

	// add other players to campaign
	$logins = [];
	if (isset($_POST['users'])) {
		$users = $_POST['users'];
		$sql = "SELECT id,email FROM login WHERE id IN (".implode(',',$users).")";
		$result = $db->query($sql);
		if ($result) {
			while($row = $result->fetch_assoc()) {
				array_push($logins, $row);
			}
		}
	}

	// get admin email address
	$admin = "";
	$sql = "SELECT email FROM login WHERE id = ".$_POST['admin_id'];
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$admin = $row['email'];
		}
	}

	// generate campaign link
	$protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
	$domain = $_SERVER['HTTP_HOST'];
	$url = "<a href='".$protocol.$domain."/?campaign=".$campaign_id."'>".$_POST['name']."!</a>";

	foreach ($users as $login_id) {
		$sql = "INSERT into login_campaign (campaign_id, login_id, campaign_role) VALUES ($campaign_id, $login_id, 2)";
		$db->query($sql);

		// notify users that they have been added to a campaign
		foreach ($logins as $login) {
			if ($login['id'] == $login_id) {
				$email = $login['email'];

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
			    $mail->Subject = 'CrabAgain.com - Join the Campaign, '.$_POST['name'].'!';
			    $msg = "You have been invited by ".$admin." to join their campaign, ".$_POST['name'].". You can view the campaign at the link below:<br>".$url;
			    $mail->msgHTML($msg);
			    $mail->send();

			}
		}
	}

	$db->close();

?>