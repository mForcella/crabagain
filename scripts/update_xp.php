<?php

	include_once('../config/db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$xp_award_id = $_POST['xp_award_id'];
	$user = $_POST['user'];
	$xp = $_POST['xp'];
	$attribute_pts = $_POST['attribute_pts'];

	// update user_xp_award - set awarded = true, set xp_after_award value
	$sql = "UPDATE user_xp_award SET awarded = 1, xp_after_award = ".$xp." WHERE id = ".$xp_award_id;
	$db->query($sql);

	// update user xp and attribute points
	$sql = "UPDATE user SET xp = ".$xp.", attribute_pts = ".$attribute_pts." WHERE id = ".$user;
	$db->query($sql);
	
	$db->close();
	echo 'ok';

?>