<?php

	include_once('../config/db_config.php');
	
	// establish database connection
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	// delete any campaign_feat entries for feat
	$sql = "DELETE FROM campaign_feat WHERE feat_id = ".$_POST['feat_id'];
	$db->query($sql);

	// delete feat
	$sql = "DELETE FROM feat_or_trait WHERE id = ".$_POST['feat_id'];
	$db->query($sql);
	
	$save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'delete_feat.php', 'delete', ".$_POST['login_id'].")";
	$db->query($save_sql);

	$db->close();

	echo 'ok';

?>