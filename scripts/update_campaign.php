<?php

	include_once('../config/db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$feat_status = $_POST['feat_status'];

	// delete all campaign feats
	$sql = "DELETE FROM campaign_feat WHERE campaign_id = ".$_POST['campaign_id'];
	$db->query($sql);

	// add current campaign feats
	foreach($feat_status as $feat_id) {
		$sql = "INSERT INTO campaign_feat (feat_id, campaign_id) VALUES (".$feat_id.", ".$_POST['campaign_id'].")";
		$db->query($sql);
	}

	$db->close();
	echo 'ok';

?>