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

	// update user_xp_award - set awarded = true, set xp_after_award value
	$sql = "UPDATE user_xp_award SET awarded = 1, xp_after_award = ".$xp." WHERE id = ".$xp_award_id;
	$db->query($sql);
			
	$save_sql = "INSERT INTO sql_query (query, source, type, login_id, character_id) VALUES ('".addslashes($sql)."', 'update_xp.php', 'update', ".$_POST['login_id'].", $user)";
	$db->query($save_sql);

	// update user xp
	$sql = "UPDATE user SET xp = ".$xp." WHERE id = ".$user;
	$db->query($sql);
			
	$save_sql = "INSERT INTO sql_query (query, source, type, login_id, character_id) VALUES ('".addslashes($sql)."', 'update_xp.php', 'update', ".$_POST['login_id'].", $user)";
	$db->query($save_sql);
	
	$db->close();
	// echo 'ok';

?>