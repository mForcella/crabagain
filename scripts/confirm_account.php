<?php

	session_start();

	include_once('../config/db_config.php');
	
	// establish database connection
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	$confirmation_code = $_GET['confirmation_code'];

	// get account from code
	$sql = "SELECT id FROM login WHERE confirmation_code = '$confirmation_code'";
	$result = $db->query($sql);
	$login_id;
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$login_id = $row['id'];
		}
	}
	if (isset($login_id)) {
		// update database and set account confirmed
		$sql = "UPDATE login SET confirmed = 1, confirmation_code = '' WHERE id = $login_id";
		$db->query($sql);
		// success - start session and redirect to select campaign
    	$_SESSION['login_id'] = $login_id;
    	header('Location: /select_campaign.php');
	} else {
		// fail - set msg and redirect to login
        $_SESSION['msg'] = "<h4 class='warning'>Confirmation link not valid</h4>";
        header('Location: /login.php');
	}
?>