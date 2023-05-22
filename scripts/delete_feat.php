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

	// delete any feat requirements
	$sql = "SELECT id FROM feat_or_trait_req_set WHERE feat_id = ".$_POST['feat_id'];
	$result = $db->query($sql);
	$req_set_ids = [];
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($req_set_ids, $row['id']);
		}
	}
	$sql = "DELETE FROM feat_or_trait_req WHERE req_set_id IN (".implode(',',$req_set_ids).")";
	$db->query($sql);
	$sql = "DELETE FROM feat_or_trait_req_set WHERE feat_id = ".$_POST['feat_id'];
	$db->query($sql);

	// delete feat
	$sql = "DELETE FROM feat_or_trait WHERE id = ".$_POST['feat_id'];
	$db->query($sql);

	$db->close();

	echo 'ok';

?>