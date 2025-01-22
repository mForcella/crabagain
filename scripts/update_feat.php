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
	$sql = "SELECT count(*) as count FROM feat_or_trait WHERE LOWER(name) LIKE LOWER('".addslashes(trim($_POST['feat_name']))."') AND id != ".$_POST['feat_id'];
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
		$sql = "UPDATE feat_or_trait SET name = '".addslashes($_POST['feat_name'])."', description = '".addslashes($_POST['feat_descrip'])."' WHERE id = ".$_POST['feat_id'];
		break;
		case "physical_trait_pos":
		$sql = "UPDATE feat_or_trait SET name = '".addslashes($_POST['feat_name'])."', description = '".addslashes($_POST['feat_descrip'])."', cost = ".$_POST['feat_cost']." WHERE id = ".$_POST['feat_id'];
		break;
		case "physical_trait_neg":
		$sql = "UPDATE feat_or_trait SET name = '".addslashes($_POST['feat_name'])."', description = '".addslashes($_POST['feat_descrip'])."', cost = ".($_POST['feat_bonus']*-1)." WHERE id = ".$_POST['feat_id'];
		break;
		case "social_trait":
		$sql = "UPDATE feat_or_trait SET name = '".addslashes($_POST['feat_name'])."', description = '".addslashes($_POST['feat_descrip'])."' WHERE id = ".$_POST['feat_id'];
		break;
		case "morale_trait":
		$sql = "UPDATE feat_or_trait SET name = '".addslashes($_POST['feat_name'])."', description = 'Positive State: ".addslashes($_POST['feat_pos_state'])."; Negative State: ".addslashes($_POST['feat_neg_state'])."' WHERE id = ".$_POST['feat_id'];
		break;
		case "compelling_action":
		$sql = "UPDATE feat_or_trait SET name = '".addslashes($_POST['feat_name'])."', description = '".addslashes($_POST['feat_descrip'])."' WHERE id = ".$_POST['feat_id'];
		break;
		case "profession":
		$sql = "UPDATE feat_or_trait SET name = '".addslashes($_POST['feat_name'])."', description = '".addslashes($_POST['feat_descrip'])."' WHERE id = ".$_POST['feat_id'];
		break;
	}
	$db->query($sql);
			
	$save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'update_feat.php', 'update', ".$_POST['login_id'].")";
	$db->query($save_sql);

	// return update confirmation
	echo "update ok";

	$db->close();

?>