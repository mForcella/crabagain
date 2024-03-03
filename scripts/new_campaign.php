<?php

	// establish database connection
	include_once('../config/keys.php');
	include_once('../config/db_config.php');

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
	$users = $_POST['users'];
	foreach ($users as $user_id) {
		$sql = "INSERT into login_campaign (campaign_id, login_id, campaign_role) VALUES ($campaign_id, $user_id, 2)";
		$db->query($sql);
	}

	$db->close();

?>