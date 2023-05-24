<?php

	include_once('../config/db_config.php');
	include_once('../config/keys.php');
	
	// establish database connection
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$user_columns = ['campaign_id', 'email', 'character_name', 'attribute_pts', 'xp', 'morale', 'race', 'height', 'weight', 'age', 'eyes', 'hair', 'gender', 'other', 'size', 'strength', 'fortitude', 'speed', 'agility', 'precision_', 'awareness', 'allure', 'deception', 'intellect', 'innovation', 'intuition', 'vitality', 'background', 'move_penalty', 'magic', 'fear', 'poison', 'disease', 'damage', 'fatigue'];

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

	// get user motivators
	if (isset($_POST['motivators'])) {
		$motivators = $_POST['motivators'];
		$motivator_pts = $_POST['motivator_pts'];
		$motivator_primary = $_POST['motivator_primary'];
		$ids = $_POST['motivator_ids'];

		// update where ID not empty; insert new where ID empty
		for ($i = 0; $i < count($motivators); $i++) {
			if ($motivators[$i] != "") {
				if ($ids[$i] == "") {
					$sql = "INSERT INTO user_motivator (motivator, points, primary_, user_id) VALUES ('".$motivators[$i]."', '".$motivator_pts[$i]."', '".$motivator_primary[$i]."', '".$user_id."')";
				} else {
					$sql = "UPDATE user_motivator SET motivator = '".$motivators[$i]."', points = '".$motivator_pts[$i]."', primary_ = ".$motivator_primary[$i]." WHERE id = ".$ids[$i];
				}
				$db->query($sql);
			}
		}
	}

	// look for new feats
	if (isset($_POST['feat_names'])) {
		$feat_names = $_POST['feat_names'];
		$feat_descriptions = $_POST['feat_descriptions'];
		$feat_ids = $_POST['feat_ids'];
		$user_feat_ids = $_POST['user_feat_ids'];

		// delete feats not in ID list
		$sql = "DELETE FROM user_feat WHERE user_id = " . $user_id . " AND id NOT IN ('" . implode("','", $user_feat_ids) . "')";
		$db->query($sql);

		for ($i = 0; $i < count($feat_names); $i++) {
			// update where ID not empty; insert new where ID empty
			if ($user_feat_ids[$i] == "") {
				// insert feat_id if present
				$sql = $feat_ids[$i] == "" ?
				"INSERT INTO user_feat (name, description, user_id) VALUES ('" :
				"INSERT INTO user_feat (name, description, user_id, feat_id) VALUES ('";
				$sql .= $feat_ids[$i] == "" ?
				addslashes($feat_names[$i])."', '".addslashes($feat_descriptions[$i])."', '".$user_id."')" :
				addslashes($feat_names[$i])."', '".addslashes($feat_descriptions[$i])."', '".$user_id."', '".$feat_ids[$i]."')";
			} else {
				$sql = "UPDATE user_feat SET name = '".addslashes($feat_names[$i])."', description = '".addslashes($feat_descriptions[$i])."' WHERE id = ".$user_feat_ids[$i];
			}
			$db->query($sql);
		}
	} else {
		// remove any old feats
		$sql = "DELETE FROM user_feat WHERE user_id = " . $user_id;
		$db->query($sql);
	}

	// look for new trainings
	if (isset($_POST['training'])) {
		$training = $_POST['training'];
		$training_val = $_POST['training_val'];
		$training_magic = $_POST['training_magic'];
		$training_governing = $_POST['training_governing'];
		$ids = $_POST['training_ids'];

		// delete trainings not in ID list
		$sql = "DELETE FROM user_training WHERE user_id = " . $user_id . " AND id NOT IN ('" . implode("','", $ids) . "')";
		$db->query($sql);

		for ($i = 0; $i < count($training); $i++) {
			$vals = explode(":", $training[$i]);
			// update where ID not empty; insert new where ID empty
			if ($ids[$i] == "") {
				$sql = "INSERT INTO user_training (name, attribute_group, value, magic_school, governing_school, user_id) VALUES ('".addslashes($vals[0])."', '".$vals[1]."', '".$training_val[$i]."', '".$training_magic[$i]."', '".$training_governing[$i]."', '".$user_id."')";
			} else {
				$sql = "UPDATE user_training SET name = '".addslashes($vals[0])."', attribute_group = '".$vals[1]."', value = ".$training_val[$i]." WHERE id = ".$ids[$i];
			}
			$db->query($sql);
		}
	} else {
		// delete all trainings from user
		$sql = "DELETE FROM user_training WHERE user_id = " . $user_id;
		$db->query($sql);
	}

	// look for notes
	if (isset($_POST['notes'])) {

		$titles = $_POST['titles'];
		$notes = $_POST['notes'];
		$ids = $_POST['note_ids'];

		// delete notes not in ID list
		$sql = "DELETE FROM user_note WHERE user_id = " . $user_id . " AND id NOT IN ('" . implode("','", $ids) . "')";
		$db->query($sql);

		for ($i = 0; $i < count($notes); $i++) {
			// update where ID not empty; insert new where ID empty
			if ($ids[$i] == "") {
				$sql = "INSERT INTO user_note (title, note, user_id) VALUES ('".addslashes($titles[$i])."', '".addslashes($notes[$i])."', '".$user_id."')";
			} else {
				$sql = "UPDATE user_note SET title = '".addslashes($titles[$i])."', note = '".addslashes($notes[$i])."' WHERE id = ".$ids[$i];
			}
			$db->query($sql);
		}
	} else {
		// delete all notes from user
		$sql = "DELETE FROM user_note WHERE user_id = " . $user_id;
		$db->query($sql);
	}

	// look for weapons
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
		$weapon_crit = $_POST['weapon_crit'];
		$ids = $_POST['weapon_ids'];

		// delete weapons not in ID list
		$sql = "DELETE FROM user_weapon WHERE user_id = " . $user_id . " AND id NOT IN ('" . implode("','", $ids) . "')";
		$db->query($sql);

		// get 'equipped' count for weapons
		$equipped_1 = $_POST['weapon_1'];
		$equipped_2 = $_POST['weapon_2'];
		$equipped_3 = $_POST['weapon_3'];
		$weapon_equipped = [];
		for ($i = 0; $i < count($weapons); $i++) {
			$equipped = 0;
			$equipped += $weapons[$i] == $equipped_1 ? 1 : 0;
			$equipped += $weapons[$i] == $equipped_2 ? 1 : 0;
			$equipped += $weapons[$i] == $equipped_3 ? 1 : 0;
			$weapon_equipped[$i] = $equipped;
		}

		for ($i = 0; $i < count($weapons); $i++) {
			$weapon_damage[$i] = empty($weapon_damage[$i]) ? "NULL" : $weapon_damage[$i];
			$weapon_max_damage[$i] = empty($weapon_max_damage[$i]) ? "NULL" : $weapon_max_damage[$i];
			$weapon_range[$i] = empty($weapon_range[$i]) ? "NULL" : $weapon_range[$i];
			$weapon_defend[$i] = empty($weapon_defend[$i]) ? "NULL" : $weapon_defend[$i];
			$weapon_crit[$i] = empty($weapon_crit[$i]) ? "NULL" : $weapon_crit[$i];

			// update where ID not empty; insert new where ID empty
			if ($ids[$i] == "") {
				$sql = "INSERT INTO user_weapon (name, type, quantity, equipped, damage, max_damage, range_, rof, defend, crit, notes, weight, user_id) VALUES ('".addslashes($weapons[$i])."', '".$weapon_type[$i]."', '".addslashes($weapon_qty[$i])."', '".addslashes($weapon_equipped[$i])."', ".$weapon_damage[$i].", ".$weapon_max_damage[$i].", ".$weapon_range[$i].", '".addslashes($weapon_rof[$i])."', ".$weapon_defend[$i].", ".$weapon_crit[$i].", '".addslashes($weapon_notes[$i])."', ".$weapon_weight[$i].", '".$user_id."')";
			} else {
				$sql = "UPDATE user_weapon SET name = '".addslashes($weapons[$i])."', type = '".$weapon_type[$i]."', quantity = '".addslashes($weapon_qty[$i])."', equipped = '".addslashes($weapon_equipped[$i])."', damage = ".$weapon_damage[$i].", max_damage = ".$weapon_max_damage[$i].", range_ = ".$weapon_range[$i].", rof = '".addslashes($weapon_rof[$i])."', defend = ".$weapon_defend[$i].", crit = ".$weapon_crit[$i].", notes = '".addslashes($weapon_notes[$i])."', weight = ".$weapon_weight[$i]." WHERE id = ".$ids[$i];
			}
			$db->query($sql);
		}
	} else {
		// delete all weapons from user
		$sql = "DELETE FROM user_weapon WHERE user_id = " . $user_id;
		$db->query($sql);
	}

	// look for new protections
	if (isset($_POST['protections'])) {
		$protections = $_POST['protections'];
		$protection_bonus = $_POST['protection_bonus'];
		$protection_notes = $_POST['protection_notes'];
		$protection_weight = $_POST['protection_weight'];
		$protection_equipped = $_POST['protection_equipped'];
		$ids = $_POST['protection_ids'];

		// delete protections not in ID list
		$sql = "DELETE FROM user_protection WHERE user_id = " . $user_id . " AND id NOT IN ('" . implode("','", $ids) . "')";
		$db->query($sql);

		for ($i = 0; $i < count($protections); $i++) {
			// update where ID not empty; insert new where ID empty
			if ($ids[$i] == "") {
				$sql = "INSERT INTO user_protection (name, bonus, notes, weight, equipped, user_id) VALUES ('".addslashes($protections[$i])."', ".$protection_bonus[$i].", '".addslashes($protection_notes[$i])."', ".$protection_weight[$i].", ".$protection_equipped[$i].", '".$user_id."')";
			} else {
				$sql = "UPDATE user_protection SET name = '".addslashes($protections[$i])."', bonus = ".$protection_bonus[$i].", notes = '".addslashes($protection_notes[$i])."', weight = ".$protection_weight[$i].", equipped = ".$protection_equipped[$i]." WHERE id = ".$ids[$i];
			}
			$db->query($sql);
			// echo $db->error;
		}
	} else {
		// delete all protections from user
		$sql = "DELETE FROM user_protection WHERE user_id = " . $user_id;
		$db->query($sql);
	}

	// look for new healings
	if (isset($_POST['healings'])) {
		$healings = $_POST['healings'];
		$healing_quantity = $_POST['healing_quantity'];
		$healing_effect = $_POST['healing_effect'];
		$healing_weight = $_POST['healing_weight'];
		$ids = $_POST['healing_ids'];

		// delete healings not in ID list
		$sql = "DELETE FROM user_healing WHERE user_id = " . $user_id . " AND id NOT IN ('" . implode("','", $ids) . "')";
		$db->query($sql);

		for ($i = 0; $i < count($healings); $i++) {
			// update where ID not empty; insert new where ID empty
			if ($ids[$i] == "") {
				$sql = "INSERT INTO user_healing (name, quantity, effect, weight, user_id) VALUES ('".addslashes($healings[$i])."', '".addslashes($healing_quantity[$i])."', '".addslashes($healing_effect[$i])."', '".$healing_weight[$i]."', '".$user_id."')";
			} else {
				$sql = "UPDATE user_healing SET name = '".addslashes($healings[$i])."', quantity = '".addslashes($healing_quantity[$i])."', effect = '".addslashes($healing_effect[$i])."', weight = ".$healing_weight[$i]." WHERE id = ".$ids[$i];
			}
			$db->query($sql);
		}
	} else {
		// delete all healings from user
		$sql = "DELETE FROM user_healing WHERE user_id = " . $user_id;
		$db->query($sql);
	}

	// look for new misc items
	if (isset($_POST['misc'])) {
		$misc = $_POST['misc'];
		$misc_quantity = $_POST['misc_quantity'];
		$misc_notes = $_POST['misc_notes'];
		$misc_weight = $_POST['misc_weight'];
		$ids = $_POST['misc_ids'];

		// delete misc not in ID list
		$sql = "DELETE FROM user_misc WHERE user_id = " . $user_id . " AND id NOT IN ('" . implode("','", $ids) . "')";
		$db->query($sql);

		for ($i = 0; $i < count($misc); $i++) {
			// update where ID not empty; insert new where ID empty
			if ($ids[$i] == "") {
				$sql = "INSERT INTO user_misc (name, quantity, notes, weight, user_id) VALUES ('".addslashes($misc[$i])."', '".addslashes($misc_quantity[$i])."', '".addslashes($misc_notes[$i])."', '".$misc_weight[$i]."', '".$user_id."')";
			} else {
				$sql = "UPDATE user_misc SET name = '".addslashes($misc[$i])."', quantity = '".addslashes($misc_quantity[$i])."', notes = '".addslashes($misc_notes[$i])."', weight = ".$misc_weight[$i]." WHERE id = ".$ids[$i];
			}
			$db->query($sql);
		}
	} else {
		// delete all misc from user
		$sql = "DELETE FROM user_misc WHERE user_id = " . $user_id;
		$db->query($sql);
	}

	$db->close();
	header("Location: /?campaign=".$_POST['campaign_id']."&user=".$user_id,  true,  301 );  exit;
?>