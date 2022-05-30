<?php

	// establish database connection
	include_once('db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// get user list for dropdown nav
	$users = [];
	$sql = "SELECT * from user";
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	array_push($users, $row);
    }
  }

	$feats = [];
	$trainings = [];
	$weapons = [];
	$protections = [];
	$healings = [];
	$misc = [];

	// check for user parameter in url
	if (isset($_GET["user"])) {
    $sql = "SELECT * FROM user WHERE id = ".$_GET["user"];
    $result = $db->query($sql);
    if ($result->num_rows === 1) {
	    while($row = $result->fetch_assoc()) {
	    	$user = $row;
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
	<title>The Lost City!</title>
	<link rel="icon" type="image/png" href="/assets/image/favicon.ico"/>

	<!-- CSS -->
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Alegreya:ital,wght@0,400;1,400;1,600&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">

	<link rel="stylesheet" type="text/css" href="/assets/style.css">

</head>

<body>

	<!-- user menu -->
  <nav class="navbar">
    <div class="nav-menu">
      <div class="nav-item">
         <span class="glyphicon glyphicon-floppy-save" onclick="formSubmit()"><span class="nav-item-label"> Save Character Data</span></span>
      </div>
      <div class="nav-item">
         <span class="glyphicon glyphicon-circle-arrow-up" onclick="allocateAttributePts()"><span class="nav-item-label"> Allocate Attribute Points</span></span>
      </div>
    </div>
    <div class="attribute-pts">
	    <div class="attribute-count"></div>
	    <div><span class="glyphicon glyphicon-ok" onclick="endEditAttributes(true)"><span class="nav-item-label"> Accept Changes</span></span></div>
	    <div><span class="glyphicon glyphicon-remove" onclick="endEditAttributes(false)"><span class="nav-item-label"> Discard Changes</span></span></div>
    </div>
		<span class="glyphicon glyphicon-menu-hamburger" onclick="toggleMenu()"></span>
  </nav>

	<!-- <div class="container"> -->
		<div class="header">
			<div class="row">
				<div class="col-xs-4">
					<h1>Welcome to...<br>The Lost City!</h1>
				</div>
			</div>
		</div>
		<div id="is_mobile"></div>

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

		<!-- TODO add character picture? -->

		<form id="user_form" method="post" action="/submit.php" novalidate>
			<input type="hidden" id="user_id" name="user_id" value="<?php echo isset($user) ? $user['id'] : '' ?>">
			<div class="row">
				<div class="col-md-6">

					<!-- section: name, level, xp -->
					<div class="section form-horizontal">
						<div class="form-group">
							<label class="control-label col-sm-2 col-xs-4" for="character_name">Name</label>
							<div class="col-sm-4 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="text" id="character_name" name="character_name" value="<?php echo isset($user) ? htmlspecialchars($user['character_name']) : '' ?>">
							</div>
							<label class="control-label col-sm-4 col-xs-4" for="attribute_pts">Attribute Pts</label>
							<div class="col-sm-2 col-xs-8">
								<input class="form-control" type="number" id="attribute_pts" name="attribute_pts" value="<?php echo isset($user) ? htmlspecialchars($user['attribute_pts']) : 12 ?>">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2 col-xs-4" for="xp">Experience</label>
							<div class="col-sm-4 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="number" name="xp" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['xp']) : '' ?>">
							</div>
							<!-- TODO calculate level from xp? -->
							<label class="control-label col-sm-2 col-xs-4" for="level">Level</label>
							<div class="col-sm-4 col-xs-8">
								<input class="form-control" type="number" name="level" min="1" value="<?php echo isset($user) ? htmlspecialchars($user['level']) : '' ?>">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2 col-xs-4" for="morale">Morale</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="number" name="morale" min="-10" value="<?php echo isset($user) ? htmlspecialchars($user['morale']) : '' ?>">
							</div>
							<!-- TODO calculate effect from morale? -->
							<label class="control-label col-sm-2 col-xs-4" for="morale_effect">Effect</label>
							<div class="col-sm-6 col-xs-8">
								<input class="form-control" type="text" name="morale_effect" value="<?php echo isset($user) ? htmlspecialchars($user['morale_effect']) : '' ?>">
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
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="text" name="race" value="<?php echo isset($user) ? htmlspecialchars($user['race']) : '' ?>">
							</div>
							<label class="control-label col-sm-2 col-xs-4" for="age">Age</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="text" name="age" id="age_text" value="<?php echo isset($user) ? htmlspecialchars($user['age']) : '' ?>">
								<input class="form-control hidden-number" type="number" id="age">
							</div>
							<label class="control-label col-sm-2 col-xs-4" for="gender">Gender</label>
							<div class="col-sm-2 col-xs-8">
								<input class="form-control" type="text" name="gender" value="<?php echo isset($user) ? htmlspecialchars($user['gender']) : '' ?>">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2 col-xs-4" for="height">Height</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="text" name="height" id="height_text" value="<?php echo isset($user) ? htmlspecialchars($user['height']) : '' ?>">
								<input class="form-control hidden-number" type="number" id="height">
							</div>
							<label class="control-label col-sm-2 col-xs-4" for="weight">Weight</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="text" name="weight" id="weight_text" value="<?php echo isset($user) ? htmlspecialchars($user['weight']) : '' ?>">
								<input class="form-control hidden-number" type="number" id="weight">
							</div>
							<label class="control-label col-sm-2 col-xs-4" for="eyes">Eyes</label>
							<div class="col-sm-2 col-xs-8">
								<input class="form-control" type="text" name="eyes" value="<?php echo isset($user) ? htmlspecialchars($user['eyes']) : '' ?>">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2 col-xs-4" for="hair">Hair</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="text" name="hair" value="<?php echo isset($user) ? htmlspecialchars($user['hair']) : '' ?>">
							</div>
							<label class="control-label col-sm-2 col-xs-4" for="other">Other</label>
							<div class="col-sm-6 col-xs-8">
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
							<div class="section-title" id="section_attack">Attack</div>
							<div class="row">

								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label col-md-12 center full-width" for="weapon_1">Weapon 1<span class="glyphicon glyphicon-chevron-down" id="weapon_1" onclick="toggleWeapon('weapon_1', this)"></span></label>
										<div class="col-md-12">
											<input class="form-control weapon-name" type="text" name="weapon_1" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_1']) : '' ?>">
										</div>
									</div>
									<div id="weapon_1_container" class="weapon-container">
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_1_damage">Damage</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="number" name="weapon_1_damage" min="1" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_1_damage']) : '' ?>">
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_1_crit">Critical</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="number" name="weapon_1_crit" min="1" max="6" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_1_crit']) : '' ?>">
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_1_range">Range</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="number" name="weapon_1_range" min="1" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_1_range']) : '' ?>">
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_1_rof">R o F</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="text" name="weapon_1_rof" id="weapon_1_rof_text" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_1_rof']) : '' ?>">
												<input class="form-control hidden-number" type="number" id="weapon_1_rof">
											</div>
										</div>
									</div>
								</div>

								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label col-md-12 center full-width" for="weapon_2">Weapon 2<span class="glyphicon glyphicon-chevron-down" id="weapon_2" onclick="toggleWeapon('weapon_2', this)"></span></label>
										<div class="col-md-12">
											<input class="form-control weapon-name" type="text" name="weapon_2" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_2']) : '' ?>">
										</div>
									</div>
									<div id="weapon_2_container" class="weapon-container">
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_2_damage">Damage</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="number" name="weapon_2_damage" min="1" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_2_damage']) : '' ?>">
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_2_crit">Critical</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="number" name="weapon_2_crit" min="1" max="6" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_2_crit']) : '' ?>">
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_2_range">Range</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="number" name="weapon_2_range" min="1" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_2_range']) : '' ?>">
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_2_rof">R o F</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="text" name="weapon_2_rof" id="weapon_2_rof_text" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_2_rof']) : '' ?>">
												<input class="form-control hidden-number" type="number" id="weapon_2_rof">
											</div>
										</div>
									</div>
								</div>

								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label col-md-12 center full-width" for="weapon_3">Weapon 3<span class="glyphicon glyphicon-chevron-down" id="weapon_3" onclick="toggleWeapon('weapon_3', this)"></span></label>
										<div class="col-md-12">
											<input class="form-control weapon-name" type="text" name="weapon_3" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_3']) : '' ?>">
										</div>
									</div>
									<div id="weapon_3_container" class="weapon-container">
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_3_damage">Damage</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="number" name="weapon_3_damage" min="1" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_3_damage']) : '' ?>">
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_3_crit">Critical</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="number" name="weapon_3_crit" min="1" max="6" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_3_crit']) : '' ?>">
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_3_range">Range</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="number" name="weapon_3_range" min="1" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_3_range']) : '' ?>">
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-7 col-xs-4" for="weapon_3_rof">R o F</label>
											<div class="col-md-5 col-xs-8">
												<input class="form-control" type="text" name="weapon_3_rof" id="weapon_3_rof_text" value="<?php echo isset($user) ? htmlspecialchars($user['weapon_3_rof']) : '' ?>">
												<input class="form-control hidden-number" type="number" id="weapon_3_rof">
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
						<div class="section-title" id="section_defense">Defense</div>
						<div class="form-group">
							<label class="control-label col-sm-2 col-xs-4" for="toughness">Toughness</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="text" name="toughness" id="toughness_text" value="<?php echo isset($user) ? htmlspecialchars($user['toughness']) : '' ?>">
								<input class="form-control hidden-number" type="number" id="toughness">
							</div>
							<label class="control-label col-sm-2 col-xs-4" for="defend">Defend</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="text" name="defend" id="defend_text" value="<?php echo isset($user) ? htmlspecialchars($user['defend']) : '' ?>">
								<input class="form-control hidden-number" type="number" id="defend">
							</div>
							<label class="control-label col-sm-2 col-xs-4" for="dodge">Dodge</label>
							<div class="col-sm-2 col-xs-8">
								<input class="form-control" type="text" name="dodge" id="dodge_text" value="<?php echo isset($user) ? htmlspecialchars($user['dodge']) : '' ?>">
								<input class="form-control hidden-number" type="number" id="dodge">
							</div>
						</div>
						<div class="form-group">
							<!-- <label class="control-label col-md-2" for="magic">Magic</label>
							<div class="col-md-1 no-pad">
								<input class="form-control" type="text" name="magic">
							</div> -->
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
						<div class="section-title" id="section_health">Health</div>
						<div class="form-group">

							<div class="col-sm-4">
								<div class="row">
									<label class="control-label col-sm-12 center full-width" for="damage">Resilience</label>
								</div>
								<div class="row">
									<div class="col-xs-5 no-pad">
										<input class="form-control" type="number" name="damage" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['damage']) : '' ?>">
									</div>
									<div class="col-xs-2 center no-pad">
										/
									</div>
									<div class="col-xs-5 no-pad">
										<!-- TODO on change, set max value for damage -->
										<input class="form-control" type="number" name="resilience" min="1" value="<?php echo isset($user) ? htmlspecialchars($user['resilience']) : '' ?>">
									</div>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="row">
									<label class="control-label col-sm-12 center full-width" for="wounds">Wounds</label>
								</div>
								<div class="row">
									<div class="col-xs-5 no-pad">
										<input class="form-control" type="number" name="wounds" min="0" max="2" value="<?php echo isset($user) ? htmlspecialchars($user['wounds']) : '' ?>">
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
						<div class="section-title" id="section_actions">Actions, Move, Initiative</div>
						<div class="form-group">
							<label class="control-label col-sm-2 col-xs-4" for="standard">Standard</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="number" name="standard" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['standard']) : '' ?>">
							</div>
							<label class="control-label col-sm-2 col-xs-4" for="quick">Quick</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="number" name="quick" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['quick']) : '' ?>">
							</div>
							<label class="control-label col-sm-2 col-xs-4" for="free">Free</label>
							<div class="col-sm-2 col-xs-8">
								<input class="form-control" type="number" name="free" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['free']) : '' ?>">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2 col-xs-4" for="move">Move</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="number" name="move" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['move']) : '' ?>">
							</div>
							<label class="control-label col-sm-2 col-xs-4" for="initiative">Initiative</label>
							<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
								<input class="form-control" type="number" name="initiative" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['initiative']) : '' ?>">
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
						<div class="section-title" id="section_attributes">Attributes</div>

						<div class="form-group">
							<div class="col-sm-6 attribute-col" id="col_strength">
								<div class="row">
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
								<div class="row">
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
								<div class="row">
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
								<div class="row">
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
								<div class="row">
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
								<div class="row">
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
								<div class="row">
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
								<div class="row">
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
								<div class="row">
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
								<div class="row">
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
								<div class="row">
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
								<div class="row">
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
						<div class="section-title" id="section_motivators">Motivators</div>

						<div class="form-group no-margin">
							<div class="col-xs-3 no-pad-mobile no-pad-left">
								<input class="form-control" type="text" name="motivator_1" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_1']) : '' ?>">
							</div>
							<label class="control-label col-xs-2 no-pad-mobile" for="motivator_1_pts">Points:</label>
							<div class="col-xs-1 no-pad">
								<input class="form-control" type="number" name="motivator_1_pts" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_1_pts']) : '' ?>">
							</div>

							<div class="col-xs-3 no-pad-mobile pad-left-mobile">
								<input class="form-control" type="text" name="motivator_2" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_2']) : '' ?>">
							</div>
							<label class="control-label col-xs-2 no-pad-mobile" for="motivator_2_pts">Points:</label>
							<div class="col-xs-1 no-pad">
								<input class="form-control" type="number" name="motivator_2_pts" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_2_pts']) : '' ?>">
							</div>
						</div>

						<div class="form-group no-margin">
							<div class="col-xs-3 no-pad-mobile no-pad-left">
								<input class="form-control" type="text" name="motivator_3" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_3']) : '' ?>">
							</div>
							<label class="control-label col-xs-2 no-pad-mobile" for="motivator_3_pts">Points:</label>
							<div class="col-xs-1 no-pad">
								<input class="form-control" type="number" name="motivator_3_pts" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_3_pts']) : '' ?>">
							</div>

							<div class="col-xs-3 no-pad-mobile pad-left-mobile">
								<input class="form-control" type="text" name="motivator_4" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_4']) : '' ?>">
							</div>
							<label class="control-label col-xs-2 no-pad-mobile" for="motivator_4_pts">Points:</label>
							<div class="col-xs-1 no-pad">
								<input class="form-control" type="number" name="motivator_4_pts" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['motivator_4_pts']) : '' ?>">
							</div>
						</div>

					</div>
					<!-- end section: motivators -->

					<!-- section: feats & traits -->
					<div class="section form-horizontal">
						<div class="section-title" id="section_feats">Feats & Traits</div>
						<div class="form-group"><div class="col-sm-12"><div id="feats"></div></div></div>
						<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target="#new_feat_modal"></span></button>
					</div>
					<!-- end section: feats & traits -->
					
				</div>

				<!-- section: weapons -->
				<div class="col-md-12">
					<div class="section form-horizontal">
						<div class="section-title" id="section_items">Weapons</div>
						<div class="form-group">
							<label class="control-label col-xs-3 resize-mobile center" for="weapons[]">Item</label>
							<label class="control-label col-xs-1 resize-mobile center" for="weapon_qty[]">Qty</label>
							<label class="control-label col-xs-1 resize-mobile center" for="weapon_damage[]">Damage</label>
							<label class="control-label col-xs-5 resize-mobile center" for="weapon_notes[]">Notes</label>
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
						<div class="section-title">Protection</div>
						<div class="form-group">
							<label class="control-label col-xs-3 resize-mobile center" for="protections[]">Item</label>
							<label class="control-label col-xs-2 resize-mobile center" for="protection_bonus[]">Bonus</label>
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
						<div class="section-title">Healing, Potions, & Drugs</div>
						<div class="form-group">
							<label class="control-label col-xs-3 resize-mobile center" for="healings[]">Item</label>
							<label class="control-label col-xs-2 resize-mobile center" for="healing_quantity[]">Qty</label>
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
						<div class="section-title">Miscellaneous & Special Items</div>
						<div class="form-group">
							<label class="control-label col-xs-3 resize-mobile center" for="misc[]">Item</label>
							<label class="control-label col-xs-2 resize-mobile center" for="misc_quantity[]">Qty</label>
							<label class="control-label col-xs-5 resize-mobile center" for="misc_notes[]">Notes</label>
							<label class="control-label col-xs-1 resize-mobile center" for="misc_weight[]">Weight</label>
							<label class="control-label col-xs-1 resize-mobile center" for=""></label>
						</div>
						<div id="misc"></div>
						<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target="#new_misc_modal"></span></button>
					</div>
				</div>
				<!-- end section: misc -->

				<!-- section: special items -->
				<!-- <div class="col-md-12">
					<div class="section form-horizontal">
						<div class="section-title">Special Items</div>
						<div class="form-group">
							<label class="control-label col-md-3 resize-mobile center" for="special[]">Item</label>
							<label class="control-label col-md-2 resize-mobile center" for="special_quantity[]">Qty</label>
							<label class="control-label col-md-6 resize-mobile center" for="special_notes[]">Notes</label>
							<label class="control-label col-md-1 resize-mobile center" for="special_weight[]">Weight</label>
						</div>
						<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target=""></span></button>
					</div>
				</div> -->
				<!-- end section: special items -->

				<!-- section: weight -->
				<div class="col-md-12">
					<div class="section form-horizontal">
						<div class="form-group">
							<label class="control-label col-xs-10 align-right" for="total_weight">Total Weight</label>
							<div class="col-xs-2">
								<input class="form-control" type="text" name="total_weight" id="total_weight_text" value="<?php echo isset($user) ? htmlspecialchars($user['total_weight']) : '' ?>">
								<input class="form-control hidden-number" type="number" id="total_weight">
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-12">
					<div class="section form-horizontal">
						<div class="section-title" id="section_weight">Weight Capacity</div>
						<div class="form-group">
							<label class="control-label col-xs-3 center resize-mobile-small" for="unhindered">Unhindered (1/4)</label>
							<label class="control-label col-xs-3 center resize-mobile-small" for="encumbered">Encumbered (1/2)</label>
							<label class="control-label col-xs-3 center resize-mobile-small" for="burdened">Burdened (3/4)</label>
							<label class="control-label col-xs-3 center resize-mobile-small" for="overburdened">Overburdened (Full)</label>

							<div class="col-xs-3">
								<input class="form-control" type="number" name="unhindered" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['unhindered']) : '' ?>">
							</div>
							<div class="col-xs-3">
								<input class="form-control" type="number" name="encumbered" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['encumbered']) : '' ?>">
							</div>
							<div class="col-xs-3">
								<input class="form-control" type="number" name="burdened" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['burdened']) : '' ?>">
							</div>
							<div class="col-xs-3">
								<input class="form-control" type="number" name="overburdened" min="0" value="<?php echo isset($user) ? htmlspecialchars($user['overburdened']) : '' ?>">
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
						<div class="section-title" id="section_notes">Notes</div>
						<div class="form-group">
							<div class="col-xs-12">
								<textarea class="form-control" rows="4" name="notes" maxlength="2000"><?php echo isset($user) ? htmlspecialchars($user['notes']) : '' ?></textarea>
							</div>
						</div>
					</div>
				</div>
				<!-- end section: notes -->

				<!-- section: background -->
				<div class="col-md-12">
					<div class="section form-horizontal">
						<div class="section-title">Character Background</div>
						<div class="form-group">
							<div class="col-xs-12">
								<textarea class="form-control" rows="6" name="background" maxlength="2000"><?php echo isset($user) ? htmlspecialchars($user['background']) : '' ?></textarea>
							</div>
						</div>
					</div>
				</div>
				<!-- end section: background -->

			</div>
			<!-- <div class="submit-btn">
				<button type="button" class="btn btn-primary" onclick="formSubmit()">Save Player Data</button>
			</div> -->
			<input type="hidden" name="password" id="password_val">
			<input type="hidden" name="recaptcha_response" id="recaptcha_response">
			<input type="hidden" name="duckdacoy" id="duckdacoy">
		</form>
	<!-- </div> -->

	<!-- new feat modal -->
  <div class="modal" id="new_feat_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title" id="feat_modal_title">New Feat</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Feat Name</label>
        	<input class="form-control" type="text" id="feat_name">
        	<label class="control-label">Feat Description</label>
        	<textarea class="form-control" id="feat_description" rows="6" maxlength="255"></textarea>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newFeat()" id="feat_submit_btn">Ok</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new training modal -->
  <div class="modal" id="new_training_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title" id="training_modal_title">New Skill Training</h4>
        </div>
        <div class="modal-body">
        	<h4 class="control-label">Training Name</h4>
        	<input class="form-control" type="text" id="training_name">
        	<br>
        	<!-- skill type: hidden unless allocating points -->
        	<div id="skill_type">
        		<h4 class="control-label">
        			Unique or standard skill?
        		</h4>
	        	<div class="form-check">
		        	<input class="form-check-input" type="radio" name="skill_type" id="unique" value="4">
		        	<label class="form-check-label" for="unique">Unique Skill (4 attribute pts)</label>
	        	</div>
	        	<div class="form-check">
		        	<input class="form-check-input" type="radio" name="skill_type" id="standard" value="1">
		        	<label class="form-check-label" for="standard">Standard Skill (1 attribute pt)</label>
	        	</div>
        	</div>
        	<input type="hidden" id="attribute_type">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newTraining()">Ok</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new weapon modal -->
  <div class="modal" id="new_weapon_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title">New Weapon</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Weapon Name</label>
        	<input class="form-control" type="text" id="weapon_name">
        	<label class="control-label">Damage</label>
        	<input class="form-control" type="text" id="weapon_damage">
        	<label class="control-label">Notes</label>
        	<input class="form-control" type="text" id="weapon_notes">
        	<label class="control-label">Weight</label>
        	<input class="form-control" type="text" id="weapon_weight">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newWeapon()">Ok</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new protection modal -->
  <div class="modal" id="new_protection_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title">New Protection</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Protection Name</label>
        	<input class="form-control" type="text" id="protection_name">
        	<label class="control-label">Bonus</label>
        	<input class="form-control" type="text" id="protection_bonus">
        	<label class="control-label">Notes</label>
        	<input class="form-control" type="text" id="protection_notes">
        	<label class="control-label">Weight</label>
        	<input class="form-control" type="text" id="protection_weight">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newProtection()">Ok</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new healing modal -->
  <div class="modal" id="new_healing_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title">New Healing, Potion, or Drug</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Item Name</label>
        	<input class="form-control" type="text" id="healing_name">
        	<label class="control-label">Quantity</label>
        	<input class="form-control" type="text" id="healing_quantity">
        	<label class="control-label">Effect</label>
        	<input class="form-control" type="text" id="healing_effect">
        	<label class="control-label">Weight</label>
        	<input class="form-control" type="text" id="healing_weight">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newHealing()">Ok</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new miscellaneous modal -->
  <div class="modal" id="new_misc_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title">New Miscellaneous Item</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Item Name</label>
        	<input class="form-control" type="text" id="misc_name">
        	<label class="control-label">Quantity</label>
        	<input class="form-control" type="text" id="misc_quantity">
        	<label class="control-label">Notes</label>
        	<input class="form-control" type="text" id="misc_notes">
        	<label class="control-label">Weight</label>
        	<input class="form-control" type="text" id="misc_weight">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newMisc()">Ok</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new password modal -->
  <div class="modal" id="new_password_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title">New Character</h4>
        </div>
        <div class="modal-body">
        	<h4>Please set a password for your new character. Also, you should probably write it down or something.</h4>
        	<input class="form-control" type="password" id="new_password">
        	<label class="control-label" for="password_conf">Confirm password</label>
        	<input class="form-control" type="password" id="password_conf" name="password_conf">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-primary" id="password_btn" data-dismiss="modal" data-toggle="modal" data-target="#new_password_modal_2" disabled>Ok</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- new password modal 2 -->
  <div class="modal" id="new_password_modal_2" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title">New Character</h4>
        </div>
        <div class="modal-body">
        	<h4 class="center">Before we can let you pass, we just need to make sure you're not a robot. Please answer the following question that all nerds should know.</h4>
        	<p>Ernest Gary _____ (July 27, 1938 – March 4, 2008) was an American game designer and author best known for co-creating the pioneering role-playing game Dungeons & Dragons with Dave Arneson.</p>
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
      <div class="modal-content searching-prompt">
        <div class="modal-body center">
					<span class="glyphicon glyphicon-refresh spinning"></span> Waiting for server response...
        </div>
      </div>
    </div>
  </div>

	<!-- new password modal 3 -->
  <div class="modal" id="new_password_modal_3" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title">New Character</h4>
        </div>
        <div class="modal-body">
        	<h4 class="center">Did you write it down yet?</h4>
        	<div class="form-check">
	        	<input class="form-check-input" type="radio" name="slacker" id="1" value="1">
	        	<label class="form-check-label" for="1">No, but I have really good memory.</label>
        	</div>
        	<div class="form-check">
	        	<input class="form-check-input" type="radio" name="slacker" id="2" value="2">
	        	<label class="form-check-label" for="2">No, but I made sure to pick something really easy to remember.</label>
        	</div>
        	<div class="form-check">
	        	<input class="form-check-input" type="radio" name="slacker" id="3" value="3">
	        	<label class="form-check-label" for="3">Not yet. I'm about to. Chill out.</label>
        	</div>
        	<div class="form-check">
	        	<input class="form-check-input" type="radio" name="slacker" id="4" value="4">
	        	<label class="form-check-label" for="4">Yeah, totally!</label>
        	</div>
        	<div class="button-bar">
		        <button type="button" class="btn btn-primary" id="password_btn_2" data-dismiss="modal" onclick="setPassword2()" disabled>Ok</button>
		      </div>
        </div>
      </div>
    </div>
  </div>

	<!-- password modal -->
  <div class="modal" id="password_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title">Update Character</h4>
        </div>
        <div class="modal-body">
        	<h4>Please enter your password to update your character</h4>
        	<input class="form-control" type="password" id="password">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-primary" id="password_btn" onclick="validatePassword()">Ok</button>
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
      <div class="modal-content searching-prompt">
        <div class="modal-header">
          <h4 class="modal-title">Forgot My Password</h4>
        </div>
        <div class="modal-body">
        	<h4 class="center">Did you try crab?</h4>
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary forgot-password-btn" data-dismiss="modal" onclick="forgotPassword()">Yes, I tried crab. That wasn't it.</button>
	        	<button type="button" class="btn btn-primary forgot-password-btn" data-dismiss="modal" onclick="forgotPassword()">No, it's definitely not crab.</button>
        	</div>
        </div>
      </div>
    </div>
  </div>

	<!-- JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script async src="https://www.google.com/recaptcha/api.js?render=6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="/assets/script_v22_05_30.js"></script>

	<script type="text/javascript">

		// check for user and set attributes
		var user = <?php echo json_encode(isset($user) ? $user : []); ?>;
		setAttributes(user);

		// check for user feats
		var feats = <?php echo json_encode($feats); ?>;
		for (var i in feats) {
			addFeatElements(feats[i]['name'], feats[i]['description']);
		}

		// check for user trainings
		var trainings = <?php echo json_encode($trainings); ?>;
		for (var i in trainings) {
			addTrainingElements(trainings[i]['name'], trainings[i]['attribute_group'], trainings[i]['value']);
		}

		// check for user weapons
		var weapons = <?php echo json_encode($weapons); ?>;
		for (var i in weapons) {
			addWeaponElements(weapons[i]['name'], weapons[i]['quantity'], weapons[i]['damage'], weapons[i]['notes'], weapons[i]['weight']);
		}

		// check for user protections
		var protections = <?php echo json_encode($protections); ?>;
		for (var i in protections) {
			addProtectionElements(protections[i]['name'], protections[i]['bonus'], protections[i]['notes'], protections[i]['weight']);
		}

		// check for user healings
		var healings = <?php echo json_encode($healings); ?>;
		for (var i in healings) {
			addHealingElements(healings[i]['name'], healings[i]['quantity'], healings[i]['effect'], healings[i]['weight']);
		}

		// check for user misc items
		var misc = <?php echo json_encode($misc); ?>;
		for (var i in misc) {
			addMiscElements(misc[i]['name'], misc[i]['quantity'], misc[i]['notes'], misc[i]['weight']);
		}

	</script>

</body>
</html>