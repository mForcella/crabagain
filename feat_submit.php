<?php

	include_once('db_config.php');
	
	// establish database connection
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
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
	}
	$db->query($sql);
	
	// get insert ID and add reqs for standard feats
	$feat_id = $db->insert_id;
	if ($type == "feat") {
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

	// if campaign has campign_feat entries, create new campign_feat entry
	$sql = "SELECT count(*) as count FROM campaign_feat WHERE campaign_id = ".$_POST['campaign_id'];
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			if ($row['count'] > 0) {
				$sql = "INSERT INTO campaign_feat (feat_id, campaign_id) VALUES (".$feat_id.", ".$_POST['campaign_id'].")";
				$db->query($sql);
			}
		}
	}

	// return feat_id
	echo $feat_id;

	$db->close();

?>