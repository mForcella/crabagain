<?php

	// establish database connection
	include_once('db_config.php');
	include_once('keys.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$user_columns = ['character_name', 'xp', 'level', 'morale', 'morale_effect', 'race', 'height', 'weight', 'age', 'eyes', 'hair', 'gender', 'other', 'strength', 'fortitude', 'speed', 'agility', 'precision_', 'awareness', 'allure', 'deception', 'intellect', 'innovation', 'intuition', 'vitality', 'notes', 'background', 'standard', 'quick', 'free', 'move', 'initiative', 'move_penalty', 'toughness', 'defend', 'dodge', 'fear', 'poison', 'disease', 'damage', 'resilience', 'wounds', 'wound_penalty', 'total_weight', 'unhindered', 'encumbered', 'burdened', 'overburdened', 'motivator_1', 'motivator_2', 'motivator_3', 'motivator_4', 'motivator_1_pts', 'motivator_2_pts', 'motivator_3_pts', 'motivator_4_pts', 'weapon_1', 'weapon_1_damage', 'weapon_1_crit', 'weapon_1_range', 'weapon_1_rof', 'weapon_2', 'weapon_2_damage', 'weapon_2_crit', 'weapon_2_range', 'weapon_2_rof', 'weapon_3', 'weapon_3_damage', 'weapon_3_crit', 'weapon_3_range', 'weapon_3_rof'];

	// new or existing character?
	if ($_POST['user_id'] != null) {
		$sql = "UPDATE user SET";
		foreach ($user_columns as $column) {
			$sql .= " " . $column . " = '" . addslashes($_POST[$column]) . "',";
		}
		$sql = substr($sql, 0, -1) . " WHERE id = ".$_POST['user_id'];
		$db->query($sql);
		$user_id = $_POST['user_id'];
	} else {
		// get reCAPTCHA score from Google
		$recaptcha_secret = $keys['recaptcha_secret'];
		$recaptcha_response = $_POST['recaptcha_response'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => $recaptcha_secret, 'response' => $recaptcha_response)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$arrResponse = json_decode($response, true);
		// check response values
		if($arrResponse["success"] == '1' && $arrResponse["action"] == 'new_user' && $arrResponse["score"] >= 0.5) {
			// hash password
			$hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
			$sql = "INSERT INTO user (password, ";
			foreach ($user_columns as $column) {
				$sql .= $column . ', ';
			}
			$sql = substr($sql, 0, -2) . ") VALUES ('".$hashed_password."', ";
			foreach ($user_columns as $column) {
				$sql .= "'" . addslashes($_POST[$column]) . "', ";
			}
			$sql = substr($sql, 0, -2) . ")";
			$db->query($sql);
			$user_id = $db->insert_id;
		} else {
			// Score less than 0.5 indicates suspicious activity. Return an error
			$db->close();
			$error_output = "Something went wrong. Please try again later";
		  	die($error_output);
		}

	}

	// remove any old feats
	$sql = "DELETE FROM user_feat WHERE user_id = " . $user_id;
	$db->query($sql);

	// look for new feats
	if (isset($_POST['feat_names'])) {
		$feat_names = $_POST['feat_names'];
		$feat_descriptions = $_POST['feat_descriptions'];
		for ($i = 0; $i < count($feat_names); $i++) {
			$sql = "INSERT INTO user_feat (name, description, user_id) VALUES ('".$feat_names[$i]."', '".$feat_descriptions[$i]."', '".$user_id."')";
			$db->query($sql);
		}
	}

	// remove any old trainings
	$sql = "DELETE FROM user_training WHERE user_id = " . $user_id;
	$db->query($sql);

	// look for new trainings
	if (isset($_POST['training'])) {
		$training = $_POST['training'];
		$training_val = $_POST['training_val'];
		for ($i = 0; $i < count($training); $i++) {
			$vals = explode(":", $training[$i]);
			$sql = "INSERT INTO user_training (name, attribute_group, value, user_id) VALUES ('".$vals[0]."', '".$vals[1]."', '".$training_val[$i]."', '".$user_id."')";
			$db->query($sql);
		}
	}

	// remove any old weapons
	$sql = "DELETE FROM user_weapon WHERE user_id = " . $user_id;
	$db->query($sql);

	// look for new weapons
	if (isset($_POST['weapons'])) {
		$weapons = $_POST['weapons'];
		$weapon_qty = $_POST['weapon_qty'];
		$weapon_damage = $_POST['weapon_damage'];
		$weapon_notes = $_POST['weapon_notes'];
		$weapon_weight = $_POST['weapon_weight'];
		for ($i = 0; $i < count($weapons); $i++) {
			$sql = "INSERT INTO user_weapon (name, quantity, damage, notes, weight, user_id) VALUES ('".$weapons[$i]."', '".$weapon_qty[$i]."', '".$weapon_damage[$i]."', '".$weapon_notes[$i]."', '".$weapon_weight[$i]."', '".$user_id."')";
			$db->query($sql);
		}
	}

	// remove any old protections
	$sql = "DELETE FROM user_protection WHERE user_id = " . $user_id;
	$db->query($sql);

	// look for new protections
	if (isset($_POST['protections'])) {
		$protections = $_POST['protections'];
		$protection_bonus = $_POST['protection_bonus'];
		$protection_notes = $_POST['protection_notes'];
		$protection_weight = $_POST['protection_weight'];
		for ($i = 0; $i < count($protections); $i++) {
			$sql = "INSERT INTO user_protection (name, bonus, notes, weight, user_id) VALUES ('".$protections[$i]."', '".$protection_bonus[$i]."', '".$protection_notes[$i]."', '".$protection_weight[$i]."', '".$user_id."')";
			$db->query($sql);
		}
	}

	// remove any old healings
	$sql = "DELETE FROM user_healing WHERE user_id = " . $user_id;
	$db->query($sql);

	// look for new protections
	if (isset($_POST['healings'])) {
		$healings = $_POST['healings'];
		$healing_quantity = $_POST['healing_quantity'];
		$healing_effect = $_POST['healing_effect'];
		$healing_weight = $_POST['healing_weight'];
		for ($i = 0; $i < count($healings); $i++) {
			$sql = "INSERT INTO user_healing (name, quantity, effect, weight, user_id) VALUES ('".$healings[$i]."', '".$healing_quantity[$i]."', '".$healing_effect[$i]."', '".$healing_weight[$i]."', '".$user_id."')";
			$db->query($sql);
		}
	}

	// remove any old misc items
	$sql = "DELETE FROM user_misc WHERE user_id = " . $user_id;
	$db->query($sql);

	// look for new misc items
	if (isset($_POST['misc'])) {
		$misc = $_POST['misc'];
		$misc_quantity = $_POST['misc_quantity'];
		$misc_notes = $_POST['misc_notes'];
		$misc_weight = $_POST['misc_weight'];
		for ($i = 0; $i < count($misc); $i++) {
			$sql = "INSERT INTO user_misc (name, quantity, notes, weight, user_id) VALUES ('".$misc[$i]."', '".$misc_quantity[$i]."', '".$misc_notes[$i]."', '".$misc_weight[$i]."', '".$user_id."')";
			$db->query($sql);
		}
	}

	$db->close();
	header("Location: /?user=".$user_id,  true,  301 );  exit;
?>