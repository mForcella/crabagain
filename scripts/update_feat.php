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
	
	// add reqs for standard feats
	$feat_id = $_POST['feat_id'];
	if ($type == "feat") {
		// delete all current requirements
		$sql = "SELECT id FROM feat_or_trait_req_set WHERE feat_id = ".$feat_id;
		$result = $db->query($sql);
		$req_set_ids = [];
		if ($result) {
			while($row = $result->fetch_assoc()) {
				array_push($req_set_ids, $row['id']);
			}
		}
		$sql = "DELETE FROM feat_or_trait_req WHERE req_set_id IN (".implode(',',$req_set_ids).")";
		$db->query($sql);
		$sql = "DELETE FROM feat_or_trait_req_set WHERE feat_id = ".$feat_id;
		$db->query($sql);

		// create new feat requirements
		$reqs = $_POST['feat_reqs'];
		foreach ($reqs as $req) {
			$sql = "INSERT INTO feat_or_trait_req_set (feat_id) VALUES (".$feat_id.")";
			$db->query($sql);
			$req_set_id = $db->insert_id;
			$req_parts = explode(" OR ", $req);
			foreach ($req_parts as $req_part) {
				$type_and_val = explode(": ", $req_part);
				// convert req type for db
				$req_type = $type_and_val[0] == "Precision" ? lcfirst($type_and_val[0])."_" : lcfirst($type_and_val[0]);
				$sql = "INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (".$req_set_id.", '".$req_type."', '".addslashes($type_and_val[1])."');";
				$db->query($sql);
			}
		}
		// check for 'character_creation' = true
		if (isset($_POST['feat_character_create']) && $_POST['feat_character_create'] == 'on') {
			$sql = "INSERT INTO feat_or_trait_req_set (feat_id) VALUES (".$feat_id.")";
			$db->query($sql);
			$req_set_id = $db->insert_id;
			$sql = "INSERT INTO feat_or_trait_req (req_set_id, type, value) VALUES (".$req_set_id.", 'character_creation', 'true');";
			$db->query($sql);
		}
	}

	// return update confirmation
	echo "update ok";

	$db->close();

?>