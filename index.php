<?php

	session_set_cookie_params(604800);
	session_start();

	if (isset($_POST['logout'])) {
	  session_destroy();
	  header('Location: /login.php');
	}

	// make sure we are logged in - check for existing session
	if (!isset($_SESSION['login_id'])) {
    header('Location: /login.php');
	}
	$login_id = $_SESSION['login_id'];

	// establish database connection
	include_once('config/db_config.php');
	include_once('config/keys.php');

	// check for campaign parameter in url
	if (!isset($_GET["campaign"])) {
		// redirect to campaign select page
		header('Location: /select_campaign.php');
	}

	// delete any unnamed (unsaved) users
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// get current login info (email)
	$login;
	$sql = "SELECT * from login WHERE id = $login_id";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$login = $row;
		}
	}

	// make sure campaign id variables are valid - redirect to select campaign if not
	$campaign_id = $_GET["campaign"];
	$sql = "SELECT * FROM campaign WHERE id = $campaign_id";
	$result = $db->query($sql);
	if ($result->num_rows === 0) {
		header('Location: /select_campaign.php');
  }

	// make sure user id variables are valid - redirect to new character if not
	if (isset($_GET["user"])) {
		$user_id = $_GET["user"];
		$sql = "SELECT * FROM user WHERE id = $user_id";
		$result = $db->query($sql);
		if ($result->num_rows === 0) {
			header('Location: /?campaign='.$campaign_id);
	  }
	}

	$sql = "DELETE FROM user WHERE character_name IS NULL";
	$db->query($sql);
	// TODO reset the auto-increment to max(id)+1 ?
	// $sql = "ALTER TABLE `user` AUTO_INCREMENT = 1";
	// $db->query($sql);
	// would create issues if multiple people were creating characters at once
	// move delete statement to window.unload function?

	// check user campaign role
	$campaign_role = 2;
	$sql = "SELECT campaign_role FROM login_campaign WHERE login_id = $login_id";
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	$campaign_role = $row['campaign_role'];
    }
  }

	// get user list for dropdown nav
	$users = [];
	$sql = "SELECT * FROM user WHERE campaign_id = $campaign_id ORDER BY character_name";
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	array_push($users, $row);
    }
  }

  // get feat_id list
  $feat_ids = [];
	$sql = "SELECT feat_id FROM campaign_feat WHERE campaign_id = $campaign_id";
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	array_push($feat_ids, $row['feat_id']);
    }
  }
  // add race_trait feat_ids
	$sql = "SELECT trait_id FROM race_trait";
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	array_push($feat_ids, $row['trait_id']);
    }
  }

	// get active counts for each feat type
	$counts = [];
	$sql = "SELECT feat_or_trait.type, feat_or_trait.cost FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_feat.campaign_id = $campaign_id";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$type = $row['type'] == 'physical_trait' && $row['cost'] > 0 ? 'physical_trait_pos' : 
				($row['type'] == 'physical_trait' && $row['cost'] < 0 ? 'physical_trait_neg' : $row['type']);
			$counts[$type] = isset($counts[$type]) ? $counts[$type] + 1 : 1;
		}
	}
  
  // get feat list
	$feat_list = [];
	$sql = "SELECT * FROM feat_or_trait WHERE id != 0";
	if (count($feat_ids) > 0) {
		$sql .= " AND id IN (".implode(',',$feat_ids).")";
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

  // get talents
  $talents = [];
  $no_id = [];
	$json_string = file_get_contents($keys['feat_list']);
	$talent_list_json = json_decode($json_string);

	// get trainings for autocomplete
	$json_string = file_get_contents($keys['training_list']);
	$training_autocomplete = json_decode($json_string);

	// assign talent ID
	foreach($talent_list_json as $json) {
		$found = false;
		foreach($feat_list as $feat) {
			if ($feat['name'] == $json->name) {
				$json->id = $feat['id'];
				$found = true;
			}
		}
		array_push($talents, $json);
		if (!$found) {
			array_push($no_id, $json);
		}
	}
	usort($talents, function($a, $b) {
    	return $a->name <=> $b->name;
	});

  // get campaign name
  $sql = "SELECT * FROM campaign WHERE id = $campaign_id";
	$result = $db->query($sql);
	$campaign = "";
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	$campaign = $row;
    }
  }

  // get campaign races and race traits
  $race_ids = [];
  $races = [];
  $race_traits = [];
  $sql = "SELECT race_id FROM campaign_race WHERE campaign_id = $campaign_id";
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	array_push($race_ids, $row['race_id']);
    }
  }

  if (count($race_ids) > 0) {
	  $sql = "SELECT * FROM race WHERE id IN (".implode(',',$race_ids).") ORDER BY name";
		$result = $db->query($sql);
	  if ($result) {
	    while($row = $result->fetch_assoc()) {
	    	array_push($races, $row);
	    }
	  }

	  $sql = "SELECT * FROM race_trait WHERE race_id IN (".implode(',',$race_ids).")";
		$result = $db->query($sql);
	  if ($result) {
	    while($row = $result->fetch_assoc()) {
	    	array_push($race_traits, $row);
	    }
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
	    	$user['is_new'] = false;

	    	// get xp awards
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
		    $user['magic_talents'] = false;
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
	} else {
		// if no user set, create a new user entry
		$sql = "INSERT INTO user VALUES ()";
		$db->query($sql);
    $sql = "SELECT * FROM user WHERE id = ".$db->insert_id;
    $result = $db->query($sql);
    if ($result->num_rows === 1) {
	    while($row = $result->fetch_assoc()) {
	    	$user = $row;
	    	$user['is_new'] = true;
		    $user['magic_talents'] = false;
		    $user['attribute_pts'] = 12;
	    }
	  }
	}

	// check if user can edit (always true for campaign admin)
	$can_edit = $campaign_role == 1 ? 1 : 0;
	$sql = "SELECT id FROM user WHERE login_id = $login_id";
	$result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
    	$can_edit = $can_edit == 1 || $row['id'] == $user['id'] ? 1 : 0;
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
	<title><?php echo $campaign['name'].($user['is_new'] ? '' : ' : '.$user['character_name']) ?></title>
	<link rel="icon" type="image/png" href="/assets/image/favicon-pentacle.ico"/>

	<!-- Bootstrap -->
	<link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
	<!-- <link rel="stylesheet" type="text/css" href="/assets/font-awesome/font-awesome-6.1.1-all.min.css"> -->
	<!-- jQuery UI -->
	<link rel="stylesheet" type="text/css" href="/assets/jquery/jquery-ui-1.12.1.min.css">
	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;800&family=Alegreya:ital,wght@0,400;1,400;1,600&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
	<!-- Custom Styles -->
	<link rel="stylesheet" type="text/css" href="<?php echo $keys['styles'] ?>">

	<style type="text/css">
		.login-name .glyphicon:hover {
			cursor: default;
		}
	</style>

</head>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-R6WG932F36"></script>
<script>
	if (document.location.hostname.search("crabagain.com") !== -1) {
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'G-R6WG932F36');
	}
</script>

<body>

	<!-- use div visibility to determine if we're on mobile -->
	<div id="is_mobile"></div>

	<!-- user menu -->
	<nav class="navbar">

	  <div class="nav-menu">
	    <div class="nav-item login-name">
	      <span class="glyphicon"><span class="nav-item-label"><i class="fa-solid icon-crab custom-icon nav-icon"></i> <?php echo explode("@", $login['email'])[0]; ?></span></span>
	    </div>
	  	<!-- show 'Save' option for new characters -->
	    <?php
	    	if ($user['is_new']) {
	    		echo '
				    <div class="nav-item">
				      <span class="glyphicon" onclick="formSubmit()"><span class="nav-item-label"><i class="fa-solid fa-floppy-disk nav-icon"></i> Save New Character</span></span>
				    </div>
				   ';
	    	}
	    ?>
	    <!-- allocate attribute points -->
	    <?php
	    	if ($can_edit == 1) {
	    		echo '
				    <div class="nav-item">
				       <span id="attribute_pts_span" class="glyphicon '. ($user['attribute_pts'] == 0 ? 'disabled' : '') .'" onclick="allocateAttributePts(this)"><span class="nav-item-label"><i class="fa-solid fa-shield-heart nav-icon"></i> Allocate Attribute Points</span></span>
				    </div>
				   ';
	    	}
	    ?>
	    <!-- GM edit mode -->
	    <?php
	    	if (!$user['is_new'] && $campaign_role == 1) {
	    		echo '
				    <div class="nav-item">
				       <button type="button" class="glyphicon" onclick="GMEditMode()"><span class="nav-item-label"><i class="fa-solid fa-dice-d20 nav-icon"></i> GM Edit Mode</span></button>
				    </div>
				   ';
	    	}
	    ?>
	    <!-- campaign admin -->
	    <?php
	    	if ($campaign_role == 1) {
	    		echo '
				    <div class="nav-item">
				       <span class="glyphicon" onclick="settings()"><span class="nav-item-label"><i class="fa-solid fa-gear nav-icon"></i> Campaign Admin</span></span>
				    </div>
				   ';
	    	}
	    ?>
	    <div class="nav-item">
	       <span class="glyphicon" onclick="back()"><span class="nav-item-label"><i class="fa-solid fa-person-hiking nav-icon"></i> Change Campaign</span></span>
	    </div>
	    <div class="nav-item">
	    	<form method="post">
					<button class="glyphicon" type="submit" name="logout"><span class="nav-item-label"><i class="fa-solid icon-log custom-icon nav-icon"></i> Logout</span></button>
			  </form>
	       
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
	    <div><span class="glyphicon glyphicon-ok" onclick="endGMEdit()"><span class="nav-item-label"> Exit GM Mode</span></span></div>
	  </div>

	  <!-- hamburger menu -->
		<span class="glyphicon glyphicon-menu-hamburger" onclick="toggleMenu()"></span>

	  <!-- user help menu - visible only on character creation -->
	    <?php
	    	if ($user['is_new']) {
	    		echo '
	  				<span class="glyphicon glyphicon-info-sign help-menu" data-toggle="modal" data-target="#help_modal"></span>
	    		';
	    	}
	    ?>
	</nav>

	<?php 
		foreach(range(1, 5) as $i) {
			echo '<img id="banner-'.$i.'" class="banner-image '.($i == 5 ? 'active' : '').'" src="assets/image/banners/dnd-banner-'.$i.'.jpeg">';
		}
	?>

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
		<input type="hidden" id="user_id" name="user_id" value="<?php echo $user['id'] ?>">
		<input type="hidden" id="campaign_id" name="campaign_id" value="<?php echo $_GET["campaign"] ?>">
		<input type="hidden" id="user_email" name="email" value="<?php echo $user["email"] ?>">
		<input type="hidden" id="campaign_role" value="<?php echo $campaign_role ?>">
		<input type="hidden" id="can_edit" value="<?php echo $can_edit ?>">
		<input type="hidden" id="uuid">
		<div class="row">
			<div class="col-md-6">

				<!-- section: name, level, xp -->
				<div class="section form-horizontal two-column">

					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="character_name">Name</label>
						<div class="col-sm-6 col-xs-8 mobile-pad-bottom">
							<input class="form-control <?php echo $user['is_new'] ? '' : 'track-changes' ?>" type="text" id="character_name" name="character_name" value="<?php echo htmlspecialchars($user['character_name']) ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
						</div>
						<!-- unlock on character creation -->
						<label class="control-label col-sm-2 col-xs-4 font-small smaller" for="attribute_pts">Attribute Pts</label>
						<div class="col-sm-2 col-xs-8">
							<input class="form-control track-changes" <?php echo count($awards) == 0 ? 'type="number"' : 'readonly' ?> min="0" id="attribute_pts" name="attribute_pts" value="<?php echo htmlspecialchars($user['attribute_pts']) ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
						</div>
					</div>

					<div class="form-group tablet-adjust">
						<label class="control-label col-sm-2 col-xs-4" for="xp">XP</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control pointer track-changes" readonly data-toggle="modal" data-target="#xp_modal" name="xp" id="xp" min="0" value="<?php echo htmlspecialchars($user['xp']) ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
						</div>
						<!-- TODO unlock level on character creation and adjust attribute points on change -->
						<!-- only allow by GM? -->
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
								$i = 2;
								foreach ($levels as $lvl) {
									if ($user['xp'] >= $lvl) {
										$level = $i++;
									}
								}
							?>
							<input class="form-control" <?php echo count($awards) == 0 ? 'type="number"' : 'readonly' ?> name="level" min="1" id="level" value="<?php echo $level ?>">

						</div>
						<label class="control-label col-sm-2 col-xs-4 font-small smaller" for="caster_level">Caster Level</label>
						<div class="col-sm-2 col-xs-8">
							<?php
								$caster_level = $user['vitality'] + 10;
							?>
							<input class="form-control" readonly name="caster_level" id="caster_level" value="<?php echo $caster_level ?>">
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="morale">Morale</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<input class="form-control track-changes" type="number" name="morale" id="morale" min="-10" value="<?php echo htmlspecialchars($user['morale']) ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
						</div>
						<div class="col-xs-4 d-sm-none"></div>
						<div class="col-sm-4 col-xs-8 mobile-pad-bottom">
							<input class="form-control" readonly id="morale_effect" name="morale_effect">
						</div>
						<?php
							$fate = 0;
							// vitality bonus
							$fate += $user['vitality'] >= 0 ? floor($user['vitality']/2) : ( ceil($user['vitality']/3) == 0 ? 0 : ceil($user['vitality']/3) );
							// morale bonus
							$morale = htmlspecialchars($user['morale']);
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
				<div class="section form-horizontal two-column">
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="race">Race</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control track-changes" type="text" id="race" name="race" value="<?php echo htmlspecialchars($user['race']) ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user" <?php echo $user['is_new'] || count($awards) == 0 ? '' : 'readonly' ?> >
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="age">Age</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control" type="text" name="age" id="age_text" value="<?php echo htmlspecialchars($user['age']) ?>">
							<input class="form-control hidden-number track-changes" type="number" id="age" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user" data-column="age">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="gender">Gender</label>
						<div class="col-sm-2 col-xs-8 desktop-no-pad-left">
							<input class="form-control track-changes" type="text" id="gender" name="gender" value="<?php echo htmlspecialchars($user['gender']) ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="height">Height</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<select class="form-control track-changes" name="height" id="height" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
								<?php
									// limit range by user size - small: 36-59, medium: 60-83, large: 84-107
									$size = $user['size'] == "" ? "Medium" : $user['size'];
									$height = $user['height'] == "" ? 0 : $user['height'];
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
							<input class="form-control" type="text" name="weight" id="weight_text" value="<?php echo htmlspecialchars($user['weight']) ?>">
							<input class="form-control hidden-number track-changes" type="number" id="weight" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user" data-column="weight">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="eyes">Eyes</label>
						<div class="col-sm-2 col-xs-8 desktop-no-pad-left">
							<input class="form-control track-changes" type="text" id="eyes" name="eyes" value="<?php echo htmlspecialchars($user['eyes']) ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="hair">Hair</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom desktop-no-pad-left">
							<input class="form-control track-changes" type="text" id="hair" name="hair" value="<?php echo htmlspecialchars($user['hair']) ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="other">Other</label>
						<div class="col-sm-6 col-xs-8 desktop-no-pad-left">
							<input class="form-control track-changes" type="text" id="other" name="other" value="<?php echo htmlspecialchars($user['other']) ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
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
				<div class="section form-horizontal two-column">
					<div class="section-title" id="section_defense"><span>Defense</span> <i class="fa-solid fa-shield-halved"></i></div>
					<div class="form-group">
						<label class="control-label col-sm-2 col-xs-4" for="toughness">Toughness</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<?php
								$toughness = $user['strength'] >= 0 ? floor($user['strength']/2) : ( ceil($user['strength']/3) == 0 ? 0 : ceil($user['strength']/3) );
							?>
							<input class="form-control" readonly name="toughness" id="toughness" value="<?php echo $toughness ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="defend">Defend</label>
						<div class="col-sm-2 col-xs-8 mobile-pad-bottom">
							<?php
								$size_modifier = $user['size'] == "Small" ? 2 : ($user['size'] == "Large" ? -2 : 0);
								$defend = 10 + $user['agility'] + $size_modifier;
							?>
							<input class="form-control" readonly name="defend" id="defend" value="<?php echo $defend ?>">
						</div>
						<label class="control-label col-sm-2 col-xs-4" for="dodge">Dodge</label>
						<div class="col-sm-2 col-xs-8">
							<?php
								$dodge = $user['agility'] >= 0 ? floor($user['agility']/2) : ( ceil($user['agility']/3) == 0 ? 0 : ceil($user['agility']/3) );
								$dodge += $size_modifier;
							?>
							<input class="form-control" readonly name="dodge" id="dodge" value="<?php echo $dodge ?>">
						</div>
					</div>

					<div class="form-group row-narrow-desktop">
						<div class="col-sm-3 pad-left-right-zero">
							<label class="control-label col-sm-7 col-xs-4" for="magic">Magic</label>
							<div class="col-sm-5 col-xs-8 no-pad-input mobile-pad-bottom">
							<input class="form-control" type="text" name="magic" id="magic_text" value="<?php echo htmlspecialchars($user['magic']) ?>">
							<input class="form-control hidden-number track-changes" type="number" id="magic" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user" data-column="magic">
							</div>
						</div>
						<div class="col-sm-3 pad-left-right-zero">
							<label class="control-label col-sm-7 col-xs-4" for="fear">Fear</label>
							<div class="col-sm-5 col-xs-8 no-pad-input mobile-pad-bottom">
							<input class="form-control" type="text" name="fear" id="fear_text" value="<?php echo htmlspecialchars($user['fear']) ?>">
							<input class="form-control hidden-number track-changes" type="number" id="fear" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user" data-column="fear">
							</div>
						</div>
						<div class="col-sm-3 pad-left-right-zero">
							<label class="control-label col-sm-7 col-xs-4" for="poison">Poison</label>
							<div class="col-sm-5 col-xs-8 no-pad-input mobile-pad-bottom">
							<input class="form-control" type="text" name="poison" id="poison_text" value="<?php echo htmlspecialchars($user['poison']) ?>">
							<input class="form-control hidden-number track-changes" type="number" id="poison" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user" data-column="poison">
							</div>
						</div>
						<div class="col-sm-3 pad-left-right-zero">
							<label class="control-label col-sm-7 col-xs-4" for="disease">Disease</label>
							<div class="col-sm-5 col-xs-8 no-pad-input">
							<input class="form-control" type="text" name="disease" id="disease_text" value="<?php echo htmlspecialchars($user['disease']) ?>">
							<input class="form-control hidden-number track-changes" type="number" id="disease" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user" data-column="disease">
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
								<label class="control-label col-sm-12 center full-width" for="damage">
									Resilience
									<div id="resilience_adjust">
										<span class="fa-solid fa-circle-plus" onclick="adjustResilience(1)"></span>
										<span class="fa-solid fa-circle-minus" onclick="adjustResilience(-1)"></span>
									</div>
								</label>
							</div>
							<div class="row">
								<div class="col-xs-5 no-pad">
									<?php 
										$resilience = $user['fortitude'] >= 0 ? 3 + floor($user['fortitude']/2) : 3 + ceil($user['fortitude']/3);
										$damage = $user['damage'] != null ? $user['damage'] : 0;
										$wounds = 0;
										while ($damage >= $resilience) {
											$wounds += 1;
											$damage -= $resilience;
										}
										$wound_val = $wounds == 0 ? "None" : ($wounds == 1 ? "Wounded" : ($wounds == 2 ? "Incapacitated" : ($wounds == 3 ? "Mortally Wounded" : "Yer Dead")));
										$wound_penalty_val = $wounds == 0 ? "None" : ($wounds == 1 ? "-1" : ($wounds == 2 ? "-3" : ($wounds == 3 ? "-5" : "Yer Dead")));
									?>
									<input class="form-control" id="damage" type="number" min="<?php echo $wounds == 0 ? 0 : -1 ?>" value="<?php echo $damage ?>">
									<input type="hidden" class="track-changes" name="damage" id="total_damage" data-table="user" data-column="damage" data-id="<?php echo htmlspecialchars($user['id']) ?>">
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
										$fatigue = $user['fatigue'] == '' ? 0 : $user['fatigue'];
									?>
									<select class="form-control track-changes" id="fatigue" name="fatigue" onchange="updateTotalWeight(false)" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
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
								<label class="control-label col-sm-12 center full-width" for="encumbrance">Encumbrance</label>
							</div>
							<div class="row">
								<div class="col-xs-12 no-pad">
									<input class="form-control" type="text" readonly id="encumbrance">
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
				<div class="section form-horizontal two-column">
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
					<div class="section-title" id="section_attributes"><span>Attributes & Trainings</span> <i class="fa-solid fa-dice"></i></div>

					<div class="form-group">
						<div class="col-sm-6 attribute-col" id="col_strength">
							<div class="row attribute-row">
								<label class="control-label col-md-7 col-xs-8" for="strength"><span class="attribute-name">Strength</span><span class="glyphicon glyphicon-edit hover-hide" id="tog_strength" onclick="toggleHidden('col_strength')"></label>
								<div class="col-md-5 col-xs-4">
									<label class="control-label">
										<span class="attribute-val" id="strength_text"></span>
										<input type="hidden" class="track-changes" name="strength" id="strength_val" value="<?php echo $user['strength'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Strength_up" onclick="adjustAttribute('strength', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Strength_down" onclick="adjustAttribute('strength', -1)"></span>
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
										<input type="hidden" class="track-changes" name="fortitude" id="fortitude_val" value="<?php echo $user['fortitude'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Fortitude_up" onclick="adjustAttribute('fortitude', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Fortitude_down" onclick="adjustAttribute('fortitude', -1)"></span>
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
										<input type="hidden" class="track-changes" name="speed" id="speed_val" value="<?php echo $user['speed'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Speed_up" onclick="adjustAttribute('speed', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Speed_down" onclick="adjustAttribute('speed', -1)"></span>
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
										<input type="hidden" class="track-changes" name="agility" id="agility_val" value="<?php echo $user['agility'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Agility_up" onclick="adjustAttribute('agility', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Agility_down" onclick="adjustAttribute('agility', -1)"></span>
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
										<input type="hidden" class="track-changes" name="precision_" id="precision__val" value="<?php echo $user['precision_'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Precision_up" onclick="adjustAttribute('precision_', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Precision_down" onclick="adjustAttribute('precision_', -1)"></span>
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
										<input type="hidden" class="track-changes" name="awareness" id="awareness_val" value="<?php echo $user['awareness'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Awareness_up" onclick="adjustAttribute('awareness', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Awareness_down" onclick="adjustAttribute('awareness', -1)"></span>
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
										<input type="hidden" class="track-changes" name="allure" id="allure_val" value="<?php echo $user['allure'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Allure_up" onclick="adjustAttribute('allure', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Allure_down" onclick="adjustAttribute('allure', -1)"></span>
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
										<input type="hidden" class="track-changes" name="deception" id="deception_val" value="<?php echo $user['deception'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Deception_up" onclick="adjustAttribute('deception', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Deception_down" onclick="adjustAttribute('deception', -1)"></span>
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
										<input type="hidden" class="track-changes" name="intellect" id="intellect_val" value="<?php echo $user['intellect'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Intellect_up" onclick="adjustAttribute('intellect', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Intellect_down" onclick="adjustAttribute('intellect', -1)"></span>
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
										<input type="hidden" class="track-changes" name="innovation" id="innovation_val" value="<?php echo $user['innovation'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Innovation_up" onclick="adjustAttribute('innovation', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Innovation_down" onclick="adjustAttribute('innovation', -1)"></span>
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
										<input type="hidden" class="track-changes" name="intuition" id="intuition_val" value="<?php echo $user['intuition'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Intuition_up" onclick="adjustAttribute('intuition', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Intuition_down" onclick="adjustAttribute('intuition', -1)"></span>
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
										<input type="hidden" class="track-changes" name="vitality" id="vitality_val" value="<?php echo $user['vitality'] ?>" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user">
										<span class="glyphicon glyphicon-plus hidden-icon" id="Vitality_up" onclick="adjustAttribute('vitality', 1)"></span>
										<span class="glyphicon glyphicon-minus hidden-icon" id="Vitality_down" onclick="adjustAttribute('vitality', -1)"></span>
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
								$m_pts = 0;
								foreach ($user_motivators as $m) {
									if ($m['primary_'] == 1) {
										$m_pts += intval($m['points']);
									}
								}
								$bonuses = $m_pts >= 64 ? 5 : ($m_pts >= 32 ? 4 : ($m_pts >= 16 ? 3 : ($m_pts >= 8 ? 2 : ($m_pts >= 4 ? 1 : 0))));
								$set_motivators = count($user_motivators) == 0;
							?>
							<input class="form-control" readonly name="bonuses" id="bonuses" value="<?php echo $bonuses ?>">
						</div>
					</div>

					<div class="form-group no-margin"><div class="row">
					<?php
						for ($i = 0; $i < 4; $i++) {
							echo $i == 2 ? '</div></div><div class="form-group no-margin"><div class="row">' : '';
							echo '<div class="col-sm-6 no-pad '. ($i == 0 || $i == 2 ? 'bottom-pad-mobile' : '' ) .'"><div class="row"><div class="col-xs-8">';
							echo '<input class="form-control motivator-input '. (isset($user_motivators[$i]) && $user_motivators[$i]['primary_'] ? 'bold' : '') .' '. ($set_motivators ? 'pointer' : '') .'" type="text" name="motivators[]" id="motivator_'.$i.'" readonly value="'. (isset($user_motivators[$i]) ? $user_motivators[$i]['motivator'] : '') .'">';
							echo '</div>';
							echo '<label class="control-label col-xs-2 no-pad align-right font-mobile-small" for="motivator_pts_'.$i.'">Pts:</label>';
							echo '<div class="col-xs-2 no-pad">';
							echo '<input class="form-control motivator-pts" type="number" name="motivator_pts[]" id="motivator_pts_'.$i.'" min="0" value="'. (isset($user_motivators[$i]) ? $user_motivators[$i]['points'] : '') .'" '. ($set_motivators ? 'readonly' : '') .' data-val="'. (isset($user_motivators[$i]) ? $user_motivators[$i]['points'] : '') .'">';
							echo '</div>';
							echo '<input type="hidden" name="motivator_ids[]" value="'. (isset($user_motivators[$i]) ? $user_motivators[$i]['id'] : '') .'">';
							echo '<input type="hidden" name="motivator_primary[]" value="'. (isset($user_motivators[$i]) ? $user_motivators[$i]['primary_'] : '') .'" id="motivator_primary_'.$i.'"></div></div>';
						}
					?>
					</div></div>

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
								<div class="feat <?php echo $user['is_new'] || count($awards) == 0 ? '' : 'cursor-auto' ?>" id="size" data-toggle="<?php echo $user['is_new'] || count($awards) == 0 ? 'modal' : '' ?>" data-target="#edit_size_modal">
									<p class="feat-title">Size : </p>
						    	<?php
						    		$size = isset($user['size']) ? $user['size'] : 'Medium';
						    	?>
									<p id="character_size_text"><?php echo $size ?></p>
									<input type="hidden" name="size" id="character_size_val" value="<?php echo $size ?>">
								</div>
								<div id="race_traits"></div>
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
						<label class="control-label col-xs-3 mobile-hide center" for="weapons[]">Item</label>
						<label class="control-label col-xs-1 mobile-hide center" id="weapon_dmg_label" for="weapon_damage[]">Damage</label>
						<label class="control-label col-xs-5 mobile-hide center" id="weapon_note_label" for="weapon_notes[]">Notes</label>
						<label class="control-label col-xs-1 mobile-hide center" for="weapon_weight[]">Weight</label>
						<label class="control-label col-xs-1 mobile-hide center" for="weapon_qty[]">Qty</label>
						<label class="control-label col-xs-1 mobile-hide center" for=""></label>
					</div>
					<div id="weapons"></div>
					<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target="#new_weapon_modal"></span></button>
				</div>
			</div>
			<!-- end section: weapons -->

			<!-- section: protection -->
			<div class="col-md-12">
				<div class="section form-horizontal">
					<div class="section-title"><span>Protections</span> <i class="fa-solid icon-protection custom-icon"></i></div>
					<div class="form-group">
						<label class="control-label col-xs-1 col-icon mobile-hide" for="_eqip"></label>
						<label class="control-label col-xs-3 col-icon-right mobile-hide center" for="protections[]">Item</label>
						<label class="control-label col-xs-1 mobile-hide center" for="protection_bonus[]">Bonus</label>
						<label class="control-label col-xs-5 mobile-hide center" for="protection_notes[]">Notes</label>
						<label class="control-label col-xs-1 mobile-hide center" for="protection_weight[]">Weight</label>
						<label class="control-label col-xs-1 mobile-hide center" for=""></label>
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
						<label class="control-label col-xs-4 mobile-hide center" for="healings[]">Item</label>
						<label class="control-label col-xs-5 mobile-hide center" for="healing_effect[]">Effect</label>
						<label class="control-label col-xs-1 mobile-hide center" for="healing_weight[]">Weight</label>
						<label class="control-label col-xs-1 mobile-hide center" for="healing_quantity[]">Qty</label>
						<label class="control-label col-xs-1 mobile-hide center" for=""></label>
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
						<label class="control-label col-xs-4 mobile-hide center" for="misc[]">Item</label>
						<label class="control-label col-xs-5 mobile-hide center" for="misc_notes[]">Notes</label>
						<label class="control-label col-xs-1 mobile-hide center" for="misc_weight[]">Weight</label>
						<label class="control-label col-xs-1 mobile-hide center" for="misc_quantity[]">Qty</label>
						<label class="control-label col-xs-1 mobile-hide center" for=""></label>
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
								$base = 100 + 20 * $user['strength'];
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
							<textarea class="form-control track-changes" rows="6" name="background" id="background" maxlength="2000" data-id="<?php echo htmlspecialchars($user['id']) ?>" data-table="user"><?php echo htmlspecialchars($user['background']) ?></textarea>
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
          <h4 class="modal-title" id="magic_modal_title">New Magic School</h4>
        </div>
        <div class="modal-body">
        	<label class="control-label">Choose a Talent from your new school</label>
        	<select class="form-control" id="magic_talents">
        	</select>
        	<select class="form-control elemental_select hidden">
        		<option value="Fire">Fire</option>
        		<option value="Ice">Ice</option>
        		<option value="Electricity">Electricity</option>
        	</select>
        	<select class="form-control elementalist_select hidden">
        		<option value="Earth">Earth</option>
        		<option value="Water">Water</option>
        		<option value="Air">Air</option>
        	</select>
        	<select class="form-control superhuman_select hidden">
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
						$next_level = 0;
						foreach ($levels as $lvl) {
							if ($user['xp'] < $lvl) {
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
        	<textarea class="form-control" id="note_note" rows="10" maxlength="2000"></textarea>
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
        	<label class="control-label" id="select_feat_type_label">Type</label>
        	<select class="form-control" id="select_feat_type">
        		<option id="standard_option" value="feat_name">Standard Talent</option>
        		<option id="race_trait_option" value="race_trait_name" hidden>Race Trait</option>
        		<!--  hide unless user has magic -->
        		<option id="magic_option" value="magic_talent_name">Magical Talent</option>
        		<option value="social_background_name">Social Background</option>
        		<option value="social_trait_name">Social Trait</option>
        		<option value="physical_trait_pos_name">Physical Trait (Positive)</option>
        		<option value="physical_trait_neg_name">Physical Trait (Negative)</option>
        		<option value="morale_trait_name">Morale Trait</option>
        		<option value="compelling_action_name">Compelling Action</option>
        		<option value="profession_name">Profession</option>
        	</select>
        	<label class="control-label">Name</label>
        	<input class="form-control clearable feat-type" type="text" id="feat_name">
        	<input class="form-control clearable feat-type hidden" type="text" id="magic_talent_name">
        	<select class="form-control feat-type feat-select hidden" id="race_trait_name">
        		<?php
        			foreach ($race_traits as $trait) {
        				echo '<option value="'.$trait['trait'].'"">'.$trait['trait'].'</option>';
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="social_background_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'social_background') {
        					echo '<option value="'.$feat['name'].'"">'.$feat['name'].'</option>';
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="social_trait_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'social_trait') {
        					echo '<option value="'.$feat['name'].'"">'.$feat['name'].'</option>';
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="physical_trait_pos_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'physical_trait' && $feat['cost'] > 0) {
        					echo '<option value="'.$feat['name'].'"">'.$feat['name'].'</option>';
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="physical_trait_neg_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'physical_trait' && $feat['cost'] < 0) {
        					echo '<option value="'.$feat['name'].'"">'.$feat['name'].'</option>';
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="compelling_action_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'compelling_action') {
        					echo '<option value="'.$feat['name'].'"">'.$feat['name'].'</option>';
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="profession_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'profession') {
        					echo '<option value="'.$feat['name'].'"">'.$feat['name'].'</option>';
        				}
        			}
        		?>
        	</select>
        	<select class="form-control feat-type feat-select hidden" id="morale_trait_name">
        		<option></option>
        		<?php
        			foreach ($feat_list as $feat) {
        				if ($feat['type'] == 'morale_trait') {
        					echo '<option value="'.$feat['name'].'"">'.$feat['name'].'</option>';
        				}
        			}
        		?>
        	</select>

        	<select class="form-control elemental_select hidden">
        		<option value="Fire">Fire</option>
        		<option value="Ice">Ice</option>
        		<option value="Electricity">Electricity</option>
        	</select>

        	<select class="form-control elementalist_select hidden">
        		<option value="Earth">Earth</option>
        		<option value="Water">Water</option>
        		<option value="Air">Air</option>
        	</select>

        	<select class="form-control superhuman_select hidden">
        		<option value="Power/Dexterity">Power (Strength/Fortitude) & Dexterity (Speed/Agility)</option>
        		<option value="Power/Precision">Power (Strength/Fortitude) & Perception (Precision/Awareness)</option>
        		<option value="Dexterity/Precision">Dexterity (Speed/Agility) & Perception (Precision/Awareness)</option>
        	</select>

        	<div class="row shapeshifter_select hidden">
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
        	<input type="hidden" id="feat_name_val">
        	<input type="hidden" id="feat_id">
        	<input type="hidden" id="feat_type">
        	<input type="hidden" id="feat_cost">
        	<input type="hidden" id="user_feat_id">
        	<div class="button-bar">
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newFeat()" id="feat_submit_btn">Ok</button>
	        	<button type="button" class="btn btn-primary hidden" data-dismiss="modal" onclick="updateFeat()" id="feat_update_btn">Ok</button>
	        	<button type="button" class="btn btn-primary" data-dismiss="modal" id="feat_cancel_btn">Cancel</button>
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
		        	<input class="form-check-input" type="radio" name="skill_type" id="skill" value="skill">
		        	<label class="form-check-label" for="skill">Unique Skill (2 attribute pts)</label>
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
        			<input class="form-control skill-name clearable" type="text" id="focus_name2">
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
        	<input class="form-control" type="number" min="0" id="weapon_quantity">
        	<label class="control-label">Damage*</label>
        	<input class="form-control" type="number" min="0" id="weapon_damage">
        	<label class="control-label">Max Damage</label>
        	<input class="form-control" type="number" min="0" id="weapon_max_damage">
        	<label class="control-label">Range</label>
        	<input class="form-control" type="number" min="0" id="weapon_range_">
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

	<!-- save character - bot test -->
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
		        <button type="button" class="btn btn-primary" id="password_btn_2" data-dismiss="modal" onclick="submitUser()">Ok</button>
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
					<p>When creating a new character you will start with a default of 12 Attribute Points. This value is 'unlocked' during character creation, and can be adjusted based on any modifiers. Your Attribute Points can be allocated by selecting the <i>Allocate Attribute Points</i> option from the nav menu. Points will be automatically adjusted as you increase or decrease Attributes, and as Talents and Trainings are added. Your Attributes and Talents are also 'unlocked' during character creation, allowing you to add additional starting Talents/Traits and Skills as needed. In order to save a newly created character, you will need to know the 'secret code.' If you don't know what it is, ask your GM. If they don't know it...find a new GM?</p><br>
					<h4>Adding XP & Allocating Attribute Points</h4>
					<p>Once your character has begun collecting XP, all of your Attribute Values, Skills, and Talents will be locked. The only way to modify your Attributes is by accruing and allocating Attribute Points. As you add XP, your level will be automatically adjusted, and as you gain levels, Attribute Points will automatically be added. These Attribute Points can then be allocated via the <i>Allocate Attribute Points</i> option from the nav menu. Attributes can only be raised by one point per allocation, and only one unique Skill or Talent, as well as one Focus or Training, can be added per allocation. Attribute Points will be automatically deducted. If additional modifications need to be made to Attributes, Skills or Talents, this will need to be done through the <i>GM Edit Mode</i>.</p><br>
					<h4>GM Edit Mode</h4>
					<p>The GM can unlock and edit Attribute Points, XP, Attribute Values, Skills, and Talents. The GM can use this edit mode to make changes to any of the characters at any time. 
					<h4>Campaign Admin</h4>
					<p>The campaign admin page can only be accessed by the campaign admin (the creator of the campaign). The admin page provides a quick view of all characters and certain attributes. It is also where the GM is able to award XP to characters.
					<br><br><span class="narrow"><strong>NOTE:</strong> XP bonuses from Motivator arguments are automatically awarded when characters increase these values on their character sheets.</span><br>
					From the campaign admin you can also view all available Talents, Traits, etc, and you can adjust which Talents and Traits are available to your campaign.</p>
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
	<script async src="https://www.google.com/recaptcha/api.js?render=6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi"></script>
	<script src="/assets/jquery/jquery-3.5.1.min.js"></script>
	<script src="/assets/bootstrap/js/bootstrap.min.js"></script>
	<script src="/assets/jquery/jquery-ui-1.12.1.min.js"></script>
	<script src="/assets/font-awesome/font-awesome-6.1.1-all.min.js"></script>
	<?php echo $keys['scripts'] ?>
	<script type="text/javascript">

		// get all database values
		campaign = <?php echo json_encode(isset($campaign) ? $campaign : []); ?>;
		user = <?php echo json_encode($user); ?>;
		console.log(user);
		feat_list = <?php echo json_encode($feat_list); ?>;
		feat_reqs = <?php echo json_encode($feat_reqs); ?>;
		let races = <?php echo json_encode($races); ?>;
		let talents = <?php echo json_encode($talents); ?>;
		// talents with no ID are either not active for campaign or not in DB
		let no_id = <?php echo json_encode($no_id); ?>;
		console.log(no_id);
		let race_traits = <?php echo json_encode($race_traits); ?>;
		let counts = <?php echo json_encode($counts); ?>;
		
		xp_awards = <?php echo json_encode(isset($awards) ? $awards : []); ?>;
		let user_feats = <?php echo json_encode($feats); ?>;
		let user_trainings = <?php echo json_encode($trainings); ?>;
		for (var i in user_trainings) {
			let training = new UserTraining(user_trainings[i]);
			userTrainings.push(training);
		}

		let trainingAutocompletes = <?php echo json_encode($training_autocomplete); ?>;

		// disable all inputs if user can't edit
		if ($("#can_edit").val() == 0 && !user['is_new']) {
			$("input").attr("readonly", true);
			$("select").attr("disabled", true);
			$("textarea").attr("readonly", true);
			$(".glyphicon-plus-sign").attr("data-toggle", null);
			$("#user_select").attr("disabled", false);
		}

		// set user motivators
		let user_motivators = <?php echo json_encode($user_motivators); ?>;
		for (var i in user_motivators) {
			let motivator = new UserMotivator(user_motivators[i]);
			userMotivators.push(motivator);
		}
		user['motivators'] = userMotivators;
		setAttributes(user);

		// get feat list and requirements
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
		// get feat description from feat_or_trait table if feat_id is present
		for (var i in user_feats) {
			// store name and description values (may be custom)
			user_feats[i]['display_name'] = user_feats[i]['name'];
			var feat_id_null = true;
			if (user_feats[i]['feat_id'] != null && user_feats[i]['feat_id'] != 0) {
				feat_id_null = false;
				// get name, description, type, and cost from feat list
				for (var j in feat_list) {
					if (feat_list[j]['id'] == user_feats[i]['feat_id']) {
						user_feats[i]['name'] = feat_list[j]['name'];
						if (user_feats[i]['description'] == "") {
							user_feats[i]['description'] = feat_list[j]['description'];
						}
						user_feats[i]['type'] = feat_list[j]['type'];
						user_feats[i]['cost'] = feat_list[j]['cost'];
					}
				}
			}
			let talent = new UserTalent(user_feats[i]);
			userTalents.push(talent);
			addFeatElements(talent);
		}
		user['talents'] = userTalents;

		// set feat list
		setFeatList();
		
		// character creation mode
		if (user['is_new'] || xp_awards.length == 0) {
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
		for (var i in userTrainings) {
			addTrainingElements(userTrainings[i], null);
		}
		user['trainings'] = userTrainings;

		// check for user weapons
		loadingItems = true;
		var select_id = 1;
		let user_weapons = <?php echo json_encode($weapons); ?>;
		for (var i in user_weapons) {
			let weapon = new UserWeapon(user_weapons[i]);
			userWeapons.push(weapon);
			addWeaponElements(weapon);

			// select from dropdown if weapon is equipped
			for (var j = 0; j < weapon.equipped; j++) {
				if (j < weapon.quantity) {
					$("#weapon_select_"+select_id).val(weapon.name);
					selectWeapon(select_id, false);
					weapon.equipped_index.push(select_id++);
				}
			}

		}
		user['weapons'] = userWeapons;

		// check for user protections
		let user_protections = <?php echo json_encode($protections); ?>;
		for (var i in user_protections) {
			let protection = new UserProtection(user_protections[i]);
			userProtections.push(protection);
			addProtectionElements(protection, false);
		}
		user['protections'] = userProtections;
		// update toughness for equipped protections
		setToughness();

		// check for user healings
		let user_healings = <?php echo json_encode($healings); ?>;
		for (var i in user_healings) {
			let healing = new UserHealing(user_healings[i]);
			userHealings.push(healing);
			addHealingElements(healing);
		}
		user['healings'] = userHealings;

		// check for user misc items
		let user_misc = <?php echo json_encode($misc); ?>;
		for (var i in user_misc) {
			let misc = new UserMisc(user_misc[i]);
			userMisc.push(misc);
			addMiscElements(misc);
		}
		user['misc'] = userMisc;

		// show encumbered alert after all items have been loaded
		loadingItems = false;
		updateTotalWeight(true);

		// check for user notes
		let user_notes = <?php echo json_encode($notes); ?>;
		for (var i in user_notes) {
			let note = new UserNote(user_notes[i]);
			userNotes.push(note);
			addNoteElements(note);
		}
		user['notes'] = userNotes;

		// generate leveling table
		let levels = [];
		var xp_total = 0;
		levels.push(xp_total);
		for (var i = 1; i < 25; i++) {
			xp_total +=  20 * i;
			levels.push(xp_total);
		}

	</script>

</body>
</html>