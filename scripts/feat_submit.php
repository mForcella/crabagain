<?php

	include_once('../config/db_config.php');
	
	// establish database connection
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	// make sure feat name isn't duplicate
	$sql = "SELECT count(*) as count FROM feat_or_trait WHERE LOWER(name) LIKE LOWER('".addslashes(trim($_POST['feat_name']))."')";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			if ($row['count'] > 0) {
				echo 'Feat name already in use';
				$db->close();
				return;
			}
		}
	}

	$type = $_POST['feat_type'];
	$sql = "";
	switch($type) {
		case "feat":
		$sql = "INSERT INTO feat_or_trait (name, description, type, cost) VALUES ('".addslashes($_POST['feat_name'])."', '".addslashes($_POST['feat_descrip'])."', 'feat', "."4)";
		break;
		case "physical_trait_pos":
		$sql = "INSERT INTO feat_or_trait (name, description, type, cost) VALUES ('".addslashes($_POST['feat_name'])."', '".addslashes($_POST['feat_descrip'])."', 'physical_trait', ".$_POST['feat_cost'].")";
		break;
		case "physical_trait_neg":
		$sql = "INSERT INTO feat_or_trait (name, description, type, cost) VALUES ('".addslashes($_POST['feat_name'])."', '".addslashes($_POST['feat_descrip'])."', 'physical_trait', ".($_POST['feat_bonus']*-1).")";
		break;
		case "social_trait":
		$sql = "INSERT INTO feat_or_trait (name, description, type) VALUES ('".addslashes($_POST['feat_name'])."', '".addslashes($_POST['feat_descrip'])."', 'social_trait')";
		break;
		case "morale_trait":
		$sql = "INSERT INTO feat_or_trait (name, description, type) VALUES ('".addslashes($_POST['feat_name'])."', 'Positive State: ".addslashes($_POST['feat_pos_state'])."; Negative State: ".addslashes($_POST['feat_neg_state'])."', 'morale_trait')";
		break;
		case "compelling_action":
		$sql = "INSERT INTO feat_or_trait (name, description, type, cost) VALUES ('".addslashes($_POST['feat_name'])."', '".addslashes($_POST['feat_descrip'])."', 'compelling_action', "."-2)";
		break;
		case "profession":
		$sql = "INSERT INTO feat_or_trait (name, description, type) VALUES ('".addslashes($_POST['feat_name'])."', '".addslashes($_POST['feat_descrip'])."', 'profession')";
		break;
		case "social_background":
		$sql = "INSERT INTO feat_or_trait (name, description, type) VALUES ('".addslashes($_POST['feat_name'])."', '".addslashes($_POST['feat_descrip'])."', 'social_background')";
		break;
	}
	$db->query($sql);
	$feat_id = $db->insert_id;
	
	$save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'feat_submit.php', 'insert', ".$_POST['login_id'].")";
	$db->query($save_sql);

	// if campaign has campign_feat entries for current feat type, create new campign_feat entry
	// $sql = "SELECT count(*) as count FROM campaign_feat JOIN feat_or_trait ON campaign_feat.feat_id = feat_or_trait.id WHERE campaign_id = ".$_POST['campaign_id']." AND type = .".$_POST['feat_type']."'";
	// $result = $db->query($sql);
	// if ($result) {
	// 	while($row = $result->fetch_assoc()) {
	// 		if ($row['count'] > 0) {
	// 			$sql = "INSERT INTO campaign_feat (feat_id, campaign_id) VALUES (".$feat_id.", ".$_POST['campaign_id'].")";
	// 			$db->query($sql);
	// 		}
	// 	}
	// }

	// return feat_id
	echo $feat_id;

	$db->close();

?>