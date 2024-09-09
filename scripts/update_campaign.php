<?php

	include_once('../config/db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$feat_status = $_POST['feat_status'];
	$race_status = isset($_POST['race_status']) ? $_POST['race_status'] : [];
	$campaign_id = $_POST['campaign_id'];

	// delete all campaign feats
	$sql = "DELETE FROM campaign_feat WHERE campaign_id = $campaign_id";
	$db->query($sql);
			
	// $save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'update_campaign.php', 'delete', ".$_POST['login_id'].")";
	// $db->query($save_sql);

	// add current campaign feats
	foreach($feat_status as $feat_id) {
		$sql = "INSERT INTO campaign_feat (feat_id, campaign_id) VALUES ($feat_id, $campaign_id)";
		$db->query($sql);
			
		// $save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'update_campaign.php', 'insert', ".$_POST['login_id'].")";
		// $db->query($save_sql);
	}

	// delete all campaign races
	$sql = "DELETE FROM campaign_race WHERE campaign_id = $campaign_id";
	$db->query($sql);
			
	// $save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'update_campaign.php', 'delete', ".$_POST['login_id'].")";
	// $db->query($save_sql);

	// add current campaign races
	foreach($race_status as $race_id) {
		$sql = "INSERT INTO campaign_race (race_id, campaign_id) VALUES ($race_id, $campaign_id)";
		$db->query($sql);
			
		// $save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'update_campaign.php', 'insert', ".$_POST['login_id'].")";
		// $db->query($save_sql);
	}

	$db->close();
	echo 'ok';

?>