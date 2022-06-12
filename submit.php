<?php

	include_once('db_config.php');
	include_once('keys.php');
	
	// establish database connection
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$user_columns = ['character_name', 'attribute_pts', 'xp', 'morale', 'race', 'height', 'weight', 'age', 'eyes', 'hair', 'gender', 'other', 'size', 'strength', 'fortitude', 'speed', 'agility', 'precision_', 'awareness', 'allure', 'deception', 'intellect', 'innovation', 'intuition', 'vitality', 'background', 'free', 'move_penalty', 'fear', 'poison', 'disease', 'damage', 'wounds', 'wound_penalty', 'weapon_1', 'weapon_2', 'weapon_3', 'motivator_1', 'motivator_2', 'motivator_3', 'motivator_4', 'motivator_1_pts', 'motivator_2_pts', 'motivator_3_pts', 'motivator_4_pts'];

	// new or existing character?
	if ($_POST['user_id'] != null) {
		$sql = "UPDATE user SET";
		foreach ($user_columns as $column) {
			// convert '' value to NULL
			$insert_value;
			if ($_POST[$column] == '') {
				$insert_value = 'NULL';
			} else {
				$insert_value = "'" . addslashes($_POST[$column]) . "'";
			}
			$sql .= " " . $column . " = " . $insert_value . ",";
		}
		$sql = substr($sql, 0, -1) . " WHERE id = ".$_POST['user_id'];
		$db->query($sql);
		// echo $db->error;
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
			// make sure character name isn't empty
			if ($_POST['character_name'] == null|| $_POST['character_name'] == "") {
				$db->close();
				$error_output = "Something went wrong. Please try again later";
			  	die($error_output);
			}
			// hash password
			$hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
			$sql = "INSERT INTO user (password, ";
			foreach ($user_columns as $column) {
				$sql .= $column . ', ';
			}
			$sql = substr($sql, 0, -2) . ") VALUES ('".$hashed_password."', ";
			foreach ($user_columns as $column) {
				// convert '' value to NULL
				$insert_value;
				if ($_POST[$column] == '') {
					$insert_value = 'NULL';
				} else {
					$insert_value = "'" . addslashes($_POST[$column]) . "'";
				}
				$sql .= $insert_value . ", ";
			}
			$sql = substr($sql, 0, -2) . ")";
			$db->query($sql);
			// echo $db->error;
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
			$sql = "INSERT INTO user_feat (name, description, user_id) VALUES ('".addslashes($feat_names[$i])."', '".addslashes($feat_descriptions[$i])."', '".$user_id."')";
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
			$sql = "INSERT INTO user_training (name, attribute_group, value, user_id) VALUES ('".addslashes($vals[0])."', '".$vals[1]."', '".$training_val[$i]."', '".$user_id."')";
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
		$weapon_type = $_POST['weapon_type'];
		$weapon_max_damage = $_POST['weapon_max_damage'];
		$weapon_range = $_POST['weapon_range'];
		$weapon_rof = $_POST['weapon_rof'];
		$weapon_defend = $_POST['weapon_defend'];
		for ($i = 0; $i < count($weapons); $i++) {
			$weapon_damage[$i] = empty($weapon_damage[$i]) ? "NULL" : $weapon_damage[$i];
			$weapon_max_damage[$i] = empty($weapon_max_damage[$i]) ? "NULL" : $weapon_max_damage[$i];
			$weapon_range[$i] = empty($weapon_range[$i]) ? "NULL" : $weapon_range[$i];
			$weapon_defend[$i] = empty($weapon_defend[$i]) ? "NULL" : $weapon_defend[$i];
			$sql = "INSERT INTO user_weapon (name, type, quantity, damage, max_damage, range_, rof, defend, notes, weight, user_id) VALUES ('".addslashes($weapons[$i])."', '".$weapon_type[$i]."', '".addslashes($weapon_qty[$i])."', ".$weapon_damage[$i].", ".$weapon_max_damage[$i].", ".$weapon_range[$i].", '".addslashes($weapon_rof[$i])."', ".$weapon_defend[$i].", '".addslashes($weapon_notes[$i])."', ".$weapon_weight[$i].", '".$user_id."')";
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
			$sql = "INSERT INTO user_protection (name, bonus, notes, weight, user_id) VALUES ('".addslashes($protections[$i])."', '".addslashes($protection_bonus[$i])."', '".addslashes($protection_notes[$i])."', '".$protection_weight[$i]."', '".$user_id."')";
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
			$sql = "INSERT INTO user_healing (name, quantity, effect, weight, user_id) VALUES ('".addslashes($healings[$i])."', '".addslashes($healing_quantity[$i])."', '".addslashes($healing_effect[$i])."', '".$healing_weight[$i]."', '".$user_id."')";
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
			$sql = "INSERT INTO user_misc (name, quantity, notes, weight, user_id) VALUES ('".addslashes($misc[$i])."', '".addslashes($misc_quantity[$i])."', '".addslashes($misc_notes[$i])."', '".$misc_weight[$i]."', '".$user_id."')";
			$db->query($sql);
		}
	}

	// remove any old notes
	$sql = "DELETE FROM user_note WHERE user_id = " . $user_id;
	$db->query($sql);

	// look for new notes
	if (isset($_POST['notes'])) {
		$titles = $_POST['titles'];
		$notes = $_POST['notes'];
		for ($i = 0; $i < count($notes); $i++) {
			$sql = "INSERT INTO user_note (title, note, user_id) VALUES ('".addslashes($titles[$i])."', '".addslashes($notes[$i])."', '".$user_id."')";
			$db->query($sql);
		}
	}

	$db->close();
	header("Location: /?user=".$user_id,  true,  301 );  exit;
?>