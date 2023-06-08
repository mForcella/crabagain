<?php

	// establish database connection
	include_once('config/db_config.php');
	include_once('config/keys.php');
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
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'social_background'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['social_background_count'] = $row['count'];
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
	$user_motivators = [];
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

	    	// get pending xp awards
	    	$sql = "SELECT * FROM user_xp_award WHERE awarded IS NULL AND user_id = ".$_GET["user"];
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
		    		if ($row['name'] == "Arcane Blood" || $row['name'] == "Divine Magic") {
		    			$user['magic_talents'] = true;
		    		}
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
	    	// get user motivators
	    	$sql = "SELECT * FROM user_motivator WHERE user_id = ".$_GET["user"];
	    	$result = $db->query($sql);
	    	if ($result) {
		    	while($row = $result->fetch_assoc()) {
		    		array_push($user_motivators, $row);
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
	<title><?php echo $campaign['name'].(isset($user) ? ' : '.$user['character_name'] : '') ?></title>
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
	<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;800&family=Alegreya:ital,wght@0,400;1,400;1,600&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
	<!-- Custom Styles -->
	<link rel="stylesheet" type="text/css" href="<?php echo $keys['styles'] ?>">

</head>

<body>

	<!-- use div visibility to determine if we're on mobile -->
	<div id="is_mobile"></div>

	<!-- user menu -->
	<nav class="navbar">

	  <div class="nav-menu">
	    <?php
	    	if (!isset($user)) {
	    		echo '
				    <div class="nav-item">
				      <span class="glyphicon" onclick="formSubmit()"><span class="nav-item-label"><i class="fa-solid fa-floppy-disk nav-icon"></i> Save Character Data</span></span>
				    </div>
				   ';
	    	}
	    ?>
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

	<img id="banner-1" class="banner-image" src="assets/image/banners/dnd-banner-1.jpeg">
	<img id="banner-2" class="banner-image" src="assets/image/banners/dnd-banner-2.jpeg">
	<img id="banner-3" class="banner-image" src="assets/image/banners/dnd-banner-3.jpeg">
	<img id="banner-4" class="banner-image" src="assets/image/banners/dnd-banner-4.jpeg">
	<img id="banner-5" class="banner-image active" src="assets/image/banners/dnd-banner-5.jpeg">

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
		<option value="#section_feats">Talents</option>
		<option value="#section_items">Items</option>
		<option value="#section_weight">Weight Capacity</option>
		<option value="#section_notes">Notes</option>
	</select>

	<form id="user_form" method="post" action="/scripts/submit.php" novalidate>
		<input type="hidden" id="user_id" name="user_id" value="<?php echo isset($user) ? $user['id'] : '' ?>">
		<input type="hidden" id="campaign_id" name="campaign_id" value="<?php echo $_GET["campaign"] ?>">
		<input type="hidden" id="user_email" name="email" value="<?php echo isset($user) ? $user["email"] : '' ?>">
		<div class="row">
			<div class="col-md-6">

				<!-- section: name, level, xp -->
				<div class="section form-horizontal">

					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="character_name">Name</label>
						<div class="col-sm-6 col-xs-8 mobile-pad-bottom">
							<input class="form-control track-changes" type="text" id="character_name" name="character_name" value="<?php echo isset($user) ? htmlspecialchars($user['character_name']) : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
						</div>
						<!-- readonly, unless new character -->
						<label class="control-label col-sm-2 col-xs-4 font-small smaller" for="attribute_pts">Attribute Pts</label>
						<div class="col-sm-2 col-xs-8">
							<input class="form-control track-changes" <?php echo isset($user) && $user['xp'] != 0 ? 'readonly' : 'type="number"' ?> min="0" id="attribute_pts" name="attribute_pts" value="<?php echo isset($user) ? htmlspecialchars($user['attribute_pts']) : 12 ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
						</div>
					</div>

					<div class="form-group tablet-adjust">
						<label class="control-label col-sm-2 col-xs-4" for="xp">XP</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control pointer track-changes" readonly data-toggle="modal" data-target="#xp_modal" name="xp" id="xp" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['xp']) : 0 ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="level">Level</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
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
						<label class="control-label col-sm-2 col-xs-4 font-small smaller" for="caster_level">Caster Level</label>
						<div class="col-sm-2 col-xs-8">
							<?php
								$caster_level = isset($user) ? $user['vitality'] + 10 : 10;
							?>
							<input class="form-control" readonly name="caster_level" id="caster_level" value="<?php echo $caster_level ?>">
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="morale">Morale</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control track-changes" type="number" name="morale" id="morale" min="-10" value="<?php echo isset($user) ? htmlspecialchars($user['morale']) : 0 ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
						</div>
						<div class="col-xs-4 d-sm-none"></div>
						<div class="col-sm-4 col-xs-8 mobile-pad-bottom">
							<input class="form-control" readonly id="morale_effect" name="morale_effect">
						</div>
						<?php
							$fate = 0;
							// vitality bonus
							$fate += isset($user) ? (
								$user['vitality'] >= 0 ?
									floor($user['vitality']/2) :
									(ceil($user['vitality']/3) == 0 ? 0 : ceil($user['vitality']/3))
							) : 0;
							// morale bonus
							$morale = isset($user) ? htmlspecialchars($user['morale']) : 0;
							$fate += $morale >= 6 ? 2 : ($morale >= 2 ? 1 : 0);
						?>
						<label class="control-label col-sm-2 col-xs-4" for="fate">Fate</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control" name="fate" id="fate" value="<?php echo $fate ?>" readonly>
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
							<input class="form-control track-changes" type="text" id="race" name="race" value="<?php echo isset($user) ? htmlspecialchars($user['race']) : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="age">Age</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control" type="text" name="age" id="age_text" value="<?php echo isset($user) ? htmlspecialchars($user['age']) : '' ?>">
							<input class="form-control hidden-number track-changes" type="number" id="age" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user" data-column="age">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="gender">Gender</label>
						<div class="col-sm-2 col-xs-8 desktop-no-pad-left">
							<input class="form-control track-changes" type="text" id="gender" name="gender" value="<?php echo isset($user) ? htmlspecialchars($user['gender']) : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="height">Height</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<select class="form-control track-changes" name="height" id="height" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
								<?php
									// limit range by user size - small: 36-59, medium: 60-83, large: 84-107
									$size = "Medium";
									$height = 0;
									if (isset($user)) {
										$size = $user['size'] == "" ? "Medium" : $user['size'];
										$height = $user['height'] == "" ? 0 : $user['height'];
									}
									$lower = $size == "Small" ? 36 : ($size == "Large" ? 84 : 60);
									$upper = $size == "Small" ? 60 : ($size == "Large" ? 108 : 84);
									for ($i = $lower; $i < $upper; $i++) {
										$feet = 0;
										$inches = $i;
										while ($inches > 11) {
											$feet += 1;
											$inches -= 12;
										}
										echo '<option value="'.$i.'" '.($height == $i ? 'selected' : '').'>'.$feet.'\' '.$inches.'"'.'</option>';
									}
								?>
							</select>
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="weight">Weight</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control" type="text" name="weight" id="weight_text" value="<?php echo isset($user) ? htmlspecialchars($user['weight']) : '' ?>">
							<input class="form-control hidden-number track-changes" type="number" id="weight" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user" data-column="weight">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="eyes">Eyes</label>
						<div class="col-sm-2 col-xs-8 desktop-no-pad-left">
							<input class="form-control track-changes" type="text" id="eyes" name="eyes" value="<?php echo isset($user) ? htmlspecialchars($user['eyes']) : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="hair">Hair</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control track-changes" type="text" id="hair" name="hair" value="<?php echo isset($user) ? htmlspecialchars($user['hair']) : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="other">Other</label>
						<div class="col-sm-6 col-xs-8 desktop-no-pad-left">
							<input class="form-control track-changes" type="text" id="other" name="other" value="<?php echo isset($user) ? htmlspecialchars($user['other']) : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
									<label class="control-label col-md-12 center full-width" for="weapon_select_1">Weapon 1<span class="glyphicon glyphicon-chevron-down" id="weapon_1" onclick="toggleWeapon(this.id, this)"></span></label>
									<div class="col-md-12">
										<select class="form-control weapon-select" id="weapon_select_1" name="weapon_1" onchange="selectWeapon(1)">
											<option></option>
										</select>
									</div>
								</div>
								<div id="weapon_1_container" class="weapon-container">
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_damage_1">Damage</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_damage_1" name="weapon_1_damage">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_crit_1">Critical</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_crit_1" name="weapon_1_crit">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_range_1">Range</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_range_1" name="weapon_1_range">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_rof_1">R o F</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_rof_1" name="weapon_1_rof">
										</div>
									</div>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label col-md-12 center full-width" for="weapon_select_2">Weapon 2<span class="glyphicon glyphicon-chevron-down" id="weapon_2" onclick="toggleWeapon(this.id, this)"></span></label>
									<div class="col-md-12">
										<select class="form-control weapon-select" id="weapon_select_2" name="weapon_2" onchange="selectWeapon(2)">
											<option></option>
										</select>
									</div>
								</div>
								<div id="weapon_2_container" class="weapon-container">
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_damage_2">Damage</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_damage_2" name="weapon_2_damage">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_crit_2">Critical</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_crit_2" name="weapon_2_crit">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_range_2">Range</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_range_2" name="weapon_2_range">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_rof_2">R o F</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_rof_2" name="weapon_2_rof">
										</div>
									</div>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label col-md-12 center full-width" for="weapon_select_3">Weapon 3<span class="glyphicon glyphicon-chevron-down" id="weapon_3" onclick="toggleWeapon(this.id, this)"></span></label>
									<div class="col-md-12">
										<select class="form-control weapon-select" id="weapon_select_3" name="weapon_3" onchange="selectWeapon(3)">
											<option></option>
										</select>
									</div>
								</div>
								<div id="weapon_3_container" class="weapon-container">
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_damage_3">Damage</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_damage_3" name="weapon_3_damage">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_crit_3">Critical</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_crit_3" name="weapon_3_crit">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_range_3">Range</label>
										<div class="col-md-5 col-xs-8 no-pad-left">
											<input class="form-control" readonly id="weapon_range_3" name="weapon_3_range">
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-7 col-xs-4" for="weapon_rof_3">R o F</label>
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

					<div class="form-group row-narrow-desktop">
						<div class="col-sm-3 pad-left-right-zero">
							<label class="control-label col-sm-7 col-xs-4" for="magic">Magic</label>
							<div class="col-sm-5 col-xs-8 no-pad-input mobile-pad-bottom">
							<input class="form-control" type="text" name="magic" id="magic_text" value="<?php echo isset($user) ? htmlspecialchars($user['magic']) : '' ?>">
							<input class="form-control hidden-number track-changes" type="number" id="magic" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user" data-column="magic">
							</div>
						</div>
						<div class="col-sm-3 pad-left-right-zero">
							<label class="control-label col-sm-7 col-xs-4" for="fear">Fear</label>
							<div class="col-sm-5 col-xs-8 no-pad-input mobile-pad-bottom">
							<input class="form-control" type="text" name="fear" id="fear_text" value="<?php echo isset($user) ? htmlspecialchars($user['fear']) : '' ?>">
							<input class="form-control hidden-number track-changes" type="number" id="fear" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user" data-column="fear">
							</div>
						</div>
						<div class="col-sm-3 pad-left-right-zero">
							<label class="control-label col-sm-7 col-xs-4" for="poison">Poison</label>
							<div class="col-sm-5 col-xs-8 no-pad-input mobile-pad-bottom">
							<input class="form-control" type="text" name="poison" id="poison_text" value="<?php echo isset($user) ? htmlspecialchars($user['poison']) : '' ?>">
							<input class="form-control hidden-number track-changes" type="number" id="poison" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user" data-column="poison">
							</div>
						</div>
						<div class="col-sm-3 pad-left-right-zero">
							<label class="control-label col-sm-7 col-xs-4" for="disease">Disease</label>
							<div class="col-sm-5 col-xs-8 no-pad-input">
							<input class="form-control" type="text" name="disease" id="disease_text" value="<?php echo isset($user) ? htmlspecialchars($user['disease']) : '' ?>">
							<input class="form-control hidden-number track-changes" type="number" id="disease" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user" data-column="disease">
							</div>
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
									<?php 
										$resilience = isset($user) ? (
											$user['fortitude'] >= 0 ? 
												3 + floor($user['fortitude']/2) :
												3 + ceil($user['fortitude']/3)
										) : 3;
										$damage = isset($user) && $user['damage'] != null ? $user['damage'] : 0;
										$wounds = 0;
										while ($damage >= $resilience) {
											$wounds += 1;
											$damage -= $resilience;
										}
										$wound_val = $wounds == 0 ? "None" : ($wounds == 1 ? "Wounded" : ($wounds == 2 ? "Incapacitated" : ($wounds == 3 ? "Mortally Wounded" : "Yer Dead")));
										$wound_penalty_val = $wounds == 0 ? "None" : ($wounds == 1 ? "-1" : ($wounds == 2 ? "-3" : ($wounds == 3 ? "-5" : "Yer Dead")));
									?>
									<input class="form-control" id="damage" type="number" min="<?php echo $wounds == 0 ? 0 : -1 ?>" value="<?php echo $damage ?>">
									<input type="hidden" class="track-changes" name="damage" id="total_damage" data-table="user" data-column="damage" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>">
								</div>
								<div class="col-xs-2 center no-pad">
									/
								</div>
								<div class="col-xs-5 no-pad">
									<input class="form-control" readonly id="resilience" name="resilience" value="<?php echo $resilience ?>">
								</div>
							</div>
						</div>

						<div class="col-sm-4">
							<div class="row">
								<label class="control-label col-sm-12 center full-width" for="wounds">Wounds</label>
							</div>
							<div class="row">
								<div class="col-sm-12 no-pad">
									<input type="text" class="form-control" id="wounds" readonly value="<?php echo $wound_val ?>">
									<select id="wounds_val" class="form-control hidden">
										<option value="0">None</option>
										<option value="1" <?php echo $wounds == 1 ? 'selected' : '' ?>>Wounded</option>
										<option value="2" <?php echo $wounds == 2 ? 'selected' : '' ?>>Incapacitated</option>
										<option value="3" <?php echo $wounds == 3 ? 'selected' : '' ?>>Mortally Wounded</option>
										<option value="4" <?php echo $wounds == 4 ? 'selected' : '' ?>>Yer Dead</option>
									</select>
								</div>
							</div>
						</div>

						<div class="col-sm-4">
							<div class="row">
								<label class="control-label col-sm-12 center full-width penalty" for="wound_penalty">Penalty</label>
							</div>
							<div class="row">
								<div class="col-sm-12 no-pad">
									<input type="text" class="form-control" id="wound_penalty" readonly value="<?php echo $wound_penalty_val ?>">
									<select id="wound_penalty_val" class="form-control hidden">
										<option value="0">None</option>
										<option value="1" <?php echo $wounds == 1 ? 'selected' : '' ?>>-1</option>
										<option value="2" <?php echo $wounds == 2 ? 'selected' : '' ?>>-3</option>
										<option value="3" <?php echo $wounds == 3 ? 'selected' : '' ?>>-5</option>
										<option value="4" <?php echo $wounds == 4 ? 'selected' : '' ?>>Yer Dead</option>
									</select>
								</div>
							</div>
						</div>

						<div class="col-sm-6">
							<div class="row">
								<label class="control-label col-sm-12 center full-width" for="fatigue">Fatigue</label>
							</div>
							<div class="row">
								<div class="col-xs-12 no-pad">
									<?php 
										$fatigue = isset($user) ? ($user['fatigue'] == '' ? 0 : $user['fatigue']) : 0;
									?>
									<select class="form-control track-changes" id="fatigue" name="fatigue" onchange="updateTotalWeight(false)" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
										<option value="0" <?php echo $fatigue == 0 ? 'selected' : '' ?>>None</option>
										<option value="1" <?php echo $fatigue == 1 ? 'selected' : '' ?>>Tired</option>
										<option value="2" <?php echo $fatigue == 2 ? 'selected' : '' ?>>Weary</option>
										<option value="3" <?php echo $fatigue == 3 ? 'selected' : '' ?>>Spent</option>
										<option value="4" <?php echo $fatigue == 4 ? 'selected' : '' ?>>Exhausted</option>
									</select>
								</div>
							</div>
						</div>

						<div class="col-sm-6">
							<div class="row">
								<label class="control-label col-sm-12 center full-width" for="encumberence">Encumberence</label>
							</div>
							<div class="row">
								<div class="col-xs-12 no-pad">
									<input class="form-control" type="text" readonly id="encumberence">
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
							<input class="form-control" readonly name="standard" id="standard" value="">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="quick">Quick</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control" readonly name="quick" id="quick" value="">
						</div>
						<label class="control-label col-sm-2 col-xs-4 penalty" for="action_penalty">Penalty</label>
						<div class="col-sm-2 col-xs-8">
							<input class="form-control" type="text" id="action_penalty" value="None" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="initiative">Initiative</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control" readonly name="initiative" id="initiative" value="">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="move">Move/Run</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control" readonly name="move" id="move" value="">
						</div>
						<label class="control-label col-sm-2 col-xs-4 penalty" for="move_penalty">Penalty</label>
						<div class="col-sm-2 col-xs-8">
							<input class="form-control" type="text" id="move_penalty" value="None" readonly>
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
										<input type="hidden" class="track-changes" name="strength" id="strength_val" value="<?php echo isset($user) ? $user['strength'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Strength_btn" onclick="newTrainingModal('Strength')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
										<input type="hidden" class="track-changes" name="fortitude" id="fortitude_val" value="<?php echo isset($user) ? $user['fortitude'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Fortitude_btn" onclick="newTrainingModal('Fortitude')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
										<input type="hidden" class="track-changes" name="speed" id="speed_val" value="<?php echo isset($user) ? $user['speed'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Speed_btn" onclick="newTrainingModal('Speed')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
										<input type="hidden" class="track-changes" name="agility" id="agility_val" value="<?php echo isset($user) ? $user['agility'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Agility_btn" onclick="newTrainingModal('Agility')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
										<input type="hidden" class="track-changes" name="precision_" id="precision__val" value="<?php echo isset($user) ? $user['precision_'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Precision_btn" onclick="newTrainingModal('Precision')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
										<input type="hidden" class="track-changes" name="awareness" id="awareness_val" value="<?php echo isset($user) ? $user['awareness'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Awareness_btn" onclick="newTrainingModal('Awareness')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
										<input type="hidden" class="track-changes" name="allure" id="allure_val" value="<?php echo isset($user) ? $user['allure'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Allure_btn" onclick="newTrainingModal('Allure')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
										<input type="hidden" class="track-changes" name="deception" id="deception_val" value="<?php echo isset($user) ? $user['deception'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Deception_btn" onclick="newTrainingModal('Deception')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
										<input type="hidden" class="track-changes" name="intellect" id="intellect_val" value="<?php echo isset($user) ? $user['intellect'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Intellect_btn" onclick="newTrainingModal('Intellect')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
										<input type="hidden" class="track-changes" name="innovation" id="innovation_val" value="<?php echo isset($user) ? $user['innovation'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Innovation_btn" onclick="newTrainingModal('Innovation')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-6 attribute-col" id="col_intuition">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="intuition"><span class="attribute-name">Intuition</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_intuition" onclick="toggleHidden('col_intuition')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="intuition_text"></span>
										<input type="hidden" class="track-changes" name="intuition" id="intuition_val" value="<?php echo isset($user) ? $user['intuition'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" onclick="adjustAttribute('intuition', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" onclick="adjustAttribute('intuition', -1)"></span>
									</label>
								</div>
							</div>
							<div class="row training">
								<div class="col-md-12">
									<div class="row">
										<div id="Intuition"></div>
									</div>
									<div class="row">
										<div class="col-md-12 button-bar">
											<button type="button" class="btn btn-default" id="Intuition_btn" onclick="newTrainingModal('Intuition')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
										<input type="hidden" class="track-changes" name="vitality" id="vitality_val" value="<?php echo isset($user) ? $user['vitality'] : '' ?>" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user">
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
											<button type="button" class="btn btn-default" id="Vitality_btn" onclick="newTrainingModal('Vitality')"><span class="glyphicon glyphicon-plus-sign hidden-icon"></span></button>
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
								if (isset($user)) {
									$m_pts = 0;
									foreach ($user_motivators as $m) {
										if ($m['primary_'] == 1) {
											$m_pts += intval($m['points']);
										}
									}
									$bonuses = $m_pts >= 64 ? 5 : ($m_pts >= 32 ? 4 : ($m_pts >= 16 ? 3 : ($m_pts >= 8 ? 2 : ($m_pts >= 4 ? 1 : 0))));
								} else {
									$bonuses = 0;
								}
								$set_motivators = count($user_motivators) == 0;
							?>
							<input class="form-control" readonly name="bonuses" id="bonuses" value="<?php echo $bonuses ?>">
						</div>
					</div>

					<div class="form-group no-margin">
					<?php
						for ($i = 0; $i < 4; $i++) {
							echo $i == 2 ? '</div><div class="form-group no-margin">' : '';
							echo '<div class="col-xs-4 no-pad-mobile '.($i == 0 || $i == 2 ? 'no-pad-left' : 'pad-left-mobile').'">';
							echo '<input class="form-control motivator-input '. (isset($user_motivators[$i]) && $user_motivators[$i]['primary_'] ? 'bold' : '') .' '. ($set_motivators ? 'pointer' : '') .'" type="text" name="motivators[]" id="motivator_'.$i.'" readonly value="'. (isset($user_motivators[$i]) ? $user_motivators[$i]['motivator'] : '') .'">';
							echo '</div>';
							echo '<label class="control-label col-xs-1 no-pad-left align-right font-mobile-small" for="motivator_pts_'.$i.'">Pts:</label>';
							echo '<div class="col-xs-1 no-pad">';
							echo '<input class="form-control motivator-pts" type="number" name="motivator_pts[]" id="motivator_pts_'.$i.'" min="0" value="'. (isset($user_motivators[$i]) ? $user_motivators[$i]['points'] : '') .'" '. ($set_motivators ? 'readonly' : '') .' data-val="'. (isset($user_motivators[$i]) ? $user_motivators[$i]['points'] : '') .'">';
							echo '</div>';
							echo '<input type="hidden" name="motivator_ids[]" value="'. (isset($user_motivators[$i]) ? $user_motivators[$i]['id'] : '') .'">';
							echo '<input type="hidden" name="motivator_primary[]" value="'. (isset($user_motivators[$i]) ? $user_motivators[$i]['primary_'] : '') .'" id="motivator_primary_'.$i.'">';
						}
					?>
					</div>

				</div>

			  <!-- motivators modal -->
			  <div class="modal" id="motivator_modal" tabindex="-1" role="dialog">
			    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			      <div class="modal-content">
			        <div class="modal-header">
			          <h4 class="modal-title">Motivators</h4>
			        </div>
			        <div class="modal-body">
			        	<h4 class="control-label center">Please set your motivators</h4>
								<?php 
									$motivators = [
										'',
										'Altruism',
										'Freedom',
										'Harmony',
										'Heroism',
										'Honor',
										'Justice',
										'Pleasure',
										'Power',
										'Pragmatism',
										'Prestige'
									];
								?>
								<input type="hidden" id="edit_motivators">
			        	<label>*Primary Motivator (2 pts)</label>
								<select class="form-control" id="m1" onchange="motivatorCheck(this.id)">
									<?php 
										foreach ($motivators as $motivator) {
											echo '<option value="'.$motivator.'">'.$motivator.'</option>';
										}
									?>
								</select>
			        	<label>*Secondary Motivator (1 pt)</label>
								<select class="form-control" id="m2" onchange="motivatorCheck(this.id)">
									<?php 
										foreach ($motivators as $motivator) {
											echo '<option value="'.$motivator.'">'.$motivator.'</option>';
										}
									?>
								</select>
			        	<label>*Tertiary Motivator (1 pt)</label>
								<select class="form-control" id="m3" onchange="motivatorCheck(this.id)">
									<?php 
										foreach ($motivators as $motivator) {
											echo '<option value="'.$motivator.'">'.$motivator.'</option>';
										}
									?>
								</select>
			        	<label>Turdiary Motivator (Optional)</label>
								<select class="form-control" id="m4" onchange="motivatorCheck(this.id)">
									<?php 
										foreach ($motivators as $motivator) {
											echo '<option value="'.$motivator.'">'.$motivator.'</option>';
										}
									?>
								</select>
			        	<div class="button-bar">
				        	<button type="button" class="btn btn-primary" onclick="setMotivators()">Ok</button>
				        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
			        	</div>
			        </div>
			      </div>
			    </div>
			  </div>

				<!-- end section: motivators -->

				<!-- section: feats & traits -->
				<div class="section form-horizontal">
					<div class="section-title" id="section_feats"><span>Talents & Traits</span> <i class="fa-solid fa-trophy"></i></div>
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
						<p class="col-xs-3 center resize-mobile-small">(-10 Move)</p>
						<p class="col-xs-3 center resize-mobile-small">(-1 QA, <br class="mobile-break">-10 Move)</p>
						<p class="col-xs-3 center resize-mobile-small">(-1 SA, <br class="mobile-break">-10 Move)</p>
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
							<textarea class="form-control track-changes" rows="6" name="background" id="background" maxlength="2000" data-id="<?php echo isset($user) ? htmlspecialchars($user['id']) : '' ?>" data-table="user"><?php echo isset($user) ? htmlspecialchars($user['background']) : '' ?></textarea>
						</div>
					</div>
				</div>
			</div>
			<!-- end section: background -->

		</div>
		<input type="hidden" name="password" id="password_val">
		<input type="hidden" name="recaptcha_response" id="recaptcha_response">
	</form>

	<!-- new school modal -->
  <div class="modal" id="new_school_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="note_modal_title">New Magic School</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Choose a Talent from your new school</label>
        	<select class="form-control" id="magic_talents">
        	</select>
        	<select class="form-control elemental_select">
        		<option value="Fire">Fire</option>
        		<option value="Ice">Ice</option>
        		<option value="Electricity">Electricity</option>
        	</select>
        	<select class="form-control elementalist_select">
        		<option value="Earth">Earth</option>
        		<option value="Water">Water</option>
        		<option value="Air">Air</option>
        	</select>
        	<select class="form-control superhuman_select">
        		<option value="Power/Dexterity">Power (Strength/Fortitude) & Dexterity (Speed/Agility)</option>
        		<option value="Power/Precision">Power (Strength/Fortitude) & Perception (Precision/Awareness)</option>
        		<option value="Dexterity/Precision">Dexterity (Speed/Agility) & Perception (Precision/Awareness)</option>
        	</select>
        	<textarea class="form-control" id="talent_descrip" readonly></textarea>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Learn Magic!</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="cancelMagic()">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- personality crisis modal -->
  <div class="modal" id="crisis_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Oh No! My Personality!</h4>
        </div>
        <div class="modal-body">
        	<p>It looks like you might be having a bit of a <strong>Personality Crisis</strong>.<br><br>Would you like to make <strong><span id="crisis_name"></span></strong> one of your primary motivators?</p>
        	<p class="smaller"><br>NOTE: A Personality Crisis will result in a -2 penalty to your Morale and NO Motivator bonuses for your next session.<br></p>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#crisis_modal_2">Yeah that sounds right</button>
	        	<p></p>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">No thanks</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- personality crisis modal #2 -->
  <div class="modal" id="crisis_modal_2" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Update Primary Motivators</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Select a Motivator to <strong>remove</strong> from your Primary Motivators</label>

        	<div class="row motivator-row" id="motivator_0_row">
	        	<input class="form-check-input" type="radio" name="update_motivators" id="remove_motivator_0" checked="checked">
			      <label class="form-check-label" for="remove_motivator_0" id="motivator_0_label"></label>
        	</div>
        	<div class="row motivator-row" id="motivator_1_row">
	        	<input class="form-check-input" type="radio" name="update_motivators" id="remove_motivator_1">
			      <label class="form-check-label" for="remove_motivator_1" id="motivator_1_label"></label>
        	</div>
        	<div class="row motivator-row" id="motivator_2_row">
	        	<input class="form-check-input" type="radio" name="update_motivators" id="remove_motivator_2">
			      <label class="form-check-label" for="remove_motivator_2" id="motivator_2_label"></label>
        	</div>

        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="updateMotivators()">Update Motivators</button>
	        	<p></p>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- xp modal -->
  <div class="modal" id="xp_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Experience Points</h4>
        </div>
        <div class="modal-body">
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
        	<div class="button-bar">
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
        		<option value="Small" <?php echo $size == "Small" ? 'selected' : '' ?>>Small (3’0”–4’11”)</option>
        		<option value="Medium" <?php echo $size == "Medium" ? 'selected' : '' ?>>Medium (5’0”–6’11”)</option>
        		<option value="Large" <?php echo $size == "Large" ? 'selected' : '' ?>>Large (7’0”–8’11”)</option>
        	</select>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="editSize(true)">Ok</button>
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
          <h4 class="modal-title" id="feat_modal_title">New Talent/Trait</h4>
        </div>
        <div class="modal-body">
        	<!-- show dropdown only during character creation -->
        	<label class="control-label <?php echo isset($user) && $user['xp'] != 0 ? 'hidden' : ''; ?>" id="select_feat_type_label">Type</label>
        	<select class="form-control <?php echo isset($user) && $user['xp'] != 0 ? 'hidden' : ''; ?>" id="select_feat_type">
        		<option id="standard_option" value="feat_name">Standard Talent</option>
        		<!--  hide unless user has magic -->
        		<option id="magic_option" value="magic_talent_name" <?php echo isset($user) && $user['magic_talents'] == true ? '' : 'hidden'; ?>>Magical Talent</option>
        		<!-- hide options if their counts are zero -->
        		<option value="social_background_name" <?php echo count($feat_ids) > 0 && $counts['social_background_count'] == 0 ? 'hidden' : '' ?>>Social Background</option>
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
        	<input class="form-control clearable feat-type hidden" type="text" id="magic_talent_name">
        	<select class="form-control feat-type feat-select hidden" id="social_background_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'social_background') {
        					echo "<option value='".str_replace('\'','',$feat['name'])."'>".$feat['name']."</option>";
        				}
        			}
        		?>
        	</select>
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

        	<select class="form-control elemental_select">
        		<option value="Fire">Fire</option>
        		<option value="Ice">Ice</option>
        		<option value="Electricity">Electricity</option>
        	</select>

        	<select class="form-control elementalist_select">
        		<option value="Earth">Earth</option>
        		<option value="Water">Water</option>
        		<option value="Air">Air</option>
        	</select>

        	<select class="form-control superhuman_select">
        		<option value="Power/Dexterity">Power (Strength/Fortitude) & Dexterity (Speed/Agility)</option>
        		<option value="Power/Precision">Power (Strength/Fortitude) & Perception (Precision/Awareness)</option>
        		<option value="Dexterity/Precision">Dexterity (Speed/Agility) & Perception (Precision/Awareness)</option>
        	</select>

        	<div class="row shapeshifter_select">
        		<div class="col-sm-6">
	        		<label class="control-label">Animal Name</label>
	        		<input class="form-control" type="text" id="animal_name">
	        	</div>
        		<div class="col-sm-6">
	        		<label class="control-label">Animal Level</label>
	        		<input class="form-control" type="number" id="animal_level" min="1" value="1">
	        	</div>
        	</div>

        	<label class="control-label">Description</label>
        	<textarea class="form-control" id="feat_description" rows="6" maxlength="2000"></textarea>
        	<input type="hidden" id="feat_id">
        	<input type="hidden" id="user_feat_id">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newFeat()" id="feat_submit_btn">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="vows_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Vows</h4>
        </div>
        <div class="modal-body">
        	<h4 class="center">Choose a Vow to follow in service to your God</h4>
	        <div class="form-check">
	        	<input class="form-check-input" type="radio" name="vow" id="Poverty" value="Poverty" checked="checked">
			      <label class="form-check-label" for="Poverty">Vow of Poverty</label>
			    </div>
			    <label class="smaller narrow" id="Poverty_description" for="Poverty">You have sworn against the pursuit of material things. You cannot accrue wealth or unnecessary items or partake in excesses and luxuries. Any wealth you might acquire must be given to those in need, or your Church, and not to other party members.</label>
	        <div class="form-check">
	        	<input class="form-check-input" type="radio" name="vow" id="Peace" value="Peace">
			      <label class="form-check-label" for="Peace">Vow of Peace</label>
			    </div>
			    <label class="smaller narrow" id="Peace_description" for="Peace">You have sworn off of violence. While you may still defend yourself and others, you must do all in your power to ensure violence is avoided when possible, and when it cannot be avoided, that killing is avoided when possible. Even if you are struck, striking back should be a last resort unless you perceive death or severe bodily injury to be imminent.</label>
	        <div class="form-check">
	        	<input class="form-check-input" type="radio" name="vow" id="Hedonism" value="Hedonism">
			      <label class="form-check-label" for="Hedonism">Vow of Hedonism</label>
			    </div>
			    <label class="smaller narrow" id="Hedonism_description" for="Hedonism">You find religious ecstasy only through excess, and your God speaks to you only at your moments of highest pleasure. You may only abstain during times when it would be virtually impossible for you to seek out pleasures, or if doing so would lead to personal harm, or harm to your God and their desires.</label>
	        <div class="form-check">
	        	<input class="form-check-input" type="radio" name="vow" id="Protection" value="Protection">
			      <label class="form-check-label" for="Protection">Vow of Protection</label>
			    </div>
			    <label class="smaller narrow" id="Protection_description" for="Protection">You have sworn to protect the good and just in the world. Wherever you see people in need, so long as they align with you and your God morally, you are required to help. The only exception would be if helping would lead to your imminent death or somehow interfere with the greater needs of your God.</label>
	        <div class="form-check">
	        	<input class="form-check-input" type="radio" name="vow" id="Freedom" value="Freedom">
			      <label class="form-check-label" for="Freedom">Vow of Freedom</label>
			    </div>
			    <label class="smaller narrow" id="Freedom_description" for="Freedom">You have sworn to thwart authority at every turn. If anyone is being systemically oppressed by a system of law, bureaucracy, or set of rules, you are compelled to intercede. This does not necessarily mean aiding an individual or group of people, as long as the institution suffers in some way.</label>
	        <div class="form-check">
	        	<input class="form-check-input" type="radio" name="vow" id="Truth" value="Truth">
			      <label class="form-check-label" for="Truth">Vow of Truth</label>
			    </div>
			    <label class="smaller narrow" id="Truth_description" for="Truth">You have sworn to never lie and seek out truth wherever it may hide. While a lie may be permitted from time to time, it can only be in greater service to your God. Additionally, if blatant lies surround you, you will be obliged to help reveal the truth, unless doing so would somehow be a disservice to your God or greater purpose.</label>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
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
        	<div id="skill_type">
        		<h4 class="control-label">
        			Skill / Training Type
        		</h4>
	        	<div class="form-check" id="magic_inputs">
		        	<input class="form-check-input" type="radio" name="skill_type" id="school" value="school">
		        	<label class="form-check-label" for="school">Magic School (4 attribute pt)</label>
        			<select class="form-control skill-name" id="school_name">
        				<option value=""></option>
        				<option value="Ka">Ka</option>
        				<option value="Avani">Avani</option>
        				<option value="Nouse">Nouse</option>
        				<option value="Soma">Soma</option>
        			</select>
	        	</div>
	        	<div class="form-check">
		        	<input class="form-check-input" type="radio" name="skill_type" id="unique" value="unique">
		        	<label class="form-check-label" for="unique">Unique Skill (2 attribute pts)</label>
        			<input class="form-control skill-name clearable" type="text" id="skill_name">
	        	</div>
	        	<div class="form-check">
		        	<input class="form-check-input" type="radio" name="skill_type" id="training" value="training">
		        	<label class="form-check-label" for="training">Training (2 attribute pts)</label>
        			<input class="form-control skill-name clearable" type="text" id="training_name">
	        	</div>
	        	<div class="form-check">
		        	<input class="form-check-input" type="radio" name="skill_type" id="focus" value="focus">
		        	<label class="form-check-label" for="focus">Focus (1 attribute pt)</label>
        			<input class="form-control skill-name clearable" type="text" id="focus_name">
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
          <h4 class="modal-title">Fuckin' Magnets: How Do They Work?</h4>
        </div>
        <div class="modal-body">
        	<h4>Character Creation</h4>
					<p>When creating a new character you will start with a default of 12 Attribute Points. This value is 'unlocked' during character creation, and can be adjusted based on any modifiers. Your Attribute Points can be allocated by selecting the <i>Allocate Attribute Points</i> option from the nav menu. Points will be automatically adjusted as you increase or decrease Attributes, and as Talents and Trainings are added. Your Attributes and Talents are also 'unlocked' during character creation, allowing you to add additional starting Talents/Traits and Skills as needed. In order to save a newly created character, you will need to know the 'secret code.' If you don't know what it is, ask your GM. If they don't know it...find a new GM? You will also need to set a personal password when creating a new character, which you will need when updating your character in the future.</p><br>
					<h4>Adding XP & Allocating Attribute Points</h4>
					<p>Once your character has begun collecting XP, all of your Attribute Values, Skills, and Talents will be locked. The only way to modify your Attributes is by accruing and allocating Attribute Points. As you add XP, your level will be automatically adjusted, and as you gain levels, Attribute Points will automatically be added. These Attribute Points can then be allocated via the <i>Allocate Attribute Points</i> option from the nav menu. Attributes can only be raised by one point per allocation, and only one unique Skill or Talent, as well as one Focus or Training, can be added per allocation. Attribute Points will be automatically deducted. If additional modifications need to be made to Attributes, Skills or Talents, this will need to be done through the <i>GM Edit Mode</i>.</p><br>
					<h4>GM Edit Mode</h4>
					<p>Using the admin password (set when creating the campaign), the GM can unlock and edit Attribute Points, XP, Attribute Values, Skills, and Talents. The GM can use this edit mode to make and save changes to any of the characters at any time. 
						<br><br><span class="narrow"><strong>NOTE:</strong> If modiyfing a character <i>during gameplay</i> make sure that player has saved their character beforehand to ensure that you are working with the most current version of that character.</span></p><br>
					<h4>Campaign Admin</h4>
					<p>The campaign admin page is password protected and can only be accessed with the admin password. The admin page provides a quick view of all characters and certain attributes. It is also where the GM is able to award XP to characters. You can also award bonus XP based on leftover Motivator chips bonuses and costumes. 
						<br><br><span class="narrow"><strong>NOTE:</strong> XP bonuses from Motivator arguments are automatically awarded when characters increase these values on their character sheets.</span><br>
						You can also view all available Talents, Traits, Compelling Actions, and Profressions. You can add new Talents and Traits, and you can adjust which Talents and Traits are available to your players.</p>
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
	<script src="<?php echo $keys['scripts'] ?>"></script>
	<script type="text/javascript">

		keys = <?php echo json_encode($keys); ?>;

		// check for user and campaign values
		campaign = <?php echo json_encode(isset($campaign) ? $campaign : []); ?>;
		user = <?php echo json_encode(isset($user) ? $user : []); ?>;
		xp_awards = <?php echo json_encode(isset($awards) ? $awards : []); ?>;
		user_motivators = <?php echo json_encode($user_motivators); ?>;
		setAttributes(user);

		// get feat list and requirements
		feat_list = <?php echo json_encode($feat_list); ?>;
		feat_reqs = <?php echo json_encode($feat_reqs); ?>;
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
		// get feat description from feat_or_trait table if feat_id is present
		for (var i in user_feats) {
			var feat_id_null = true;
			if (user_feats[i]['feat_id'] != null) {
				feat_id_null = false;
				for (var j in feat_list) {
					if (feat_list[j]['id'] == user_feats[i]['feat_id']) {
						user_feats[i]['description'] = feat_list[j]['description'];
					}
				}
			}
			// TODO update function to accept object
			addFeatElements(user_feats[i]['name'], user_feats[i]['name'], user_feats[i]['description'], feat_id_null ? "" : user_feats[i]['feat_id'], user_feats[i]['id']);
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
		user_trainings = <?php echo json_encode($trainings); ?>;
		// check for governing school
		var governing = "";
		for (var i in user_trainings) {
			if (user_trainings[i]['governing_school'] == 1) {
				governing = user_trainings[i]['name'];
			}
		}
		for (var i in user_trainings) {
			var displayName = user_trainings[i]['name'];
			// look for magic school - set label to governing/companion/opposition
			if (user_trainings[i]['magic_school'] == 1) {
				if (user_trainings[i]['governing_school'] == 1) {
					displayName += " (Gov)";
				} else if (governing != "") {
					if (governing == "Soma") {
						displayName += displayName == "Avani" ? " (Comp)" : " (Opp)";
					} else if (governing == "Avani") {
						displayName += displayName == "Soma" ? " (Comp)" : " (Opp)";
					} else if (governing == "Nouse") {
						displayName += displayName == "Ka" ? " (Comp)" : " (Opp)";
					} else {
						displayName += displayName == "Nouse" ? " (Comp)" : " (Opp)";
					}
				}
			}
			// TODO update function to accept object
			addTrainingElements(user_trainings[i]['name'], displayName, user_trainings[i]['attribute_group'], user_trainings[i]['id'], user_trainings[i]['value']);
		}

		// check for user weapons
		loadingItems = true;
		user_weapons = <?php echo json_encode($weapons); ?>;
		var select_id = 1;
		for (var i in user_weapons) {
			addWeaponElements(user_weapons[i]);
			// look for equipped weapons
			if (user_weapons[i]['equipped'] > 0) {
				for (var j = 1; j <= user_weapons[i]['equipped']; j++) {
					$("#weapon_select_"+select_id).val(user_weapons[i]['name']);
					selectWeapon(select_id++, false);
					if (is_mobile) {
						// TODO make these auto-expanded on load on mobile
						// $("#weapon_"+(select_id-1)).trigger("click");
					}
				}
			}
		}

		// check for user protections
		user_protections = <?php echo json_encode($protections); ?>;
		for (var i in user_protections) {
			addProtectionElements(user_protections[i]);
			// check if protection is equipped
			if (user_protections[i]['equipped'] == 1) {
				$("#protection_"+user_protections[i]['id']+"_equip_ban").toggle();
			}
		}
		// update toughness for equipped protections
		setToughness();

		// check for user healings
		user_healings = <?php echo json_encode($healings); ?>;
		for (var i in user_healings) {
			addHealingElements(user_healings[i]);
		}

		// check for user misc items
		user_misc = <?php echo json_encode($misc); ?>;
		for (var i in user_misc) {
			addMiscElements(user_misc[i]);
		}
		// show encumbered alert after all items are loaded
		loadingItems = false;

		// check for user notes
		user_notes = <?php echo json_encode($notes); ?>;
		for (var i in user_notes) {
			addNoteElements(user_notes[i]);
		}

	</script>

</body>
</html>