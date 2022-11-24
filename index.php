<?php

	// establish database connection
	include_once('db_config.php');
	include_once('keys.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check for campaign parameter in url
	if (!isset($_GET["campaign"])) {
		// redirect to campaign select page
		header('Location: /select_campaign.php');
	}

	// get user list for dropdown nav
	$users = [];
	$sql = "SELECT * FROM user WHERE campaign_id = ".$_GET["campaign"]." ORDER BY character_name";
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	array_push($users, $row);
    }
  }

  // get feat_id list
  $feat_ids = [];
	$sql = "SELECT feat_id FROM campaign_feat WHERE campaign_id = ".$_GET["campaign"];
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	array_push($feat_ids, $row['feat_id']);
    }
  }

	// get active counts for each feat type
	$counts = [];
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'physical_trait' AND cost > 0";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['physical_pos_count'] = $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'physical_trait' AND cost < 0";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['physical_neg_count'] = $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'social_trait'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['social_count'] = $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'morale_trait'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['morale_count'] = $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'compelling_action'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['compelling_count'] = $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'profession'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['profession_count'] = $row['count'];
		}
	}
  
  // get feat list
	$feat_list = [];
	$sql = "SELECT * FROM feat_or_trait";
	if (count($feat_ids) > 0) {
		$sql .= " WHERE id IN (".implode(',',$feat_ids).")";
	}
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	array_push($feat_list, $row);
    }
  }

  // get feat requirements
  $feat_reqs = [];
	$sql = "SELECT feat_id, req_set_id, type, value FROM feat_or_trait_req_set JOIN feat_or_trait_req ON feat_or_trait_req_set.id = feat_or_trait_req.req_set_id";
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	array_push($feat_reqs, $row);
    }
  }

  // get campaign name
  $sql = "SELECT * FROM campaign WHERE id = ".$_GET["campaign"];
	$result = $db->query($sql);
	$campaign = "";
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	$campaign = $row;
    }
  }

	$feats = [];
	$trainings = [];
	$weapons = [];
	$protections = [];
	$healings = [];
	$misc = [];
	$notes = [];
	$awards = [];

	// check for user parameter in url
	if (isset($_GET["user"])) {
    $sql = "SELECT * FROM user WHERE id = ".$_GET["user"];
    $result = $db->query($sql);
    if ($result->num_rows === 1) {
	    while($row = $result->fetch_assoc()) {
	    	$user = $row;
	    	// get user awards
	    	$sql = "SELECT * FROM user_xp_award WHERE user_id = ".$_GET["user"];
	    	$result = $db->query($sql);
	    	if ($result) {
		    	while($row = $result->fetch_assoc()) {
		    		array_push($awards, $row);
		    	}
	    	}
	    	// get user feats
	    	$sql = "SELECT * FROM user_feat WHERE user_id = ".$_GET["user"];
	    	$result = $db->query($sql);
	    	if ($result) {
		    	while($row = $result->fetch_assoc()) {
		    		array_push($feats, $row);
		    	}
	    	}
	    	// get user trainings
	    	$sql = "SELECT * FROM user_training WHERE user_id = ".$_GET["user"];
	    	$result = $db->query($sql);
	    	if ($result) {
		    	while($row = $result->fetch_assoc()) {
		    		array_push($trainings, $row);
		    	}
	    	}
	    	// get user weapons
	    	$sql = "SELECT * FROM user_weapon WHERE user_id = ".$_GET["user"];
	    	$result = $db->query($sql);
	    	if ($result) {
		    	while($row = $result->fetch_assoc()) {
		    		array_push($weapons, $row);
		    	}
	    	}
	    	// get user protections
	    	$sql = "SELECT * FROM user_protection WHERE user_id = ".$_GET["user"];
	    	$result = $db->query($sql);
	    	if ($result) {
		    	while($row = $result->fetch_assoc()) {
		    		array_push($protections, $row);
		    	}
	    	}
	    	// get user healings
	    	$sql = "SELECT * FROM user_healing WHERE user_id = ".$_GET["user"];
	    	$result = $db->query($sql);
	    	if ($result) {
		    	while($row = $result->fetch_assoc()) {
		    		array_push($healings, $row);
		    	}
	    	}
	    	// get user misc
	    	$sql = "SELECT * FROM user_misc WHERE user_id = ".$_GET["user"];
	    	$result = $db->query($sql);
	    	if ($result) {
		    	while($row = $result->fetch_assoc()) {
		    		array_push($misc, $row);
		    	}
	    	}
	    	// get user notes
	    	$sql = "SELECT * FROM user_note WHERE user_id = ".$_GET["user"];
	    	$result = $db->query($sql);
	    	if ($result) {
		    	while($row = $result->fetch_assoc()) {
		    		array_push($notes, $row);
		    	}
	    	}

			}
    }
	}

	$db->close();

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, height=device-height,  initial-scale=1.0, user-scalable=no, user-scalable=0"/>
	<meta name="robots" content="noindex">
	<meta property="og:image" content="https://crabagain.com/assets/image/treasure-header-desaturated.jpg">
	<title><?php echo $campaign['name'] ?>!</title>
	<link rel="icon" type="image/png" href="/assets/image/favicon-pentacle.ico"/>

	<!-- Bootstrap -->
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
	<!-- jQuery UI -->
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Alegreya:ital,wght@0,400;1,400;1,600&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
	<!-- Custom Styles -->
	<link rel="stylesheet" type="text/css" href="/assets/style_v22_07_27.css">

</head>

<body>

	<!-- use div visibility to determine if we're on mobile -->
	<div id="is_mobile"></div>

	<!-- user menu -->
	<nav class="navbar">

	  <div class="nav-menu">
	    <div class="nav-item">
	       <span class="glyphicon" onclick="formSubmit()"><span class="nav-item-label"><i class="fa-solid fa-floppy-disk nav-icon"></i> Save Character Data</span></span>
	    </div>
	    <div class="nav-item">
	       <span id="attribute_pts_span" class="glyphicon <?php echo isset($user) && $user['attribute_pts'] == 0 ? 'disabled' : ''; ?>" onclick="allocateAttributePts(this)"><span class="nav-item-label"><i class="fa-solid fa-shield-heart nav-icon"></i> Allocate Attribute Points</span></span>
	    </div>
	    <?php
	    	if (isset($user) && $user['xp'] != 0) {
	    		echo '
				    <div class="nav-item">
				       <span class="glyphicon" data-toggle="modal" data-target="#gm_modal"><span class="nav-item-label"><i class="fa-solid fa-dice-d20 nav-icon"></i> GM Edit Mode</span></span>
				    </div>
				   ';
	    	}
	    ?>
	    <div class="nav-item">
	       <span class="glyphicon" onclick="settings()"><span class="nav-item-label"><i class="fa-solid fa-gear nav-icon"></i> Campaign Admin</span></span>
	    </div>
	    <div class="nav-item">
	       <span class="glyphicon" onclick="back()"><span class="nav-item-label"><i class="fa-solid fa-arrow-left nav-icon"></i> Change Campaign</span></span>
	    </div>
	  </div>

	  <!-- attribute point menu -->
	  <div class="attribute-pts">
	    <div class="attribute-count"></div>
	    <div><span class="glyphicon glyphicon-ok" onclick="endEditAttributes(true)"><span class="nav-item-label"> Accept Changes</span></span></div>
	    <div><span class="glyphicon glyphicon-remove" onclick="endEditAttributes(false)"><span class="nav-item-label"> Discard Changes</span></span></div>
	  </div>

	  <!-- GM edit menu -->
	  <div class="gm-menu">
	    <div><span class="glyphicon glyphicon-ok" onclick="endGMEdit(true)"><span class="nav-item-label"> Accept Changes</span></span></div>
	    <div><span class="glyphicon glyphicon-remove" onclick="endGMEdit(false)"><span class="nav-item-label"> Discard Changes</span></span></div>
	  </div>

	  <!-- hamburger menu -->
		<span class="glyphicon glyphicon-menu-hamburger" onclick="toggleMenu()"></span>

	  <!-- user help menu - visible only on character creation -->
	    <?php
	    	if (!isset($user) || $user['xp'] == 0) {
	    		echo '
	  				<span class="glyphicon glyphicon-info-sign help-menu" data-toggle="modal" data-target="#help_modal"></span>
	    		';
	    	}
	    ?>
	</nav>

	<div class="header">
		<div class="row">
			<div class="col-xs-4">
				<h1>Welcome to...<br><?php echo $campaign['name'] ?>!</h1>
			</div>
		</div>
	</div>

	<!-- character select -->
	<select class="form-control" id="user_select">
		<option value="">NEW CHARACTER</option>
		<?php
			foreach($users as $option) {
				echo '<option value='.$option['id'].' '.($option['id'] == $user['id'] ? 'selected' : '').'>'.$option['character_name'].'</option>';
			}
		?>
	</select>

	<!-- anchor links -->
	<select class="form-control" id="anchor_links">
		<option value="">JUMP TO SECTION...</option>
		<option value="#section_attack">Attack</option>
		<option value="#section_defense">Defense</option>
		<option value="#section_health">Health</option>
		<option value="#section_actions">Actions, Move, & Initiative</option>
		<option value="#section_motivators">Motivators</option>
		<option value="#section_attributes">Attributes</option>
		<option value="#section_feats">Feats</option>
		<option value="#section_items">Items</option>
		<option value="#section_weight">Weight Capacity</option>
		<option value="#section_notes">Notes</option>
	</select>

	<form id="user_form" method="post" action="/submit.php" novalidate>
		<input type="hidden" id="user_id" name="user_id" value="<?php echo isset($user) ? $user['id'] : '' ?>">
		<input type="hidden" id="campaign_id" name="campaign_id" value="<?php echo $_GET["campaign"] ?>">
		<input type="hidden" id="user_email" name="email" value="<?php echo isset($user) ? $user["email"] : '' ?>">
		<div class="row">
			<div class="col-md-6">

				<!-- section: name, level, xp -->
				<div class="section form-horizontal">
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="character_name">Name</label>
						<div class="col-sm-4 col-xs-8 mobile-pad-bottom">
							<input class="form-control" type="text" id="character_name" name="character_name" value="<?php echo isset($user) ? htmlspecialchars($user['character_name']) : '' ?>">
						</div>
						<!-- readonly, unless new character -->
						<label class="control-label col-sm-4 col-xs-4 smaller" for="attribute_pts">Attribute Pts</label>
						<div class="col-sm-2 col-xs-8">
							<input class="form-control" <?php echo isset($user) && $user['xp'] != 0 ? 'readonly' : 'type="number"' ?> min="0" id="attribute_pts" name="attribute_pts" value="<?php echo isset($user) ? htmlspecialchars($user['attribute_pts']) : 12 ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="xp">Experience</label>
						<div class="col-sm-4 col-xs-8 mobile-pad-bottom">
							<input class="form-control" readonly data-toggle="modal" data-target="#xp_modal" name="xp" id="xp" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['xp']) : 0 ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="level">Level</label>
						<div class="col-sm-4 col-xs-8">
							<?php
								$levels = [];
								$xp_total = 0;
								foreach (range(1,25) as $number) {
									$xp_total += 20 * $number;
									array_push($levels, $xp_total);
								}
								$level = 1;
								if (isset($user)) {
									$i = 2;
									foreach ($levels as $lvl) {
										if ($user['xp'] >= $lvl) {
											$level = $i++;
										}
									}
								}
							?>
							<input class="form-control" readonly name="level" id="level" value="<?php echo $level ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="morale">Morale</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control" type="number" name="morale" id="morale" min="-10" value="<?php echo isset($user) ? htmlspecialchars($user['morale']) : 0 ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="morale_effect">Effect</label>
						<div class="col-sm-6 col-xs-8">
							<input class="form-control" readonly id="morale_effect" name="morale_effect">
						</div>
					</div>
				</div>
				<!-- end section: name, level, xp -->

			</div>
			<div class="col-md-6">

				<!-- section: characteristics -->
				<div class="section form-horizontal">
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="race">Race</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control" type="text" name="race" value="<?php echo isset($user) ? htmlspecialchars($user['race']) : '' ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="age">Age</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control" type="text" name="age" id="age_text" value="<?php echo isset($user) ? htmlspecialchars($user['age']) : '' ?>">
							<input class="form-control hidden-number" type="number" id="age">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="gender">Gender</label>
						<div class="col-sm-2 col-xs-8 desktop-no-pad-left">
							<input class="form-control" type="text" name="gender" value="<?php echo isset($user) ? htmlspecialchars($user['gender']) : '' ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="height">Height</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control" type="text" name="height" id="height_text" value="<?php echo isset($user) ? htmlspecialchars($user['height']) : '' ?>">
							<input class="form-control hidden-number" type="number" id="height">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="weight">Weight</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control" type="text" name="weight" id="weight_text" value="<?php echo isset($user) ? htmlspecialchars($user['weight']) : '' ?>">
							<input class="form-control hidden-number" type="number" id="weight">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="eyes">Eyes</label>
						<div class="col-sm-2 col-xs-8 desktop-no-pad-left">
							<input class="form-control" type="text" name="eyes" value="<?php echo isset($user) ? htmlspecialchars($user['eyes']) : '' ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="hair">Hair</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control" type="text" name="hair" value="<?php echo isset($user) ? htmlspecialchars($user['hair']) : '' ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="other">Other</label>
						<div class="col-sm-6 col-xs-8 desktop-no-pad-left">
							<input class="form-control" type="text" name="other" value="<?php echo isset($user) ? htmlspecialchars($user['other']) : '' ?>">
						</div>
					</div>
				</div>
				<!-- end section: characteristics -->
				
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">

				<!-- section: weapons -->
				<div class="section form-horizontal">
					<div class="form-group">
						<div class="section-title" id="section_attack"><span>Attack</span> <i class="fa-solid icon-attack custom-icon"></i></div>
						<div class="row">

							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label col-md-12 center full-width" for="weapon_1">Weapon 1<span class="glyphicon glyphicon-chevron-down" id="weapon_1" onclick="toggleWeapon(this.id, this)"></span></label>
									<div class="col-md-12">
										<select class="form-control weapon-select" id="weapon_select_1" name="weapon_1" onchange="selectWeapon(1)">
											<option></option>
											<?php 
												foreach ($weapons as $weapon) {
													echo '<option value="'.$weapon['name'].'" '.($user['weapon_1'] == $weapon['name'] ? 'selected' : '').'>'.$weapon['name'].'</option>';
												}
											?>
										</select>
									</div>
								</div>
								<div id="weapon_1_container" class="weapon-container">
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_1_damage">Damage</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_damage_1" name="weapon_1_damage">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_1_crit">Critical</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_crit_1" name="weapon_1_crit">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_1_range">Range</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_range_1" name="weapon_1_range">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_1_rof">R o F</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_rof_1" name="weapon_1_rof">
										</div>
									</div>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label col-md-12 center full-width" for="weapon_2">Weapon 2<span class="glyphicon glyphicon-chevron-down" id="weapon_2" onclick="toggleWeapon(this.id, this)"></span></label>
									<div class="col-md-12">
										<select class="form-control weapon-select" id="weapon_select_2" name="weapon_2" onchange="selectWeapon(2)">
											<option></option>
											<?php 
												foreach ($weapons as $weapon) {
													echo '<option value="'.$weapon['name'].'" '.($user['weapon_2'] == $weapon['name'] ? 'selected' : '').'>'.$weapon['name'].'</option>';
												}
											?>
										</select>
									</div>
								</div>
								<div id="weapon_2_container" class="weapon-container">
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_2_damage">Damage</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_damage_2" name="weapon_2_damage">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_2_crit">Critical</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_crit_2" name="weapon_2_crit">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_2_range">Range</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_range_2" name="weapon_2_range">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_2_rof">R o F</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_rof_2" name="weapon_2_rof">
										</div>
									</div>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label col-md-12 center full-width" for="weapon_3">Weapon 3<span class="glyphicon glyphicon-chevron-down" id="weapon_3" onclick="toggleWeapon(this.id, this)"></span></label>
									<div class="col-md-12">
										<select class="form-control weapon-select" id="weapon_select_3" name="weapon_3" onchange="selectWeapon(3)">
											<option></option>
											<?php 
												foreach ($weapons as $weapon) {
													echo '<option value="'.$weapon['name'].'" '.($user['weapon_3'] == $weapon['name'] ? 'selected' : '').'>'.$weapon['name'].'</option>';
												}
											?>
										</select>
									</div>
								</div>
								<div id="weapon_3_container" class="weapon-container">
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_3_damage">Damage</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_damage_3" name="weapon_3_damage">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_3_crit">Critical</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_crit_3" name="weapon_3_crit">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_3_range">Range</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_range_3" name="weapon_3_range">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_3_rof">R o F</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_rof_3" name="weapon_3_rof">
										</div>
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>
				<!-- end section: weapons -->

			</div>
			<div class="col-md-6">

				<!-- section: defense -->
				<div class="section form-horizontal">
					<div class="section-title" id="section_defense"><span>Defense</span> <i class="fa-solid fa-shield-halved"></i></div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="toughness">Toughness</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<?php
								$toughness = isset($user) ? (
									$user['strength'] >= 0 ?
										floor($user['strength']/2) :
										(ceil($user['strength']/3) == 0 ? 0 : ceil($user['strength']/3))
								) : 0;
							?>
							<input class="form-control" readonly name="toughness" id="toughness" value="<?php echo $toughness ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="defend">Defend</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<?php
								$defend = isset($user) ? 10 + $user['agility'] : 10;
								// add size modifier
								$size_modifier = 0;
								if (isset($user)) {
									$size_modifier = $user['size'] == "Small" ? 2 : ($user['size'] == "Large" ? -2 : 0);
									$defend += $size_modifier;
								}
							?>
							<input class="form-control" readonly name="defend" id="defend" value="<?php echo $defend ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="dodge">Dodge</label>
						<div class="col-sm-2 col-xs-8">
							<?php
								$dodge = isset($user) ? (
									$user['agility'] >= 0 ?
										floor($user['agility']/2) :
										(ceil($user['agility']/3) == 0 ? 0 : ceil($user['agility']/3))
								) : 0;
								// add size modifier
								$dodge += $size_modifier;
							?>
							<input class="form-control" readonly name="dodge" id="dodge" value="<?php echo $dodge ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="fear">Fear</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control" type="text" name="fear" id="fear_text" value="<?php echo isset($user) ? htmlspecialchars($user['fear']) : '' ?>">
							<input class="form-control hidden-number" type="number" id="fear">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="poison">Poison</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control" type="text" name="poison" id="poison_text" value="<?php echo isset($user) ? htmlspecialchars($user['poison']) : '' ?>">
							<input class="form-control hidden-number" type="number" id="poison">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="disease">Disease</label>
						<div class="col-sm-2 col-xs-8">
							<input class="form-control" type="text" name="disease" id="disease_text" value="<?php echo isset($user) ? htmlspecialchars($user['disease']) : '' ?>">
							<input class="form-control hidden-number" type="number" id="disease">
						</div>
					</div>
				</div>
				<!-- end section: defense -->

				<!-- section: health -->
				<div class="section form-horizontal">
					<div class="section-title" id="section_health"><span>Health</span> <i class="fa-solid fa-heart"></i></div>
					<div class="form-group">

						<div class="col-sm-4">
							<div class="row">
								<label class="control-label col-sm-12 center full-width" for="damage">Resilience</label>
							</div>
							<div class="row">
								<div class="col-xs-5 no-pad">
									<input class="form-control" id="damage" type="number" name="damage" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['damage']) : 0 ?>">
								</div>
								<div class="col-xs-2 center no-pad">
									/
								</div>
								<div class="col-xs-5 no-pad">
									<?php 
										$resilience = isset($user) ? (
											$user['fortitude'] >= 0 ? 
												3 + floor($user['fortitude']/2) :
												3 + ceil($user['fortitude']/3)
										) : 3;
									?>
									<input class="form-control" readonly id="resilience" name="resilience" value="<?php echo $resilience ?>">
								</div>
							</div>
						</div>

						<div class="col-sm-4">
							<div class="row">
								<label class="control-label col-sm-12 center full-width" for="wounds">Wounds</label>
							</div>
							<div class="row">
								<div class="col-xs-5 no-pad">
									<input class="form-control" id="wounds" type="number" name="wounds" min="0" max="3" value="<?php echo isset($user) ? htmlspecialchars($user['wounds']) : 0 ?>">
								</div>
								<div class="col-xs-2 center no-pad">
									/
								</div>
								<div class="col-xs-5 center no-pad">
									3
								</div>
							</div>
						</div>

						<div class="col-sm-4">
							<div class="row">
								<label class="control-label col-sm-12 center full-width penalty" for="wound_penalty">Penalty</label>
							</div>
							<div class="row">
								<div class="col-sm-12 no-pad">
									<input class="form-control" type="text" name="wound_penalty" id="wound_penalty_text" value="<?php echo isset($user) ? htmlspecialchars($user['wound_penalty']) : '' ?>">
									<input class="form-control hidden-number" type="number" id="wound_penalty">
								</div>
							</div>
						</div>

					</div>
				</div>
				<!-- end section: health -->
				
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">

				<!-- section: actions, move -->
				<div class="section form-horizontal">
					<div class="section-title" id="section_actions"><span>Actions, Move, Initiative</span> <i class="fa-solid fa-hourglass"></i></div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="standard">Standard</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<?php
								$standard = isset($user) ? (
									$user['speed'] >= 0 ?
										1 + floor($user['speed']/4) :
										1 + round($user['speed']/6)
								) : 1;
							?>
							<input class="form-control" readonly name="standard" id="standard" value="<?php echo $standard ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="quick">Quick</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<?php
								$quick = isset($user) ? (
									$user['speed'] >= 0 ?
										($user['speed']/2 % 2 == 0 ? 0 : 1) :
										(ceil($user['speed']/3) % 2 == 0 ? 0 : 1)
								) : 0;
							?>
							<input class="form-control" readonly name="quick" id="quick" value="<?php echo $quick ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="free">Free</label>
						<div class="col-sm-2 col-xs-8">
							<input class="form-control" type="number" name="free" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['free']) : '' ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="move">Move</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<?php
								$move = isset($user) ? ($user['size'] == "Small" ? 0.5 :
									($user['size'] == "Large" ? 1.5 : 1)
								) : 1;
							?>
							<input class="form-control" readonly name="move" id="move" value="<?php echo $move ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="initiative">Initiative</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<?php
								$initiative = isset($user) ? (
									$user['awareness'] >= 0 ?
										10 - floor($user['awareness']/2) :
										10 - ceil($user['awareness']/3)
								) : 10;
							?>
							<input class="form-control" readonly name="initiative" id="initiative" value="<?php echo $initiative ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4 penalty" for="move_penalty">Penalty</label>
						<div class="col-sm-2 col-xs-8">
							<input class="form-control" type="text" name="move_penalty" id="move_penalty_text" value="<?php echo isset($user) ? htmlspecialchars($user['move_penalty']) : '' ?>">
							<input class="form-control hidden-number" type="number" id="move_penalty">
						</div>
					</div>
				</div>
				<!-- end section: actions, move -->

				<!-- section: attributes -->
				<div class="section form-horizontal">
					<div class="section-title" id="section_attributes"><span>Attributes</span> <i class="fa-solid fa-dice"></i></div>

					<div class="form-group">
						<div class="col-sm-6 attribute-col" id="col_strength">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="strength"><span class="attribute-name">Strength</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_strength" onclick="toggleHidden('col_strength')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="strength_text"></span>
										<input type="hidden" name="strength" id="strength_val" value="<?php echo isset($user) ? $user['strength'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('strength', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('strength', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Strength"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Strength')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-6 attribute-col" id="col_fortitude">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="fortitude"><span class="attribute-name">Fortitude</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_fortitude" onclick="toggleHidden('col_fortitude')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="fortitude_text"></span>
										<input type="hidden" name="fortitude" id="fortitude_val" value="<?php echo isset($user) ? $user['fortitude'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('fortitude', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('fortitude', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Fortitude"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Fortitude')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-6 attribute-col" id="col_speed">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="speed"><span class="attribute-name">Speed</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_speed" onclick="toggleHidden('col_speed')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="speed_text"></span>
										<input type="hidden" name="speed" id="speed_val" value="<?php echo isset($user) ? $user['speed'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('speed', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('speed', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Speed"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Speed')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-6 attribute-col" id="col_agility">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="agility"><span class="attribute-name">Agility</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_agility" onclick="toggleHidden('col_agility')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="agility_text"></span>
										<input type="hidden" name="agility" id="agility_val" value="<?php echo isset($user) ? $user['agility'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('agility', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('agility', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Agility"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Agility')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-6 attribute-col" id="col_precision">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="precision_"><span class="attribute-name">Precision</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_precision" onclick="toggleHidden('col_precision')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="precision__text"></span>
										<input type="hidden" name="precision_" id="precision__val" value="<?php echo isset($user) ? $user['precision_'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('precision_', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('precision_', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Precision"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Precision')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-6 attribute-col" id="col_awareness">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="awareness"><span class="attribute-name">Awareness</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_awareness" onclick="toggleHidden('col_awareness')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="awareness_text"></span>
										<input type="hidden" name="awareness" id="awareness_val" value="<?php echo isset($user) ? $user['awareness'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('awareness', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('awareness', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Awareness"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Awareness')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-6 attribute-col" id="col_allure">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="allure"><span class="attribute-name">Allure</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_allure" onclick="toggleHidden('col_allure')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="allure_text"></span>
										<input type="hidden" name="allure" id="allure_val" value="<?php echo isset($user) ? $user['allure'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('allure', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('allure', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Allure"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Allure')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-6 attribute-col" id="col_deception">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="deception"><span class="attribute-name">Deception</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_deception" onclick="toggleHidden('col_deception')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="deception_text"></span>
										<input type="hidden" name="deception" id="deception_val" value="<?php echo isset($user) ? $user['deception'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('deception', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('deception', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Deception"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Deception')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-6 attribute-col" id="col_intellect">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="intellect"><span class="attribute-name">Intellect</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_intellect" onclick="toggleHidden('col_intellect')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="intellect_text"></span>
										<input type="hidden" name="intellect" id="intellect_val" value="<?php echo isset($user) ? $user['intellect'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('intellect', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('intellect', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Intellect"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Intellect')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-6 attribute-col" id="col_innovation">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="innovation"><span class="attribute-name">Innovation</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_innovation" onclick="toggleHidden('col_innovation')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="innovation_text"></span>
										<input type="hidden" name="innovation" id="innovation_val" value="<?php echo isset($user) ? $user['innovation'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('innovation', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('innovation', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Innovation"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Innovation')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-6 attribute-col" id="col_intuition">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="intuition"><span class="attribute-name">Intution</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_intuition" onclick="toggleHidden('col_intuition')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="intuition_text"></span>
										<input type="hidden" name="intuition" id="intuition_val" value="<?php echo isset($user) ? $user['intuition'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('intuition', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('intuition', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Intution"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Intution')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-6 attribute-col" id="col_vitality">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="vitality"><span class="attribute-name">Vitality</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_vitality" onclick="toggleHidden('col_vitality')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="vitality_text"></span>
										<input type="hidden" name="vitality" id="vitality_val" value="<?php echo isset($user) ? $user['vitality'] : '' ?>">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('vitality', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('vitality', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Vitality"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" onclick="newTrainingModal('Vitality')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- end section: attributes -->

			</div>
			<div class="col-md-6">

				<!-- section: motivators -->
				<div class="section form-horizontal">
					<div class="section-title section-motivators">
						<span class="motivator-title"><span>Motivators</span></span> <i class="fa-solid fa-yin-yang"></i>
						<div class="form-group motivator-bonus">
							<label for="bonuses">Bonuses:</label>
							<?php
								// get two highest motivator values
								if (isset($user)) {
									$motivators = [];
									array_push($motivators, $user['motivator_1_pts'] == '' ? 0 : $user['motivator_1_pts']);
									array_push($motivators, $user['motivator_2_pts'] == '' ? 0 : $user['motivator_2_pts']);
									array_push($motivators, $user['motivator_3_pts'] == '' ? 0 : $user['motivator_3_pts']);
									array_push($motivators, $user['motivator_4_pts'] == '' ? 0 : $user['motivator_4_pts']);
									arsort($motivators);
									$total_pts = intval($motivators[0]) + intval($motivators[1]) + intval($motivators[2]);
									$bonuses = $total_pts >= 64 ? 5 : ($total_pts >= 32 ? 4 : ($total_pts >= 16 ? 3 : ($total_pts >= 8 ? 2 : ($total_pts >= 4 ? 1 : 0))));
								} else {
									$bonuses = 0;
								}
							?>
							<input class="form-control" readonly name="bonuses" id="bonuses" value="<?php echo $bonuses ?>">
						</div>
					</div>

					<div class="form-group no-margin">
						<div class="col-xs-3 no-pad-mobile no-pad-left">
							<input class="form-control" type="text" name="motivator_1" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_1']) : '' ?>">
						</div>
						<label class="control-label col-xs-2 no-pad-mobile" for="motivator_1_pts">Points:</label>
						<div class="col-xs-1 no-pad">
							<input class="form-control motivator-pts" type="number" name="motivator_1_pts" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_1_pts']) : '' ?>">
						</div>

						<div class="col-xs-3 no-pad-mobile pad-left-mobile">
							<input class="form-control" type="text" name="motivator_2" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_2']) : '' ?>">
						</div>
						<label class="control-label col-xs-2 no-pad-mobile" for="motivator_2_pts">Points:</label>
						<div class="col-xs-1 no-pad">
							<input class="form-control motivator-pts" type="number" name="motivator_2_pts" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_2_pts']) : '' ?>">
						</div>
					</div>

					<div class="form-group no-margin">
						<div class="col-xs-3 no-pad-mobile no-pad-left">
							<input class="form-control" type="text" name="motivator_3" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_3']) : '' ?>">
						</div>
						<label class="control-label col-xs-2 no-pad-mobile" for="motivator_3_pts">Points:</label>
						<div class="col-xs-1 no-pad">
							<input class="form-control motivator-pts" type="number" name="motivator_3_pts" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_3_pts']) : '' ?>">
						</div>

						<div class="col-xs-3 no-pad-mobile pad-left-mobile">
							<input class="form-control" type="text" name="motivator_4" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_4']) : '' ?>">
						</div>
						<label class="control-label col-xs-2 no-pad-mobile" for="motivator_4_pts">Points:</label>
						<div class="col-xs-1 no-pad">
							<input class="form-control motivator-pts" type="number" name="motivator_4_pts" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_4_pts']) : '' ?>">
						</div>
					</div>

				</div>
				<!-- end section: motivators -->

				<!-- section: feats & traits -->
				<div class="section form-horizontal">
					<div class="section-title" id="section_feats"><span>Feats & Traits</span> <i class="fa-solid fa-trophy"></i></div>
					<div class="form-group">
						<div class="col-sm-12">
							<div id="feats">
								<div class="feat" id="size" data-toggle="modal" data-target="#edit_size_modal">
									<p class="feat-title">Size : </p>
						    	<?php
						    		$size = isset($user['size']) ? $user['size'] : 'Medium';
						    	?>
									<p id="character_size_text"><?php echo $size ?></p>
									<input type="hidden" name="size" id="character_size_val" value="<?php echo $size ?>">
								</div>
							</div>
						</div>
					</div>
					<button type="button" class="btn btn-default hidden-icon" id="new_feat_btn"><span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target="#new_feat_modal"></span></button>
				</div>
				<!-- end section: feats & traits -->
				
			</div>

			<!-- section: weapons -->
			<div class="col-md-12">
				<div class="section form-horizontal">
					<div class="section-title" id="section_items"><span>Weapons</span> <i class="fa-solid icon-sword custom-icon"></i></div>
					<div class="form-group">
						<label class="control-label col-xs-3 resize-mobile center" for="weapons[]">Item</label>
						<label class="control-label col-xs-1 resize-mobile center" for="weapon_qty[]">Qty</label>
						<label class="control-label col-xs-1 resize-mobile center" id="weapon_dmg_label" for="weapon_damage[]">Damage</label>
						<label class="control-label col-xs-5 resize-mobile center" id="weapon_note_label" for="weapon_notes[]">Notes</label>
						<label class="control-label col-xs-1 resize-mobile center" for="weapon_weight[]">Weight</label>
						<label class="control-label col-xs-1 resize-mobile center" for=""></label>
					</div>
					<div id="weapons"></div>
					<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target="#new_weapon_modal"></span></button>
				</div>
			</div>
			<!-- end section: weapons -->

			<!-- section: protection -->
			<div class="col-md-12">
				<div class="section form-horizontal">
					<div class="section-title"><span>Protection</span> <i class="fa-solid icon-protection custom-icon"></i></div>
					<div class="form-group">
						<label class="control-label col-xs-1 col-icon resize-mobile" for="_eqip"></label>
						<label class="control-label col-xs-3 col-icon-right resize-mobile center" for="protections[]">Item</label>
						<label class="control-label col-xs-1 resize-mobile center" for="protection_bonus[]">Bonus</label>
						<label class="control-label col-xs-5 resize-mobile center" for="protection_notes[]">Notes</label>
						<label class="control-label col-xs-1 resize-mobile center" for="protection_weight[]">Weight</label>
						<label class="control-label col-xs-1 resize-mobile center" for=""></label>
					</div>
					<div id="protections"></div>
					<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target="#new_protection_modal"></span></button>
				</div>
			</div>
			<!-- end section: protection -->

			<!-- section: healing -->
			<div class="col-md-12">
				<div class="section form-horizontal">
					<div class="section-title"><span>Healings, Potions, & Drugs</span> <i class="fa-solid fa-flask"></i></div>
					<div class="form-group">
						<label class="control-label col-xs-4 resize-mobile center" for="healings[]">Item</label>
						<label class="control-label col-xs-1 resize-mobile center" for="healing_quantity[]">Qty</label>
						<label class="control-label col-xs-5 resize-mobile center" for="healing_effect[]">Effect</label>
						<label class="control-label col-xs-1 resize-mobile center" for="healing_weight[]">Weight</label>
						<label class="control-label col-xs-1 resize-mobile center" for=""></label>
					</div>
					<div id="healings"></div>
					<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target="#new_healing_modal"></span></button>
				</div>
			</div>
			<!-- end section: healing -->

			<!-- section: misc -->
			<div class="col-md-12">
				<div class="section form-horizontal">
					<div class="section-title"><span>Misc & Special Items</span> <i class="fa-solid icon-misc custom-icon"></i></div>
					<div class="form-group">
						<label class="control-label col-xs-4 resize-mobile center" for="misc[]">Item</label>
						<label class="control-label col-xs-1 resize-mobile center" for="misc_quantity[]">Qty</label>
						<label class="control-label col-xs-5 resize-mobile center" for="misc_notes[]">Notes</label>
						<label class="control-label col-xs-1 resize-mobile center" for="misc_weight[]">Weight</label>
						<label class="control-label col-xs-1 resize-mobile center" for=""></label>
					</div>
					<div id="misc"></div>
					<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target="#new_misc_modal"></span></button>
				</div>
			</div>
			<!-- end section: misc -->

			<!-- section: weight -->
			<div class="col-md-12">
				<div class="section form-horizontal">
					<div class="form-group">
						<label class="control-label col-xs-10 total-weight align-right" for="total_weight">Total Weight</label>
						<div class="col-xs-2 total-weight">
							<input class="form-control" readonly id="total_weight" name="total_weight" value="0">
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-12">
				<div class="section form-horizontal">
					<div class="section-title" id="section_weight"><span>Weight Capacity</span> <i class="fa-solid fa-scale-balanced"></i></div>
					<div class="form-group">
						<label class="control-label col-xs-3 center resize-mobile-small" for="unhindered">Unhindered (1/4)</label>
						<label class="control-label col-xs-3 center resize-mobile-small" for="encumbered">Encumbered (1/2)</label>
						<label class="control-label col-xs-3 center resize-mobile-small" for="burdened">Burdened (3/4)</label>
						<label class="control-label col-xs-3 center resize-mobile-small" for="overburdened">Overburdened (Full)</label>

						<div class="col-xs-3">
							<?php
								$base = isset($user) ? 100 + 20 * $user['strength'] : 100;
							?>
							<input class="form-control" readonly name="unhindered" id="unhindered" value="<?php echo $base / 4 ?>">
						</div>
						<div class="col-xs-3">
							<input class="form-control" readonly name="encumbered" id="encumbered" value="<?php echo $base / 2 ?>">
						</div>
						<div class="col-xs-3">
							<input class="form-control" readonly name="burdened" id="burdened" value="<?php echo $base / 4 * 3 ?>">
						</div>
						<div class="col-xs-3">
							<input class="form-control" readonly name="overburdened" id="overburdened" value="<?php echo $base ?>">
						</div>

						<p class="col-xs-3"></p>
						<p class="col-xs-3 center resize-mobile-small">(-1 Quick Action)</p>
						<p class="col-xs-3 center resize-mobile-small">(-1 Quick Action, <br class="mobile-break">-0.5 Move)</p>
						<p class="col-xs-3 center resize-mobile-small">(-1 Quick Action, <br class="mobile-break">-1 Move)</p>
					</div>
				</div>
			</div>
			<!-- end section: weight -->

			<!-- section: notes -->
			<div class="col-md-12">
				<div class="section form-horizontal">
					<div class="section-title" id="section_notes"><span>Notes</span> <i class="fa-solid fa-scroll"></i></div>
					<div class="form-group">
						<div class="col-xs-12">
							<ul id="notes"></ul>
						</div>
						<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target="#new_note_modal"></span></button>
					</div>
				</div>
			</div>
			<!-- end section: notes -->

			<!-- section: background -->
			<div class="col-md-12">
				<div class="section form-horizontal">
					<div class="section-title"><span>Character Background</span> 
						<!-- <i class="fa-solid icon-path custom-icon"></i> -->
						<i class="fa-solid fa-signs-post"></i>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<textarea class="form-control" rows="6" name="background" id="background" maxlength="2000"><?php echo isset($user) ? htmlspecialchars($user['background']) : '' ?></textarea>
						</div>
					</div>
				</div>
			</div>
			<!-- end section: background -->

		</div>
		<input type="hidden" name="password" id="password_val">
		<input type="hidden" name="recaptcha_response" id="recaptcha_response">
	</form>

	<!-- xp modal -->
  <div class="modal" id="xp_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Experience Points</h4>
        </div>
        <div class="modal-body">
        	<!-- <h3 class="center">Current XP: <span id="xp_text"><?php echo isset($user) ? $user['xp'] : 0 ?></span></h3> -->
        	<!-- get xp to next level -->
					<?php
						$current_xp = isset($user) ? $user['xp'] : 0;
						$next_level = 0;
						foreach ($levels as $lvl) {
							if ($current_xp < $lvl) {
								$next_level = $lvl;
								break;
							}
						}
					?>
        	<h3 class="center">Next Level: <span id="next_level"><?php echo $next_level ?></span> xp</h3>
        	<!-- input for adding xp -->
        	<!-- <div class="add-xp">
	        	<h3 class="center">Add XP:</h3>
	        	<input class="form-control" type="number" id="add_xp">
	        	<div class="button-bar">
	        		<button type="button" class="btn btn-primary" onclick="addXP()"><span class="glyphicon glyphicon-plus-sign"></span></button>
	        	</div>
        	</div> -->
        	<div class="button-bar">
	        	<!-- <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="setXP()">Ok</button> -->
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

  <!-- GM edit modal -->
  <div class="modal" id="gm_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="gm_title">GM Edit Mode</h4>
        </div>
        <div class="modal-body">
        	<h4 class="control-label center">What's the secret word?</h4>
        	<input class="form-control" type="text" id="gm_password">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="GMEditMode()">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- edit size modal -->
  <div class="modal" id="edit_size_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Character Size</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Please select your character size</label>
        	<?php
        		$size = isset($user['size']) ? $user['size'] : 'Medium';
        	?>
        	<select class="form-control" id="character_size_select">
        		<option value="Small" <?php echo $size == "Small" ? 'selected' : '' ?>>Small</option>
        		<option value="Medium" <?php echo $size == "Medium" ? 'selected' : '' ?>>Medium</option>
        		<option value="Large" <?php echo $size == "Large" ? 'selected' : '' ?>>Large</option>
        	</select>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="editSize()">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new note modal -->
  <div class="modal" id="new_note_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="note_modal_title">New Note</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Title</label>
        	<input class="form-control" type="text" id="note_title">
        	<label class="control-label">Note</label>
        	<textarea class="form-control" id="note_content" rows="10" maxlength="2000"></textarea>
        	<input type="hidden" id="note_id">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newNote()">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new feat modal -->
  <div class="modal" id="new_feat_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="feat_modal_title">New Feat/Trait</h4>
        </div>
        <div class="modal-body">
        	<!-- show dropdown only during character creation -->
        	<label class="control-label <?php echo isset($user) && $user['xp'] != 0 ? 'hidden' : ''; ?>" id="select_feat_type_label">Type</label>
        	<select class="form-control <?php echo isset($user) && $user['xp'] != 0 ? 'hidden' : ''; ?>" id="select_feat_type">
        		<option value="feat_name">Standard Feat</option>
        		<!-- hide options if their counts are zero -->
        		<option value="social_trait_name" <?php echo count($feat_ids) > 0 && $counts['social_count'] == 0 ? 'hidden' : '' ?>>Social Trait</option>
        		<option value="physical_trait_pos_name" <?php echo count($feat_ids) > 0 && $counts['physical_pos_count'] == 0 ? 'hidden' : '' ?>>Physical Trait (Positive)</option>
        		<option value="physical_trait_neg_name" <?php echo count($feat_ids) > 0 && $counts['physical_neg_count'] == 0 ? 'hidden' : '' ?>>Physical Trait (Negative)</option>
        		<option value="morale_trait_name" <?php echo count($feat_ids) > 0 && $counts['morale_count'] == 0 ? 'hidden' : '' ?>>Morale Trait</option>
        		<option value="compelling_action_name" <?php echo count($feat_ids) > 0 && $counts['compelling_count'] == 0 ? 'hidden' : '' ?>>Compelling Action</option>
        		<option value="profession_name" <?php echo count($feat_ids) > 0 && $counts['profession_count'] == 0 ? 'hidden' : '' ?>>Profession</option>
        	</select>
        	<label class="control-label">Name</label>
        	<input type="hidden" id="feat_name_val">
        	<input class="form-control clearable feat-type" type="text" id="feat_name">
        	<select class="form-control feat-type feat-select hidden" id="social_trait_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'social_trait') {
        					echo "<option value='".str_replace('\'','',$feat['name'])."'>".$feat['name']."</option>";
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="physical_trait_pos_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'physical_trait' && $feat['cost'] > 0) {
        					echo "<option value='".str_replace('\'','',$feat['name'])."'>".$feat['name']."</option>";
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="physical_trait_neg_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'physical_trait' && $feat['cost'] < 0) {
        					echo "<option value='".str_replace('\'','',$feat['name'])."'>".$feat['name']."</option>";
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="compelling_action_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'compelling_action') {
        					echo "<option value='".str_replace('\'','',$feat['name'])."'>".$feat['name']."</option>";
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="profession_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'profession') {
        					echo "<option value='".str_replace('\'','',$feat['name'])."'>".$feat['name']."</option>";
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="morale_trait_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'morale_trait') {
        					echo "<option value='".str_replace('\'','',$feat['name'])."'>".$feat['name']."</option>";
        				}
        			}
        		?>
        	</select>
        	<label class="control-label">Description</label>
        	<textarea class="form-control" id="feat_description" rows="6" maxlength="2000"></textarea>
        	<input type="hidden" id="feat_id">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newFeat()" id="feat_submit_btn">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new training modal -->
  <div class="modal" id="new_training_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="training_modal_title">New Skill Training</h4>
        </div>
        <div class="modal-body">
        	<h4 class="control-label">Skill / Training Name</h4>
        	<input class="form-control" type="text" id="training_name">
        	<br>
        	<div id="skill_type">
        		<h4 class="control-label">
        			Skill / Training Type
        		</h4>
	        	<div class="form-check">
		        	<input class="form-check-input" type="radio" name="skill_type" id="unique" value="4">
		        	<label class="form-check-label" for="unique">Unique Skill (4 attribute pts)</label>
	        	</div>
	        	<div class="form-check">
		        	<input class="form-check-input" type="radio" name="skill_type" id="training" value="2">
		        	<label class="form-check-label" for="training">Training (2 attribute pts)</label>
	        	</div>
	        	<div class="form-check">
		        	<input class="form-check-input" type="radio" name="skill_type" id="focus" value="1">
		        	<label class="form-check-label" for="focus">Focus (1 attribute pt)</label>
	        	</div>
        	</div>
        	<input type="hidden" id="attribute_type">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newTraining()">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new weapon modal -->
  <div class="modal" id="new_weapon_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="weapon_modal_title">New Weapon</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Weapon Type</label>
        	<select class="form-control" id="weapon_type">
        		<option value="Melee">Melee</option>
        		<option value="Ranged">Ranged</option>
        	</select>
        	<label class="control-label">Weapon Name*</label>
        	<input class="form-control" type="text" id="weapon_name">
        	<label class="control-label">Quantity</label>
        	<input class="form-control" type="text" id="weapon_qty">
        	<label class="control-label">Damage*</label>
        	<input class="form-control" type="number" min="0" id="weapon_damage">
        	<label class="control-label">Max Damage</label>
        	<input class="form-control" type="number" min="0" id="weapon_max_damage">
        	<label class="control-label">Range</label>
        	<input class="form-control" type="number" min="0" id="weapon_range">
        	<label class="control-label">Rate of Fire</label>
        	<input class="form-control" type="text" id="weapon_rof">
        	<label class="control-label">Defend Bonus</label>
        	<input class="form-control" type="number" min="0" id="weapon_defend">
        	<label class="control-label">Critical Threat Range Bonus</label>
        	<input class="form-control" type="number" min="0" id="weapon_crit">
        	<label class="control-label">Other Notes</label>
        	<input class="form-control" type="text" id="weapon_notes">
        	<label class="control-label">Weight</label>
        	<input class="form-control" type="number" min="0" id="weapon_weight">
        	<input type="hidden" id="weapon_id">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" onclick="newWeapon()">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new protection modal -->
  <div class="modal" id="new_protection_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="protection_modal_title">New Protection</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Protection Name*</label>
        	<input class="form-control" type="text" id="protection_name">
        	<label class="control-label">Bonus</label>
        	<input class="form-control" type="number" min="0" id="protection_bonus">
        	<label class="control-label">Notes</label>
        	<input class="form-control" type="text" id="protection_notes">
        	<label class="control-label">Weight</label>
        	<input class="form-control" type="number" min="0" id="protection_weight">
        	<input type="hidden" id="protection_id">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" onclick="newProtection()">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new healing modal -->
  <div class="modal" id="new_healing_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="healing_modal_title">New Healing/Potion/Drug</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Item Name*</label>
        	<input class="form-control" type="text" id="healing_name">
        	<label class="control-label">Quantity</label>
        	<input class="form-control" type="text" id="healing_quantity">
        	<label class="control-label">Effect</label>
        	<input class="form-control" type="text" id="healing_effect">
        	<label class="control-label">Weight</label>
        	<input class="form-control" type="number" min="0" id="healing_weight">
        	<input type="hidden" id="healing_id">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" onclick="newHealing()">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new miscellaneous modal -->
  <div class="modal" id="new_misc_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="misc_modal_title">New Miscellaneous Item</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Item Name*</label>
        	<input class="form-control" type="text" id="misc_name">
        	<label class="control-label">Quantity</label>
        	<input class="form-control" type="text" id="misc_quantity">
        	<label class="control-label">Notes</label>
        	<input class="form-control" type="text" id="misc_notes">
        	<label class="control-label">Weight</label>
        	<input class="form-control" type="number" min="0" id="misc_weight">
        	<input type="hidden" id="misc_id">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" onclick="newMisc()">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new password modal -->
  <div class="modal" id="new_password_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">New Character</h4>
        </div>
        <div class="modal-body">
        	<h5>Please set a password for your new character. Also, you should probably write it down or something.</h5>
        	<input class="form-control" type="password" id="new_password">
        	<label class="control-label" for="password_conf">Confirm password</label>
        	<input class="form-control" type="password" id="password_conf">
        	<h5>In the unlikely event that a large bird crashes into your head and you suffer from temporary amnesia and can't remember your password, please provide us with your email address.</h5>
        	<input class="form-control" type="email" id="email">
        	<span class="tiny">Note: We reserve the right to sell your information to satanic cults, or the highest bidder.</span>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" id="password_btn" data-dismiss="modal" data-toggle="modal" data-target="#new_password_modal_2" disabled>Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new password modal - bot test -->
  <div class="modal" id="new_password_modal_2" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">New Character</h4>
        </div>
        <div class="modal-body">
        	<h5 class="center">Before we can let you pass, we just need to make sure you're not a robot, or a Russki, or both. Please enter the most secret of the secret codes.</h5>
        	<input class="form-control" type="text" name="nerd_test" id="nerd_test">
        	<div class="button-bar">
		        <button type="button" class="btn btn-primary" id="password_btn_2" data-dismiss="modal" onclick="setPassword()">Ok</button>
		      </div>
        </div>
      </div>
    </div>
  </div>

	<!-- submitting status modal -->
  <div class="modal" id="submit_load_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-body center">
					<span class="glyphicon glyphicon-refresh spinning"></span> Waiting for server response...
        </div>
      </div>
    </div>
  </div>

	<!-- password modal -->
  <div class="modal" id="password_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Update Character</h4>
        </div>
        <div class="modal-body">
        	<h4>Please enter your password to update your character</h4>
        	<input class="form-control" type="password" id="password">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" id="password_btn" onclick="validatePassword()">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary forgot-password-btn" data-dismiss="modal" data-toggle="modal" data-target="#forgot_password_modal">I Forgot My Password!</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- forgot password modal -->
  <div class="modal" id="forgot_password_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Forgot My Password</h4>
        </div>
        <div class="modal-body">
        	<h4 class="center">Did you try crab?</h4>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary forgot-password-btn" data-dismiss="modal">Oh dang. That was it. Thanks.</button>
	        	<button type="button" class="btn btn-primary forgot-password-btn" data-dismiss="modal" onclick="forgotPassword()">Yes, I tried crab. That wasn't it.</button>
	        	<button style="white-space: normal;" type="button" class="btn btn-primary forgot-password-btn" data-dismiss="modal" onclick="forgotPassword()">No, it's definitely not crab. It's something super secure. I just can't remember what it was. I guess I should've written it down, or something?</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- suggestion modal -->
  <div class="modal" id="suggestion_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">The Suggestion Box</h4>
        </div>
        <div class="modal-body">
        	<h5 class="center">Got a suggestion for us? Something we can do better? Something not working right? Let us know!</h5>
        	<textarea class="form-control" id="suggestion" rows="6"></textarea>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#suggestion_modal2">Next</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- suggestion modal 2 -->
  <div class="modal" id="suggestion_modal2" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">The Suggestion Box</h4>
        </div>
        <div class="modal-body">
        	<h5 class="center">What's the secret nerd word?</h5>
        	<input class="form-control" type="text" id="nerd_word">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="submitSuggestion()">Submit</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- encumbered alert modal -->
  <div class="modal" id="encumbered_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-body">
        	<h4 class="center" id="encumbered_msg"></h4><br>
        	<div class="center">
	        	<label for="suppress_alert" class="control-label">Yeah, I know, quit bugging me.</label>
	        	<input type="checkbox" id="suppress_alert">
        	</div>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- help modal -->
  <div class="modal" id="help_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Magnets: How Do They Work?</h4>
        </div>
        <div class="modal-body">
        	<h4>Character Creation</h4>
					<p>When creating a new character you will start with a default of 12 Attribute Points. This value is 'unlocked' during character creation, and can be adjusted based on any modifiers. Your Attribute Points can be allocated by selecting the <i>Allocate Attribute Points</i> option from the menu. Points will be automatically adjusted as you increase or decrease attributes, and as feats and trainings are added. Your attributes and feats are also 'unlocked' during character creation, allowing you to add additional starting feats/traits and skills as needed. In order to save a newly created character, you will need to know the 'secret code.' If you don't know what it is, ask your GM. If they don't know it...find a new GM? You will also need to set a personal password when creating a new character, which you will need when updating your character in the future.</p>
					<h4>Adding XP & Allocating Attribute Points</h4>
					<p>Once your character has begun collecting XP, all of your attribute values, skills, and feats will be locked. The only way to modify your attributes is by accruing and allocating Attribute Points. As you add XP, your level will be automatically adjusted, and as you gain levels, Attribute Points will automatically be added. These Attribute Points can then be allocated via the <i>Allocate Attribute Points</i> option. Attributes can only be raised by one point per allocation, and only one unique skill or feat, as well as one focus or training, can be added per allocation. Attribute points will be automatically deducted. If additional modifications need to be made to attributes, skills or feats, this will need to be done through the <i>GM Edit Mode</i>.</p>
					<h4>GM Edit Mode</h4>
					<p>Using the admin password (set when creating the campaign), the GM can unlock and edit attribute points, XP, attribute values, skills, and feats. The GM can also use the admin password to make and save changes to any of the characters at any time.</p>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary forgot-password-btn" data-dismiss="modal">Ok</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

  <!-- footer -->
  <div class="footer row">
  	<p class="link col-md-4" data-toggle="modal" data-target="#help_modal"><span class="glyphicon glyphicon-info-sign"></span> Guide</p>
  	<p class="link col-md-4" data-toggle="modal" data-target="#suggestion_modal"><span class="glyphicon glyphicon-envelope"></span> Suggestion Box</p>
  	<p class=" col-md-4">© <?php echo date("Y"); ?> CrabAgain.com</p>
  </div>

	<!-- JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script async src="https://www.google.com/recaptcha/api.js?render=6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	<script src="/assets/script_v22_11_22.js"></script>
	<script type="text/javascript">

		var keys = <?php echo json_encode($keys); ?>;

		// check for user and campaign values
		var campaign = <?php echo json_encode(isset($campaign) ? $campaign : []); ?>;
		var user = <?php echo json_encode(isset($user) ? $user : []); ?>;
		var xp_awards = <?php echo json_encode(isset($awards) ? $awards : []); ?>;
		setAttributes(user);

		// get feat list and requirements
		var feat_list = <?php echo json_encode($feat_list); ?>;
		var feat_reqs = <?php echo json_encode($feat_reqs); ?>;
		var feat_sets = {};
		var req_sets = [];
		// sort requirements into sets
		for (var i in feat_reqs) {
			if (feat_sets[feat_reqs[i]['feat_id']] == null) {
				feat_sets[feat_reqs[i]['feat_id']] = [];
			}
			if (req_sets[feat_reqs[i]['req_set_id']] == null) {
				req_sets[feat_reqs[i]['req_set_id']] = [];
			}
			var req = {};
			req[feat_reqs[i]['type']] = feat_reqs[i]['value'];
			req_sets[feat_reqs[i]['req_set_id']].push(req);
			if (req_sets[feat_reqs[i]['req_set_id']].length > 1) {
				continue;
			} else {
				feat_sets[feat_reqs[i]['feat_id']].push(req_sets[feat_reqs[i]['req_set_id']]);
			}
		}
		// add requirements to feat list
		for (var i in feat_list) {
			for (var j in feat_sets) {
				if (feat_list[i]['id'] == j) {
					feat_list[i]['requirements'] = feat_sets[j];
				}
			}
		}

		// check for user feats
		user_feats = <?php echo json_encode($feats); ?>;
		for (var i in user_feats) {
			addFeatElements(user_feats[i]['name'], user_feats[i]['description'], user_feats[i]['id']);
		}

		// set feat list
		setFeatList();
		
		// character creation mode
		if (user.length == 0 || user['xp'] == 0) {
			characterCreation = true;
			// show new feat btn
			$("#new_feat_btn").show();
			// enable hover-hide on attribute edits
			if (is_mobile) {
				$(".attribute-col").find(".hover-hide").show();
			} else {
				$(".attribute-col").hover(function(){
					$(this).find(".hover-hide").show();
				},
				function(){
					$(this).find(".hover-hide").hide();
				});
			}
		}

		// check for user trainings
		var trainings = <?php echo json_encode($trainings); ?>;
		for (var i in trainings) {
			addTrainingElements(trainings[i]['name'], trainings[i]['attribute_group'], trainings[i]['id'], trainings[i]['value']);
		}

		// check for user weapons
		loadingItems = true;
		var weapons = <?php echo json_encode($weapons); ?>;
		for (var i in weapons) {
			addWeaponElements(weapons[i]['type'], weapons[i]['name'], weapons[i]['quantity'], weapons[i]['damage'], weapons[i]['max_damage'], weapons[i]['range_'], weapons[i]['rof'], weapons[i]['defend'], weapons[i]['crit'], weapons[i]['notes'], weapons[i]['weight'], weapons[i]['id']);
		}
		// trigger select weapon functions to update inputs and defend value
		$(".weapon-select").each(function(){
			if ($(this).val() != '') {
				$(this).trigger("change");
				var name_val = $(this).attr("name");
				// if weapon is selected, expand hidden elements (mobile only)
				if (is_mobile) {
					$(".glyphicon-chevron-down").each(function() {
						if (this.id == name_val) {
							toggleWeapon(this.id,this);
						}
					});
				}
			}
		});

		// check for user protections
		protections = <?php echo json_encode($protections); ?>;
		for (var i in protections) {
			addProtectionElements(protections[i]['name'], protections[i]['bonus'], protections[i]['notes'], protections[i]['weight'], protections[i]['equipped'], protections[i]['id']);
			// check if protection is equipped
			if (protections[i]['equipped'] == 1) {
				equipped.push(protections[i]['name']);
				$("#protection_"+protections[i]['id']+"_equip_ban").toggle();
			}
		}
		// update toughness for equipped protections
		setToughness();

		// check for user healings
		var healings = <?php echo json_encode($healings); ?>;
		for (var i in healings) {
			addHealingElements(healings[i]['name'], healings[i]['quantity'], healings[i]['effect'], healings[i]['weight'], healings[i]['id']);
		}

		// check for user misc items
		var misc = <?php echo json_encode($misc); ?>;
		for (var i in misc) {
			addMiscElements(misc[i]['name'], misc[i]['quantity'], misc[i]['notes'], misc[i]['weight'], misc[i]['id']);
		}
		// show encumbered alert after all items are loaded
		loadingItems = false;
		updateTotalWeight(true);

		// check for user notes
		var notes = <?php echo json_encode($notes); ?>;
		for (var i in notes) {
			addNoteElements(notes[i]['title'], notes[i]['note'], notes[i]['id']);
		}

	</script>

</body>
</html>