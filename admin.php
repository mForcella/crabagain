<?php

	session_start();

	// make sure we are logged in - check for existing session
	if (!isset($_SESSION['login_id'])) {
    	header('Location: /login.php');
	}
	$login_id = $_SESSION['login_id'];

	// establish database connection
	include_once('config/db_config.php');
	include_once('config/keys.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// make sure campaign ID is set
	if (!isset($_GET["campaign"])) {
		// redirect to campaign select page
		header('Location: /select_campaign.php');
	}
	$campaign;
	$campaign_id = $_GET["campaign"];
	$sql = "SELECT * FROM campaign WHERE id = $campaign_id";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$campaign = $row;
		}
	}

	// make sure user has admin privileges for the campaign
	$sql = "SELECT campaign_role FROM login_campaign WHERE login_id = $login_id AND campaign_id = $campaign_id";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			if ($row['campaign_role'] != 1) {
    			header('Location: /login.php');
			}
		}
	}

	// get characters
	$users = [];
	$sql = "SELECT * FROM user WHERE campaign_id = ".$_GET["campaign"];
	$result_u = $db->query($sql);
	if ($result_u) {
		while($row_u = $result_u->fetch_assoc()) {
			$user = $row_u;

			// get xp awards
			$xp_award = 0;
			$sql = "SELECT xp_award FROM user_xp_award WHERE user_id = ".$user["id"]." AND awarded IS NULL";
			$result_xp = $db->query($sql);
			if ($result_xp) {
				while($row_xp = $result_xp->fetch_assoc()) {
					$xp_award += $row_xp['xp_award'];
				}
			}
			$user['xp_award'] = $xp_award;

			// get toughness bonus from equipped protections
			$toughness_bonus = 0;
			$sql = "SELECT bonus FROM user_protection WHERE user_id = ".$user["id"]." AND equipped = 1";
			$result_p = $db->query($sql);
			if ($result_p) {
				while($row_p = $result_p->fetch_assoc()) {
					$toughness_bonus += $row_p['bonus'];
				}
			}
			$user['toughness_bonus'] = $toughness_bonus;

			// get defend bonus from equipped melee weapons
			$defend_bonus = 0;
			$sql = "SELECT defend FROM user_weapon WHERE user_id = ".$user["id"]." AND equipped = 1";
			$result_w = $db->query($sql);
			if ($result_w) {
				while($row_w = $result_w->fetch_assoc()) {
					$defend_bonus += $row_w['defend'];
				}
			}
			$user['defend_bonus'] = $defend_bonus;

			// check for user feat Lightning Reflexes
			$sql = "SELECT count(*) as count FROM user_feat WHERE user_id = ".$user["id"]." AND LOWER(name) = 'lightning reflexes'";
			$result = $db->query($sql);
			$user['dodge_mod'] = 0;
			if ($result) {
				while($row = $result->fetch_assoc()) {
					if ($row['count'] > 0) {
						// update dodge with speed bonus
						$user['dodge_mod'] += floor($user['speed']/2);
					}
				}
			}

			// check for user feat Relentless Defense
			$sql = "SELECT count(*) as count FROM user_feat WHERE user_id = ".$user["id"]." AND LOWER(name) = 'relentless defense'";
			$result = $db->query($sql);
			$user['defend_mod'] = 0;
			if ($result) {
				while($row = $result->fetch_assoc()) {
					if ($row['count'] > 0) {
						// update defend with speed bonus
						$user['defend_mod'] += floor($user['speed']/2);
						// update dodge with brawl bonus
						$sql = "SELECT value FROM user_training WHERE user_id = ".$user["id"]." AND LOWER(name) = 'brawl'";
						$result = $db->query($sql);
						if ($result) {
							while($row = $result->fetch_assoc()) {
								$user['dodge_mod'] += floor($row['value']/2);
							}
						}
					}
				}
			}

			// get primary / secondary initiative
			$user['primary'] = $user['awareness'] >= 0 ? 6 - floor($user['awareness']/2) : 6 - ceil($user['awareness']/3);
			$user['secondary'] = $user['speed'] >= 0 ? 6 - floor($user['speed']/2) : 6 - ceil($user['speed']/3);

			// if character has 'quick and the dead' as feat, primary and secondary can be switched
			$sql = "SELECT count(*) as count FROM user_feat WHERE user_id = ".$user["id"]." AND LOWER(name) LIKE '%quick %dead'";
			$result = $db->query($sql);
			if ($result) {
				while($row = $result->fetch_assoc()) {
					if ($row['count'] > 0 && $user['secondary'] < $user['primary'] && $user['awareness'] >= 0) {
						$hold = $user['primary'];
						$user['primary'] = $user['secondary'];
						$user['secondary'] = $hold;
					}
				}
			}

			array_push($users, $user);
		}
	}

	// sort users by initiative
	$primary = array_column($users, 'primary');
	$secondary = array_column($users, 'secondary');
	array_multisort($primary, SORT_ASC, $secondary, SORT_ASC, $users);
  
	// get feat list
	$feats = [];
	$sql = "SELECT * FROM feat_or_trait WHERE id != 0 ORDER BY name";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($feats, $row);
		}
	}

	// get active counts for each feat type
	$total_count = 0;
	$counts = [];
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'physical_trait' AND cost > 0";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['physical_pos_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'physical_trait' AND cost < 0";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['physical_neg_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'social_trait'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['social_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'morale_trait'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['morale_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'compelling_action'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['compelling_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'profession'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['profession_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = ".$_GET["campaign"]." AND type = 'social_background'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['social_background_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}

	// get feat active status
	$campaign_feats = [];
	$sql = "SELECT * FROM campaign_feat WHERE campaign_id = ".$_GET["campaign"];
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($campaign_feats, $row);
		}
	}

	// get feat requirements
	$feat_req_sets = [];
	$sql = "SELECT id, feat_id FROM feat_or_trait_req_set";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($feat_req_sets, $row);
		}
	}
	$feat_reqs = [];
	$sql = "SELECT feat_id, req_set_id, type, value FROM feat_or_trait_req_set JOIN feat_or_trait_req ON feat_or_trait_req_set.id = feat_or_trait_req.req_set_id";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($feat_reqs, $row);
		}
	}

	$feat_list = [];
	foreach($feats as $feat) {
		foreach ($campaign_feats as $campaign_feat) {
			if ($campaign_feat['feat_id'] == $feat['id']) {
				$feat['active'] = true;
			}
		}
		if ($feat['type'] == 'feat' || $feat['type'] == 'magic_talent') {
			$feat['requirements'] = [];
			foreach($feat_req_sets as $req_set) {
				if ($feat['id'] == $req_set['feat_id']) {
					$feat['requirements'][$req_set['id']] = [];
				}
			}
			foreach($feat_reqs as $req) {
				if ($feat['id'] == $req['feat_id']) {
					$req_vals = [
						$req['type'] => $req['value']
					];
					array_push($feat['requirements'][$req['req_set_id']], $req_vals);
				}
			}
			array_push($feat_list, $feat);
		} else {
			array_push($feat_list, $feat);
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
	<title><?php echo $campaign['name'] ?> : Admin</title>
	<link rel="icon" type="image/png" href="/assets/image/favicon-pentacle-black.ico"/>

	<!-- Bootstrap -->
	<link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
	<!-- jQuery UI -->
	<link rel="stylesheet" type="text/css" href="/assets/jquery/jquery-ui-1.12.1.min.css">
	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Alegreya:ital,wght@0,400;1,400;1,600&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
	<!-- Custom Styles -->
	<link rel="stylesheet" type="text/css" href="<?php echo $keys['styles'] ?>">
	<link rel="stylesheet" type="text/css" href="/assets/toggle-switchy.css">
</head>

<style type="text/css">
	.table-heading {
		text-align: center;
		font-weight: bold;
		margin-top: 30px;
		font-size: 1.5em;
	}
	.highlight {
		text-shadow: 0px 0px 3px #404040;
	}
	.section-select {
		width: 300px;
		margin: 50px auto;
	}
	.panel {
		border: 1px solid black;
		display: table;
		margin: 0 auto;
	}
	.table {
		background-color: #e6e6e6;
	}
	.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
		border: none;
		padding: 5px 15px;
	}
	th {
		white-space:nowrap;
	}
	.container, .footer {
		min-width: 750px;
	}
	.footer {
		margin-top: 30px;
	}
	@media (min-width: 768px) {
		.container {
			width: auto;
		}
	}
	.glyphicon-plus-sign {
		width: 100%;
		text-align: center;
		margin-bottom: 20px;
	}
	#base_award {
		display: inline;
		width: 100px;
		margin-bottom: 20px;
	}
	.base-award-label {
		font-size: 16px;
	}
	#xp_modal {
		width: 750px;
		position: absolute;
	}
	#xp_modal .modal-content {
		background-color: #cccccc;
	}
	#xp_modal .note {
		/*max-width: 500px;*/
		margin: 0 auto;
		margin-top: 15px;
		cursor: default;
		white-space: normal;
		width: 100%;
	}
	#xp_modal .toggle-switchy {
		transform: scale(0.7);
	}
	#xp_modal input {
		padding-left: 12px !important;
	}
	@media (pointer: coarse) and (hover: none) {
		#xp_modal input {
			padding-left: 0 !important;
		}
	}
	.small {
		font-size: 75%;
	}
	th input {
		margin-top: -3px !important;
	}
	.modal label {
		margin-top: 15px;
	}
	.modal-open {
		overflow: visible;
	}
	.modal {
		margin: 0 auto;
	}
	@media (min-width: 768px) {
		.modal-dialog {
			width: 100%;
		}
	}
	#feat_description_val {
		height: 100px;
	}
	#feat_pos_state_val, #feat_neg_state_val {
		height: 75px;
	}
	.feat-requirement {
		display: block;
	}
	.feat-requirement p {
		display: inline;
		margin-left: 5px;
	}
	.feat-requirement p:before {
		content: "•";
	}
	td {
		border-top: 1px solid black !important;
	}
	#scroll_top {
		position: fixed;
		bottom: 25px;
		right: 25px;
		font-size: 30px;
		padding: 3px 10px 0 7px;
		border-radius: 10px;
		border: 1px solid black;
		transition-duration: 500ms;
		background-color: #cccccc;
		z-index: 999;
	}
	#scroll_top.no-vis {
		bottom: -50px;
	}
	.toggle {
		border: 1px solid black;
		border-radius: 10px !important;
	}
	.switch {
		border-radius: 10px !important;
	}
	.toggle-switchy {
		margin-left: 20px;
		margin-bottom: 5px;
	}
	.title {
		text-align: center;
		margin-top: 50px;
		margin-bottom: 10px;
	}
	.title h4 {
		display: inline;
	}
	table.center th {
		text-align: center;
		padding-top: 10px !important;
	}
	table.center td input {
/*		margin-top: 7px !important;*/
	}
	a {
		color: black !important;
		text-decoration: none !important;
	}
	#home_wrapper {
		position: absolute;
		top:-5px;
		left: -5px;
		height: 75px;
		background-color: #e6e6e6;
		border: 1px solid black;
		border-radius: 5px;
		padding-top: 20px;
	}
	#home_wrapper .fa, #home_wrapper .glyphicon {
		font-size: 30px;
	}
	#home_wrapper p {
		font-weight: bold;
		text-transform: uppercase;
	}
	.fa-fort-awesome {
		padding-bottom: 12px;
	}
	.btn-wrapper {
		text-align: center;
	}
	#xp_btn {
		margin-bottom: 15px;
		margin-top: 20px;
		font-size: 20px;
		font-weight: bold;
		padding: 5px 10px;
	}
	#xp_btn .fa-solid {
		font-size: 16px;
		transform: translateY(-2px);
	}
	.xp-label {
		margin-left: 10px;
		max-width: 190px;
		cursor: pointer;
	}
	.modal label {
		margin-top: 0;
	}
	.xp-input {
		margin-top: 12px;
		margin-bottom: 20px;
	}
	.xp-btn {
		margin-top: 12px;
		margin-left: 10px;
	}
	.short-input {
		width: 50px;
	}
	.table input {
		width: 50px;
		margin: 0 auto;
		display: inline;
	}
	.name-row {
		max-width: 164px;
	}
	.mobile-name-row {
		display: none;
		border-bottom: none;
	}
	.mobile-name-row, .mobile-name-row strong {
		text-decoration: underline;
	}
	td .min, td.min {
		min-width: 150px;
	}
	.pointer {
		cursor: pointer;
	}
	.btn.btn-primary.btn-danger {
		background-color: red !important;
	}

	@media (max-width: 868px) {
		.name-row, .select-row {
			display: none;
		}
		.mobile-name-row {
			display: table-row;
		}
		.table-row td {
			border-top: none !important;
		}
	}
	@media (max-width: 868px) {
		#xp_modal {
			height: 200vh;
		}
		#xp_modal .modal-dialog {
			width: 525px;
			margin: 30px auto;
		}
		#xp_modal input[type=checkbox] {
		    transform: scale(1.3);
		    -ms-transform: scale(1.3);
		    -webkit-transform: scale(1.3);
		}
		#xp_modal .note {
			/*max-width: 300px;*/
		}
	}
	/* extra small for mobile? */
	@media (max-width: 767px) {
		#xp_modal .table>tbody>tr>th, #xp_modal .table>tbody>tr>td {
/*			padding: 5px;*/
/*			font-size: 13px;*/
		}
		th input {
/*			margin-top: -6px !important;*/
		}
		#xp_modal .modal-dialog {
/*			width: 465px;*/
		}
		#xp_modal .small {
/*			font-size: 63%;*/
		}
	}
</style>

<body>
	<div class="container">

		<input type="hidden" id="admin_password" value="<?php echo isset($_GET['auth']) ? $_GET['auth'] : '' ?>">

		<!-- button - scroll to top of page -->
		<button id="scroll_top" class="no-vis"><span class="glyphicon glyphicon-arrow-up" onclick="scrollToTop()"></span></button>

		<!-- home button -->
		<div id="home_wrapper">
			<div class="row">
				<div class="col-xs-6 center">
					<a href="<?php echo '/?campaign='.$campaign['id'] ?>"><i class="glyphicon fa fa-brands fa-fort-awesome"></i><p>Home</p></a>
				</div>
				<div class="col-xs-6 center pointer" data-toggle="modal" data-target="#welcome_modal">
					<span class="glyphicon glyphicon-info-sign" ></span><p>Guide</p>
				</div>
				<!-- <div class="col-xs-4 center pointer" data-toggle="modal" data-target="#save_modal">
					<span class="glyphicon glyphicon-floppy-disk"></span><p>Save</p>
				</div> -->
			</div>
		</div>

		<select class="form-control section-select" id="section_links">
			<option value="">Standard Talents</option>
			<option value="#magical_talents">Magical Talents</option>
			<option value="#section_physical_trait_pos">Physical Traits (Positive)</option>
			<option value="#section_physical_trait_neg">Physical Traits (Negative)</option>
			<option value="#section_social_trait">Social Traits</option>
			<option value="#section_morale_trait">Morale Traits</option>
			<option value="#section_compelling_action">Compelling Actions</option>
			<option value="#section_profession">Professions</option>
		</select>

		<!-- character stats overview -->
		<div class="panel panel-default" <?php if(count($users) == 0) { echo 'hidden'; } ?>>
			<table class="table user-table center">
				<tr>
					<th class="name-row"></th>
					<th>XP</th>
					<th>Lvl</th>
					<th>Resil</th>
					<th>Wnds</th>
					<th>Init</th>
					<th>Tgh</th>
					<th>Dfd</th>
					<th>Ddg</th>
					<th>Awr</th>
					<th>Vit</th>
				</tr>
				<?php
					foreach($users as $user) {

						// get level
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

						// get resilience
						$resilience = $user['fortitude'] >= 0 ? 
								3 + floor($user['fortitude']/2) :
								3 + ceil($user['fortitude']/3);

						// get damage and wounds
						$damage = $user['damage'];
						$wounds = 0;
						while ($damage >= $resilience) {
							$wounds += 1;
							$damage -= $resilience;
						}

						// get size modifier
						$size_modifier = $user['size'] == "Small" ? 2 : ($user['size'] == "Large" ? -2 : 0);

						// get dodge
						$dodge = $user['agility'] >= 0 ?
								floor($user['agility']/2) :
								(ceil($user['agility']/3) == 0 ? 0 : ceil($user['agility']/3));
						$dodge += $size_modifier;
						$dodge += $user['dodge_mod'];

						// get toughness
						$toughness = $user['strength'] >= 0 ?
								floor($user['strength']/2) :
								(ceil($user['strength']/3) == 0 ? 0 : ceil($user['strength']/3));

						// get defend
						$defend = isset($user) ? 10 + $user['agility'] : 10;
						$defend += $size_modifier;
						$defend += $user['defend_mod'];

						echo 
						"<tr class='mobile-name-row'>
							<td colspan='10'><a href='/?campaign=".$campaign['id']."&user=".$user['id']."'><strong>".$user['character_name']."</strong></a></td>
						</tr>
						<tr class='table-row'>
							<td class='name-row'><a href='/?campaign=".$campaign['id']."&user=".$user['id']."'><strong>".$user['character_name']."</strong></a></td>
							<td id='xp_".$user['id']."'>"
							.$user['xp']
							.($user['xp_award'] == 0 ? 
								'' 
								: ($user['xp_award'] > 0 ? 
									' (+'.$user['xp_award'].')' 
									: ' ('.$user['xp_award'].')'))
							."</td>
							<td id='level_".$user['id']."'>".$level."</td>
							<td><input class='short-input form-control' id='damage_".$user['id']."' min='0' type='number' value='".$damage."'> / ".$resilience."</td>
							<td><input class='short-input form-control' id='wounds_".$user['id']."' max='3' min='0' type='number' value='".$wounds."'> / 3</td>
							<td>".$user['primary']."/".$user['secondary']."</td>
							<td>".$toughness.($user['toughness_bonus'] > 0 ? ' (+'.$user['toughness_bonus'].')' : '')."</td>
							<td>".$defend.($user['defend_bonus'] > 0 ? ' (+'.$user['defend_bonus'].')' : '')."</td>
							<td>".$dodge."</td>
							<td>".$user['awareness']."</td>
							<td>".$user['vitality']."</td>
						</tr>";
					}
				?>
			</table>
		</div>

		<!-- open xp modal -->
		<div class="btn-wrapper" <?php if(count($users) == 0) { echo 'hidden'; } ?>>
			<button id="xp_btn" data-toggle="modal" data-target="#xp_modal"><i class="fa-solid fa-award"></i> Award XP</button>
		</div>

		<form id="campaign_form">
			<input type="hidden" id="campaign_id" name="campaign_id" value="<?php echo $campaign['id'] ?>">
			<h4 class="table-heading" id="section_feat">Standard Talents</h4>
			<!-- <span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('feat')"></span> -->
			<div class="panel panel-default">
				<table class="table" id="feat_table">
					<tr>
						<th>Enabled</th>
						<th>Name</th>
						<th>Description</th>
						<th>Requirements</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($feat_list as $feat) {
							$reqs = "";
							if ($feat['type'] == 'feat') {
								// build requirement string
								foreach($feat['requirements'] as $req_set) {
									for($i = 0; $i < count($req_set); $i++) {
										foreach($req_set[$i] as $key => $value) {
											$reqs .= $i > 0 ? "OR " : "&#8226;";
											if ($key == "character_creation") {
												$reqs .= "Character Creation Only"."<br>";
											} else {
												$reqs .= str_replace("_", "", ucfirst($key)).": ".$value."<br>";
											}
										}
									}
								}
								echo 
								"<tr class='table-row' id='row_".$feat['id']."'>
									<td class='center'><input type='checkbox' ".(isset($feat['active']) || $total_count == 0 ? 'checked' : '')." name='feat_status[]' value='".$feat['id']."'></td>
									<td>".$feat['name']."</td>
									<td>".$feat['description']."</td>
									<td>".$reqs."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
							}
						}
					?>
				</table>
			</div>

			<div class="title">
				<h4 class="table-heading" id="magical_talents">Magical Talents</h4>
				<label class="toggle-switchy" for="magical_talents_toggle" data-size="sm" data-text="false">
					<input checked type="checkbox" id="magical_talents_toggle" checked onclick="enable(this, 'magical-talent-check')">
					<span class="toggle">
						<span class="switch"></span>
					</span>
				</label>
			</div>
			<!-- <span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('magical_talent')"></span> -->
			<div class="panel panel-default">
				<table class="table" id="physical_trait_pos_table">
					<tr>
						<th>Enabled <input type='checkbox' class="magical-talent-check" checked onclick="checkAll(this, 'magical-talent-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<th>Requirements</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($feat_list as $feat) {
							$reqs = "";
							if ($feat['type'] == 'magic_talent') {
								// build requirement string
								foreach($feat['requirements'] as $req_set) {
									for($i = 0; $i < count($req_set); $i++) {
										foreach($req_set[$i] as $key => $value) {
											$reqs .= $i > 0 ? "OR " : "&#8226;";
											if ($key == "character_creation") {
												$reqs .= "Character Creation Only"."<br>";
											} else {
												$reqs .= str_replace("_", "", ucfirst($key)).": ".$value."<br>";
											}
										}
									}
								}
								echo 
								"<tr class='table-row' id='row_".$feat['id']."'>
									<td class='center'><input type='checkbox' ".(isset($feat['active']) || $total_count == 0 ? 'checked' : '')." name='feat_status[]' value='".$feat['id']."'></td>
									<td>".$feat['name']."</td>
									<td>".$feat['description']."</td>
									<td>".$reqs."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
							}
						}
					?>
				</table>
			</div>

			<div class="title">
				<h4 class="table-heading" id="section_physical_trait_pos">Physical Traits (Positive)</h4>
				<label class="toggle-switchy" for="physical_trait_pos_toggle" data-size="sm" data-text="false">
					<input checked type="checkbox" id="physical_trait_pos_toggle" checked onclick="enable(this, 'physical-trait-pos-check')">
					<span class="toggle">
						<span class="switch"></span>
					</span>
				</label>
			</div>
			<!-- <span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('physical_trait_pos')"></span> -->
			<div class="panel panel-default">
				<table class="table" id="physical_trait_pos_table">
					<tr>
						<th>Enabled <input type='checkbox' class="physical-trait-pos-check" checked onclick="checkAll(this, 'physical-trait-pos-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<th class="center">Cost</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($feat_list as $feat) {
							if ($feat['type'] == 'physical_trait' && $feat['cost'] > 0) {
								echo 
								"<tr class='table-row' id='row_".$feat['id']."'>
									<td class='center'><input class='physical-trait-pos-check' type='checkbox' ".(isset($feat['active']) || $counts['physical_pos_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$feat['id']."'></td>
									<td>".$feat['name']."</td>
									<td>".$feat['description']."</td>
									<td class='center'>".$feat['cost']."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
							}
						}
					?>
				</table>
			</div>

			<div class="title">
				<h4 class="table-heading" id="section_physical_trait_neg">Physical Traits (Negative)</h4>
				<label class="toggle-switchy" for="physical_trait_neg_toggle" data-size="sm" data-text="false">
					<input checked type="checkbox" id="physical_trait_neg_toggle" checked onclick="enable(this, 'physical-trait-neg-check')">
					<span class="toggle">
						<span class="switch"></span>
					</span>
				</label>
			</div>
			<!-- <span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('physical_trait_neg')"></span> -->
			<div class="panel panel-default">
				<table class="table" id="physical_trait_neg_table">
					<tr>
						<th>Enabled <input type='checkbox' class="physical-trait-neg-check" checked onclick="checkAll(this, 'physical-trait-neg-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<th class="center">Bonus</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($feat_list as $feat) {
							if ($feat['type'] == 'physical_trait' && $feat['cost'] < 0) {
								echo 
								"<tr class='table-row' id='row_".$feat['id']."'>
									<td class='center'><input class='physical-trait-neg-check' type='checkbox' ".(isset($feat['active']) || $counts['physical_neg_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$feat['id']."'></td>
									<td>".$feat['name']."</td>
									<td>".$feat['description']."</td>
									<td class='center'>".(intval($feat['cost'])*-1)."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
							}
						}
					?>
				</table>
			</div>

			<div class="title">
				<h4 class="table-heading" id="section_social_trait">Social Traits</h4>
				<label class="toggle-switchy" for="social_trait_toggle" data-size="sm" data-text="false">
					<input checked type="checkbox" id="social_trait_toggle" onclick="enable(this, 'social-trait-check')">
					<span class="toggle">
						<span class="switch"></span>
					</span>
				</label>
			</div>
			<!-- <span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('social_trait')"></span> -->
			<div class="panel panel-default">
				<table class="table" id="social_trait_table">
					<tr>
						<th>Enabled <input type='checkbox' class="social-trait-check" checked onclick="checkAll(this, 'social-trait-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($feat_list as $feat) {
							if ($feat['type'] == 'social_trait') {
								echo 
								"<tr class='table-row' id='row_".$feat['id']."'>
									<td class='center'><input class='social-trait-check' type='checkbox' ".(isset($feat['active']) || $counts['social_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$feat['id']."'></td>
									<td>".$feat['name']."</td>
									<td>".$feat['description']."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
							}
						}
					?>
				</table>
			</div>

			<div class="title">
				<h4 class="table-heading" id="section_morale_trait">Morale Traits</h4>
				<label class="toggle-switchy" for="morale_trait_toggle" data-size="sm" data-text="false">
					<input checked type="checkbox" id="morale_trait_toggle" checked onclick="enable(this, 'morale-trait-check')">
					<span class="toggle">
						<span class="switch"></span>
					</span>
				</label>
			</div>
			<!-- <span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('morale_trait')"></span> -->
			<div class="panel panel-default">
				<table class="table" id="morale_trait_table">
					<tr>
						<th>Enabled <input type='checkbox' class="morale-trait-check" checked onclick="checkAll(this, 'morale-trait-check')"></th>
						<th>Name</th>
						<th>Positive State</th>
						<th>Negative State</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($feat_list as $feat) {
							if ($feat['type'] == 'morale_trait') {
								$pos_state = explode('Positive State: ', $feat['description'])[1];
								$pos_state = explode('; Negative State: ', $pos_state)[0];
								$neg_state = explode('Negative State: ', $feat['description'])[1];
								echo 
								"<tr class='table-row' id='row_".$feat['id']."'>
									<td class='center'><input class='morale-trait-check' type='checkbox' ".(isset($feat['active']) || $counts['morale_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$feat['id']."'></td>
									<td>".$feat['name']."</td>
									<td>".$pos_state."</td>
									<td>".$neg_state."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
							}
						}
					?>
				</table>
			</div>

			<div class="title">
				<h4 class="table-heading" id="section_compelling_action">Compelling Actions</h4>
				<label class="toggle-switchy" for="compelling_action_toggle" data-size="sm" data-text="false">
					<input checked type="checkbox" id="compelling_action_toggle" checked onclick="enable(this, 'compelling-action-check')">
					<span class="toggle">
						<span class="switch"></span>
					</span>
				</label>
			</div>
			<!-- <span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('compelling_action')"></span> -->
			<div class="panel panel-default">
				<table class="table" id="compelling_action_table">
					<tr>
						<th>Enabled <input type='checkbox' class="compelling-action-check" checked onclick="checkAll(this, 'compelling-action-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($feat_list as $feat) {
							if ($feat['type'] == 'compelling_action') {
								echo 
								"<tr class='table-row' id='row_".$feat['id']."'>
									<td class='center'><input class='compelling-action-check' type='checkbox' ".(isset($feat['active']) || $counts['compelling_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$feat['id']."'></td>
									<td>".$feat['name']."</td>
									<td>".$feat['description']."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
							}
						}
					?>
				</table>
			</div>

			<div class="title">
				<h4 class="table-heading" id="section_profession">Professions</h4>
				<label class="toggle-switchy" for="profession_toggle" data-size="sm" data-text="false">
					<input checked type="checkbox" id="profession_toggle" checked onclick="enable(this, 'profession-check')">
					<span class="toggle">
						<span class="switch"></span>
					</span>
				</label>
			</div>
			<!-- <span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('profession')"></span> -->
			<div class="panel panel-default">
				<table class="table" id="profession_table">
					<tr>
						<th>Enabled <input type='checkbox' class="profession-check" checked onclick="checkAll(this, 'profession-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($feat_list as $feat) {
							if ($feat['type'] == 'profession') {
								echo 
								"<tr class='table-row' id='row_".$feat['id']."'>
									<td class='center'><input class='profession-check' type='checkbox' ".(isset($feat['active']) || $counts['profession_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$feat['id']."'></td>
									<td>".$feat['name']."</td>
									<td>".$feat['description']."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
							}
						}
					?>
				</table>
			</div>

			<div class="title">
				<h4 class="table-heading" id="section_social_background">Social Backgrounds</h4>
				<label class="toggle-switchy" for="social_background_toggle" data-size="sm" data-text="false">
					<input checked type="checkbox" id="social_background_toggle" checked onclick="enable(this, 'social_background-check')">
					<span class="toggle">
						<span class="switch"></span>
					</span>
				</label>
			</div>
			<!-- <span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('social_background')"></span> -->
			<div class="panel panel-default">
				<table class="table" id="social_background_table">
					<tr>
						<th>Enabled <input type='checkbox' class="social_background-check" checked onclick="checkAll(this, 'social_background-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($feat_list as $feat) {
							if ($feat['type'] == 'social_background') {
								echo 
								"<tr class='table-row' id='row_".$feat['id']."'>
									<td class='center'><input class='social_background-check' type='checkbox' ".(isset($feat['active']) || $counts['social_background_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$feat['id']."'></td>
									<td>".$feat['name']."</td>
									<td>".$feat['description']."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
							}
						}
					?>
				</table>
			</div>

		</form>

	</div>

	<!-- welcome modal -->
	<div class="modal" id="welcome_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Campaign Settings</h4>
				</div>
				<div class="modal-body">
					Welcome to the Campaign Settings page!<br><br>From here you can:<br>
					<ul>
						<li>See an overview of your players' stats.</li>
						<li>Distribute XP to your players.</li>
						<!-- <li>Create new Feats/Traits or edit existing Feats/Traits.</li> -->
						<li>Adjust which Talents/Traits are available for your campaign.<br>
						<i class="small">Note: Anything other than Standard or Magical Talents are only available to players during character creation</i></li><br>
					</ul>
					<div class="button-bar">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- award xp modal -->

	<div class="modal" id="xp_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Award XP</h4>
				</div>
				<div class="modal-body">

					<div class="row center">
						<label class="control-label base-award-label">Base XP Award:</label>
						<input type="number" class="form-control" id="base_award" value="0">
					</div>

					<div class="panel">
						<table class="table xp-table center">
							<tr>
								<th class="select-row">
									<label class="toggle-switchy" for="select_all" data-size="sm" data-text="false">
										<input checked type="checkbox" id="select_all" checked>
										<span class="toggle">
											<span class="switch"></span>
										</span>
									</label>
								</th>
								<th class="name-row">Character</th>
								<th>Base Award</th>
								<th>Costume?</th>
								<th>Chips</th>
								<th>Total</th>
							</tr>
							<?php
								foreach($users as $user) {

									// get level
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

									echo 
									"<tr class='mobile-name-row'>
										<td colspan='4'>
										<label><strong>".$user['character_name']."</strong></label>
										<label class='toggle-switchy' for='mobile_select_".$user['id']."' data-size='sm' data-text='false'>
											<input class='xp-checkbox-mobile' checked type='checkbox' id='mobile_select_".$user['id']."' checked>
											<span class='toggle'>
												<span class='switch'></span>
											</span>
										</label>
										</td>
									<tr>
									<tr class='xp-row table-row' id='".$user['id']."'>
										<td class='select-row'>
										<label class='toggle-switchy' for='select_".$user['id']."' data-size='sm' data-text='false'>
											<input class='xp-checkbox' checked type='checkbox' id='select_".$user['id']."' checked>
											<span class='toggle'>
												<span class='switch'></span>
											</span>
										</label>
										</td>
										<td class='name-row'><label for='select_".$user['id']."' class='xp-label min'><strong>".$user['character_name']."</strong></label></td>
										<td>
											<span class='award' id='award_".$user['id']."'>0</span>
										</td>
										<td><input type='checkbox' class='costume-chk' id='costume_".$user['id']."'></td>
										<td><input type='number' value='0' min='0' class='form-control chips' id='chips_".$user['id']."'></td>
										<td>
											<strong><span class='total' id='total_".$user['id']."'>0</span></strong>
											</td>
									</tr>";
								}
							?>
						</table>
					</div>

					<div class="center note">
						<i class="small">Note: XP that has been awarded to characters will be added to their total the next time that player loads/saves their character.</i>
					</div>

					<div class="button-bar">
						<button type="button" class="btn btn-primary" onclick="updateXP()">Award XP</button>
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
					<h4 class="modal-title" id="new_feat_modal_title">New Feat</h4>
				</div>
				<div class="modal-body">
					<form id="new_feat_form">
						<input type="hidden" name="campaign_id" value="<?php echo $campaign['id'] ?>">
						<input type="hidden" id="feat_id" name="feat_id">
						<input type="hidden" id="feat_type_val" name="feat_type">
						<label class="control-label">Name</label>
						<input class="form-control" type="text" id="feat_name_val" name="feat_name">
						<div class="new-feat-element" id="feat_description">
							<label class="control-label">Description</label>
							<textarea class="form-control" type="text" id="feat_description_val" name="feat_descrip"></textarea>
						</div>
						<div class="new-feat-element" id="feat_pos_state">
							<label class="control-label">Positive State</label>
							<textarea class="form-control" type="text" id="feat_pos_state_val" name="feat_pos_state"></textarea>
						</div>
						<div class="new-feat-element" id="feat_neg_state">
							<label class="control-label">Negative State</label>
							<textarea class="form-control" type="text" id="feat_neg_state_val" name="feat_neg_state"></textarea>
						</div>
						<div class="new-feat-element" id="feat_cost">
							<label class="control-label">Cost</label>
							<input class="form-control" type="number" min="0" id="feat_cost_val" name="feat_cost">
						</div>
						<div class="new-feat-element" id="feat_bonus">
							<label class="control-label">Bonus</label>
							<input class="form-control" type="number" min="0" id="feat_bonus_val" name="feat_bonus">
						</div>
						<div class="new-feat-element" id="feat_requirements">
							<label class="control-label">Requirements</label>
							<div id="requirement_container"></div>
							<span class="glyphicon glyphicon-plus-sign" data-toggle="modal" data-target="#new_req_modal"></span>
						</div>
						<label class="control-label new-feat-element" for="character_create_only" id="character_create">Feat only available during character creation <input type="checkbox" id="character_create_only" name="feat_character_create"></label>
						<div class="button-bar">
							<button type="button" class="btn btn-primary btn-danger hidden" onclick="deleteFeat()" id="delete_feat_btn">Delete</button>
							<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
							<button type="button" class="btn btn-primary" onclick="newFeat()" id="update_feat_btn">Ok</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- new requirement modal -->
	<div class="modal" id="new_req_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">New Requirement</h4>
				</div>
				<div class="modal-body">
					<select class="form-control" id="req_type_select">
						<option value="">SELECT REQUIREMENT TYPE</option>
						<option value="attribute">Attribute</option>
						<option value="training">Training</option>
						<option value="feat">Feat</option>
					</select>

					<div id="attribute_inputs" class="hidden req-inputs">
						<label class="control-label">Attribute Name</label>
						<select class="form-control" id="attribute_type_val">
							<option value=""></option>
							<option value="strength">Strength</option>
							<option value="fortitude">Fortitude</option>
							<option value="speed">Speed</option>
							<option value="agility">Agility</option>
							<option value="precision_">Precision</option>
							<option value="awareness">Awareness</option>
							<option value="allure">Allure</option>
							<option value="deception">Deception</option>
							<option value="intellect">Intellect</option>
							<option value="innovation">Innovation</option>
							<option value="intuition">Intuition</option>
							<option value="vitality">Vitality</option>
						</select>
						<label class="control-label">Attribute Value</label>
						<input type="number" class="form-control" id="attribute_value">
					</div>

					<div id="training_inputs" class="hidden req-inputs">
						<label class="control-label">Training Name</label>
						<input type="text" class="form-control" id="training_val">
					</div>

					<div id="feat_inputs" class="hidden req-inputs">
						<label class="control-label">Feat Name</label>
						<input type="text" class="form-control" id="feat_val">
					</div>

					<!--  multiple possible requirement conditions -->
					<label class="control-label" for="multi_req">This requirement can be satisfied by multiple conditions <input type="checkbox" id="multi_req"></label>

					<div id="multi_req_container" class="hidden">
						<h4 class="center">- OR -</h4>
						<select class="form-control" id="req_type_select2">
							<option value="">SELECT REQUIREMENT TYPE</option>
							<option value="attribute">Attribute</option>
							<option value="training">Training</option>
							<option value="feat">Feat</option>
						</select>

						<div id="attribute_inputs2" class="hidden req-inputs2">
							<label class="control-label">Attribute Name</label>
							<select class="form-control" id="attribute_type_val2">
								<option value=""></option>
								<option value="strength">Strength</option>
								<option value="fortitude">Fortitude</option>
								<option value="speed">Speed</option>
								<option value="agility">Agility</option>
								<option value="precision_">Precision</option>
								<option value="awareness">Awareness</option>
								<option value="allure">Allure</option>
								<option value="deception">Deception</option>
								<option value="intuition">Intuition</option>
								<option value="vitality">Vitality</option>
							</select>
							<label class="control-label">Attribute Value</label>
							<input type="number" class="form-control" id="attribute_value2">
						</div>

						<div id="training_inputs2" class="hidden req-inputs2">
							<label class="control-label">Training Name</label>
							<input type="text" class="form-control" id="training_val2">
						</div>

						<div id="feat_inputs2" class="hidden req-inputs2">
							<label class="control-label">Feat Name</label>
							<input type="text" class="form-control" id="feat_val2">
						</div>
					</div>

					<div class="button-bar">
						<button type="button" class="btn btn-primary" onclick="newRequirement()">Ok</button>
						<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
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

	<!-- footer -->
	<div class="footer row">
		<p class="link col-md-4" data-toggle="modal" data-target="#welcome_modal"><span class="glyphicon glyphicon-info-sign"></span> Guide</p>
		<p class="link col-md-4" data-toggle="modal" data-target="#suggestion_modal"><span class="glyphicon glyphicon-envelope"></span> Suggestion Box</p>
		<p class=" col-md-4">© <?php echo date("Y"); ?> CrabAgain.com</p>
	</div>

</body>

<!-- JavaScript -->
<script async src="https://www.google.com/recaptcha/api.js?render=6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi"></script>
<script src="/assets/jquery/jquery-3.5.1.min.js"></script>
<script src="/assets/bootstrap/js/bootstrap.min.js"></script>
<script src="/assets/jquery/jquery-ui-1.12.1.min.js"></script>
<script type="text/javascript">

	$(".xp-label").hover(function(){
		$(this).addClass("highlight");
	}, function(){
		$(this).removeClass("highlight");
	});

	// section links dropdown
	$("#section_links").on("change", function(){
		$('html,body').animate({scrollTop: $($(this).val()).offset().top},'slow');
		$(this).val("");
	});

	$("#req_type_select").on("change", function(){
		$(".req-inputs").addClass("hidden");
		$("#"+$(this).val()+"_inputs").removeClass("hidden");
	});
	$("#req_type_select2").on("change", function(){
		$(".req-inputs2").addClass("hidden");
		$("#"+$(this).val()+"_inputs2").removeClass("hidden");
	});

	$("#multi_req").on("change", function(){
		if ($(this).is(":checked")) {
			$("#multi_req_container").removeClass("hidden");
		} else {
			$("#multi_req_container").addClass("hidden");
		}
	});

	$("#select_all").on("change", function(){
		$(".xp-checkbox").prop("checked", this.checked).trigger("change");
	});

	// xp award table input functions
	$("#base_award").on("input", function(){
		// get number value
		var num = parseInt($(this).val());
		if (isNaN(num) && $(this).val() != "") {
			num = 0;
		}
		$(this).val(num);
		$(".award").html(isNaN(num) ? 0 : num);
		adjustTotals();
	});

	$(".costume-chk").on("change", function(){
		adjustTotals();
	});

	$(".xp-checkbox").on("change", function(){
		adjustTotals();
		var id = this.id.split("select_")[1];
		$("#mobile_select_"+id).prop("checked", $(this).is(":checked"));
		$("#chips_"+id).attr("disabled", !$(this).is(":checked"));
		$("#costume_"+id).attr("disabled", !$(this).is(":checked"));
	});

	$(".xp-checkbox-mobile").on("change", function(){
		var id = this.id.split("mobile_select_")[1];
		$("#select_"+id).trigger("click");
	});


	$(".chips").on("input", function(){
		// get number value
		var num = parseInt($(this).val());
		if (isNaN(num)) {
			num = 0;
		}
		$(this).val(num);
		adjustTotals();
	});

	function adjustTotals() {
		// select_id
		$(".xp-row").each(function(){
			var is_mobile = $("#select_"+this.id).is(":hidden");
			var selected = is_mobile ? $("#mobile_select_"+this.id).is(":checked") : $("#select_"+this.id).is(":checked");
			var level = parseInt($("#level_"+this.id).html());
			var base = parseInt($("#award_"+this.id).html());
			var costume = $("#costume_"+this.id).is(":checked");
			var chips = parseInt($("#chips_"+this.id).val());
			$("#total_"+this.id).html( selected ? base + (costume ? level : 0) + (chips * level) : 0 );
		});
	}

	var users = <?php echo json_encode($users); ?>;
	var campaign = <?php echo json_encode($campaign); ?>;

	// get feat counts
	var total_count = <?php echo json_encode($total_count); ?>;
	var counts = <?php echo json_encode($counts); ?>;
	if (total_count != 0) {
		if (counts['physical_pos_count'] == 0) {
			$("#physical_trait_pos_toggle").trigger("click");
		}
		if (counts['physical_neg_count'] == 0) {
			$("#physical_trait_neg_toggle").trigger("click");
		}
		if (counts['social_count'] == 0) {
			$("#social_trait_toggle").trigger("click");
		}
		if (counts['morale_count'] == 0) {
			$("#morale_trait_toggle").trigger("click");
		}
		if (counts['compelling_count'] == 0) {
			$("#compelling_action_toggle").trigger("click");
		}
		if (counts['profession_count'] == 0) {
			$("#profession_toggle").trigger("click");
		}
	}

	// get feat list and requirements
	var feat_list = <?php echo json_encode($feat_list); ?>;
	// set feat list for autocomplete
	var feats = [];
	for (var i in feat_list) {
		if (feat_list[i]['type'] == 'feat') {
			feats.push(feat_list[i]['name']);
		}
	}
	$("#feat_val").autocomplete({
		source: feats,
		select: function(event, ui) {}
	});
	$("#feat_val2").autocomplete({
		source: feats,
		select: function(event, ui) {}
	});

	// set training list for autocomplete
	var trainings = ["Swimming", "Engineering", "Train Animal", "Perform", "First Aid", "Tactics", "Demolitions", "Security", "Survival", "Sleight of Hand", "Ride Animal", "Stealth"];
	$("#training_val").autocomplete({
		source: trainings.sort(),
		select: function(event, ui) {}
	});
	$("#training_val2").autocomplete({
		source: trainings.sort(),
		select: function(event, ui) {}
	});

	// launch new feat modal
	function newFeatModal(type) {
		$("#feat_type_val").val(type);
		$("#new_feat_modal_title").html( ($("#feat_id").val() == "" ? "New " : "Update ") + $("#section_"+type).html());
		// hide/show elements based on feat type
		$(".new-feat-element").addClass("hidden");
		if (type != "morale_trait") {
			$("#feat_description").removeClass("hidden");
		} else {
			$("#feat_neg_state").removeClass("hidden");
			$("#feat_pos_state").removeClass("hidden");
		}
		if (type == "feat") {
			$("#feat_requirements").removeClass("hidden");
			$("#character_create").removeClass("hidden");
		}
		if (type == "physical_trait_neg") {
			$("#feat_bonus").removeClass("hidden");
		}
		if (type == "physical_trait_pos") {
			$("#feat_cost").removeClass("hidden");
		}
		$("#new_feat_modal").modal("show");
		$("#feat_description_val").height( $("#feat_description_val")[0].scrollHeight );
	}

	// on modal close, reset inputs
	$("#new_feat_modal").on('hidden.bs.modal', function(){
		$("#delete_feat_btn").addClass("hidden");
		$("#update_feat_btn").html("Ok");
		$("#new_feat_modal_title").html("New Feat");
		$("#feat_id").val("");
		$("#feat_name_val").val("");
		$("#feat_description_val").val("");
		$("#feat_description_val").height('100px');
		$("#feat_pos_state_val").val("");
		$("#feat_neg_state_val").val("");
		$("#feat_cost_val").val("");
		$("#feat_bonus_val").val("");
		$("#feat_requirements").val("");
		$("#character_create_only").prop("checked", false);
		$("#requirement_container").html("");
	});
	$("#new_req_modal").on('hidden.bs.modal', function(){
		$("#req_type_select").val("").trigger("change");
		$("#attribute_type_val").val("");
		$("#attribute_value").val("");
		$("#training_val").val("");
		$("#feat_val").val("");
		$("#multi_req").prop("checked", false).trigger("change");
		$("#req_type_select2").val("").trigger("change");
		$("#attribute_type_val2").val("");
		$("#attribute_value2").val("");
		$("#training_val2").val("");
		$("#feat_val2").val("");
	});
	$("#xp_modal").on('hidden.bs.modal', function(){
		$(".award").html("0");
		$(".chips").val("0");
		$(".total").html("0");
		$("#base_award").val("0");
		$(".xp-checkbox").prop("checked", true);
		$(".xp-checkbox-mobile").prop("checked", true);
		$(".costume-chk").prop("checked", false);
		$("#select_all").prop("checked", true);
	});
	$("#xp_modal").on('shown.bs.modal', function(){
		scrollToTop();
	});

	// add a new feat requirement
	function newRequirement() {
		// make sure inputs aren't empty
		var multi_req = $("#multi_req").is(":checked");
		var value  = "";
		var value2  = "";
		var feat_type = $("#req_type_select").val();
		var error = "";
		if (feat_type == "") {
			error = "Please select a requirement type";
		} else if (feat_type == "attribute") {
			var attribute_type = $("#attribute_type_val").val();
			var attribute_val = $("#attribute_value").val();
			if (attribute_type == "") {
				error = "Please select an attribute";
			} else if (attribute_val == "") {
				error = "Please enter an attribute value";
			} else {
				value = capitalize(attribute_type.replace("_", ""))+": "+attribute_val;
			}
		} else if (feat_type == "training") {
			var training = $("#training_val").val();
			if (training == "") {
				error = "Please enter a training name";
			} else {
				value = "Training: "+training;
			}
		} else {
			var feat = $("#feat_val").val();
			if (feat == "") {
				error = "Please enter a feat name";
			} else {
				value = "Feat: "+feat;
			}
		}
		if (multi_req) {
			var feat_type2 = $("#req_type_select2").val();
			if (feat_type2 == "") {
				error = "Please select a requirement type";
			} else if (feat_type2 == "attribute") {
				var attribute_type2 = $("#attribute_type_val2").val();
				var attribute_val2 = $("#attribute_value2").val();
				if (attribute_type2 == "") {
					error = "Please select an attribute";
				} else if (attribute_val2 == "") {
					error = "Please enter an attribute value";
				} else {
					value2 = " OR "+capitalize(attribute_type2.replace("_", ""))+": "+attribute_val2;
				}
			} else if (feat_type2 == "training") {
				var training2 = $("#training_val2").val();
				if (training2 == "") {
					error = "Please enter a training name";
				} else {
					value2 = " OR Training: "+training2;
				}
			} else {
				var feat2 = $("#feat_val2").val();
				if (feat2 == "") {
					error = "Please enter a feat name";
				} else {
					value2 = " OR Feat: "+feat2;
				}
			}
			value += value2;
		}
		if (error == "") {
			addRequirement(value);
			$("#new_req_modal").modal("hide");
		} else {
			alert(error);
		}
	}

	// create elements with requirement value
	function addRequirement(value) {
		// create elements
		var span = $('<span />', {
		  'class': 'feat-requirement',
		}).appendTo($("#requirement_container"));
	    $('<p />', {
	    	'class': 'feat-requirement-label',
	    	'text': value
	    }).appendTo(span);
	    $('<input />', {
	    	'type': 'hidden',
	    	'value': value,
	    	'name': 'feat_reqs[]',
	    	'class': 'feat-req-val'
	    }).appendTo(span);
		var removeBtn = $('<span />', {
		  'class': 'glyphicon glyphicon-remove',
		}).appendTo(span);
		removeBtn.on("click", function(){
			span.remove();
		});
	}

	function editFeat(name) {
		// get feat from feat list
		for (var i in feat_list) {
			if (feat_list[i]['name'].replace("'", "") == name) {
				$("#delete_feat_btn").removeClass("hidden");
				$("#update_feat_btn").html("Update");
				$("#feat_id").val(feat_list[i]['id']);
				$("#feat_type_val").val(feat_list[i]['type']);
				// fill modal values and launch modal
				$("#feat_name_val").val(feat_list[i]['name']);
				if (feat_list[i]['type'] != "morale_trait") {
					$("#feat_description_val").val(feat_list[i]['description']);
				} else {
					var pos_state = feat_list[i]['description'].split("Positive State: ")[1].split("; Negative State")[0];
					var neg_state = feat_list[i]['description'].split("Negative State: ")[1];
					$("#feat_neg_state_val").val(neg_state);
					$("#feat_pos_state_val").val(pos_state);
				}
				if (feat_list[i]['type'] == "feat") {
					for (var j in feat_list[i]['requirements']) {
						value = "";
						for (var k in feat_list[i]['requirements'][j]) {
							if (k > 0) {
								value += " OR ";
							}
							for (var l in feat_list[i]['requirements'][j][k]) {
								if (l == "character_creation") {
									$("#character_create_only").prop("checked", true);
								} else {
									if (l == "feat") {
										value += "Feat: "+feat_list[i]['requirements'][j][k][l];
									} else if (l == "training") {
										value += "Training: "+feat_list[i]['requirements'][j][k][l];
									} else {
										value += capitalize(l).replace("_", "")+": "+feat_list[i]['requirements'][j][k][l];
									}
								}
							}
						}
						if (value != "") {
							addRequirement(value);
						}
					}
				}
				if (feat_list[i]['type'] == "physical_trait" && feat_list[i]['cost'] < 0) {
					$("#feat_bonus_val").val(feat_list[i]['cost']*-1);
				}
				if (feat_list[i]['type'] == "physical_trait" && feat_list[i]['cost'] > 0) {
					$("#feat_cost_val").val(feat_list[i]['cost']);
				}
				newFeatModal(feat_list[i]['type'] == "physical_trait" ? 
					(feat_list[i]['cost'] > 0 ? "physical_trait_pos" : "physical_trait_neg") : feat_list[i]['type']);

			}
		}
	}

	// remove feat from database
	function deleteFeat() {
		var conf = confirm("Are you sure you want to delete this feat?");
		if (conf) {
			// get feat id
			var feat_id = $("#feat_id").val();
			// close modal
			$("#new_feat_modal").modal("hide");
			// send ajax request
			$.ajax({
				url: '/scripts/delete_feat.php',
				data: { 'feat_id' : feat_id },
				ContentType: "application/json",
				type: 'POST',
				success: function(response){
					// remove row from table
					if (response == 'ok') {
						$("#row_"+feat_id).remove();
						// update feat_list and feats
						var feat_name = "";
						for (var i in feat_list) {
							if (feat_list[i]['id'] == feat_id) {
								feat_name = feat_list[i]['name'];
								feat_list.splice(i,1);
								break;
							}
						}
						for (var i in feats) {
							if (feats[i] == feat_name) {
								feats.splice(i,1);
								break;
							}
						}
					}
				}
			});
		}
	}

	// create/update feat from modal values
	function newFeat() {
		var error = "";
		if ($("#feat_name_val").val() == "") {
			error = "Name is required";
		}
		switch ($("#feat_type_val").val()) {
			case "feat":
				if ($("#feat_description_val").val() == "") {
					error = "Description is required";
				}
				if ($("#requirement_container").html() == "") {
					error = "Feat requirements is required";
				}
				break;
			case "morale_trait":
				if ($("#feat_pos_state_val").val() == "") {
					error = "Positive state is required";
				}
				if ($("#feat_neg_state_val").val() == "") {
					error = "Negative state is required";
				}
				break;
			case "physical_trait_pos":
				if ($("#feat_description_val").val() == "") {
					error = "Description is required";
				}
				if ($("#feat_cost_val").val() == "") {
					error = "Cost is required";
				}
				break;
			case "physical_trait_neg":
				if ($("#feat_description_val").val() == "") {
					error = "Description is required";
				}
				if ($("#feat_bonus_val").val() == "") {
					error = "Bonus is required";
				}
				break;
			case "social_background":
			case "social_trait":
			case "compelling_action":
			case "profession":
				if ($("#feat_description_val").val() == "") {
					error = "Description is required";
				}
				break;
		}
		if (error != "") {
			alert(error);
			return;
		}

		// check if we are editing or creating a new feat
		var udpate = $("#feat_id").val() != undefined && $("#feat_id").val() != "";
		var conf = udpate ? confirm("Are you sure you want to update this feat?") : confirm("Are you sure you want to create a new feat?");
		if (conf) {
			// submit form via ajax
			$.ajax({
                url: udpate ? '/scripts/update_feat.php' : '/scripts/feat_submit.php',
                type: 'POST',
                data: $("#new_feat_form").serialize(),
                success:function(result){
                	if (isNaN(result)) {
                		if (result == "update ok") {
                			alert("Feat updated successfully");
                			var feat_id = $("#feat_id").val();
                			var row = getRowForFeat(feat_id);
                			$("#row_"+feat_id).replaceWith(row);
                			// update feat_list and feats
                			var feat = getNewFeatVals(feat_id);
                			var feat_name = "";
                			for (var i in feat_list) {
                				if (feat_list[i]['id'] == feat['id']) {
                					feat_name = feat_list[i]['name'];
                					feat_list.splice(i, 1);
                					break;
                				}
                			}
                			feat_list.push(feat);
                			if (feat['type'] == 'feat') {
	                			for (var i in feats) {
	                				if (feats[i] == feat_name) {
	                					feats.splice(i, 1);
	                					break;
	                				}
	                			}
	                			feats.push(feat['name']);
                			}
                			$("#new_feat_modal").modal("hide");
                			saveCampaignSettings();
                		} else {
                			alert(result);
                		}
                	} else {
                		var feat = getNewFeatVals(result);
	    				feat_list.push(feat);
						if (feat['type'] == 'feat') {
							feats.push(feat['name']);
							$("#feat_val").autocomplete({
								source: feats
							});
							$("#feat_val2").autocomplete({
								source: feats
							});
						}
						var row = getRowForFeat(result);
						switch($("#feat_type_val").val()) {
							case "feat":
							    var element = $("#feat_table");
				    			break;
							case "physical_trait_pos":
							    var element = $("#physical_trait_pos_table");
								break;
							case "physical_trait_neg":
							    var element = $("#physical_trait_neg_table");
								break;
							case "social_trait":
							    var element = $("#social_trait_table");
								break;
							case "morale_trait":
							    var element = $("#morale_trait_table");
								break;
							case "compelling_action":
							    var element = $("#compelling_action_table");
								break;
							case "profession":
							    var element = $("#profession_table");
								break;
							case "social_background":
							    var element = $("#social_background_table");
								break;
						}
						row.appendTo(element);
                		$("#new_feat_modal").modal("hide");
                		saveCampaignSettings();
                	}
                }
            });
		}
	}

	function getNewFeatVals(feat_id) {
		feat = [];
		feat['id'] = feat_id;
		feat['type'] = $("#feat_type_val").val().includes("physical_trait") ? "physical_trait" : $("#feat_type_val").val();
		feat['name'] = $("#feat_name_val").val();
	    if ($("#feat_type_val").val() != "morale_trait") {
			feat['description'] = $("#feat_description_val").val();
	    } else {
	    	feat['description'] = "Positive State: "+$("#feat_pos_state_val").val()+"; Negative State: "+$("#feat_neg_state_val").val();
	    }
		switch($("#feat_type_val").val()) {
			case "feat":
				// add feat['requirements']
			    var requirements = [];
			    $(".feat-req-val").each(function(){
			    	// create req set array
			    	var req_set = [];
			    	// split on ' OR '
			    	var reqs = $(this).val().split(" OR ");
			    	for (var i in reqs) {
			    		// for each - create dictionary type : value
			    		var req = [];
			    		req[reqs[i].split(":")[0]] = reqs[i].split(":")[1];
			    		req_set.push(req);
			    	}
			    	requirements.push(req_set);
			    });
			    // check for 'character_create_only'
			    if ($("#character_create_only").prop("checked", true)) {
			    	var req_set = [];
			    	var req = [];
			    	req["character_creation"] = true;
			    	req_set.push(req);
			    	requirements.push(req_set);
				}
			    feat['requirements'] = requirements;
    			break;
			case "physical_trait_pos":
				feat['cost'] = $("#feat_cost_val").val();
				break;
			case "physical_trait_neg":
				feat['cost'] = parseInt($("#feat_bonus_val").val()) * -1;
				break;
		}
		return feat;
	}

	function getRowForFeat(feat_id) {
		// add new entry row to table
	    var row = $('<tr />', {
	    	'class': 'table-row',
	    	'id': 'row_'+feat_id
	    });
		var check = $('<td />', {
	    	'class': 'center'
	    }).appendTo(row);
	    // disable check if section is disabled
	    var type = $("#feat_type_val").val();
	    var enabled = type == 'feat' || $("#"+type+"_toggle").prop("checked");
	    $('<input />', {
	    	'type': 'checkbox',
	    	'class': $("#feat_type_val").val().replaceAll("_","-")+"-check",
	    	'value': feat_id,
	    	'name': 'feat_status[]',
	    	"checked": "checked",
	    	"disabled": !enabled
	    }).appendTo(check);
		$('<td />', {
	    	'text': $("#feat_name_val").val()
	    }).appendTo(row);
	    if ($("#feat_type_val").val() != "morale_trait") {
			$('<td />', {
		    	'text': $("#feat_description_val").val()
		    }).appendTo(row);
	    }
		switch($("#feat_type_val").val()) {
			case "feat":
			    var reqs = "";
			    $(".feat-req-val").each(function(){
			    	reqs += "&#8226;"+$(this).val()+"<br>";
			    });
			    if ($("#character_create_only").prop("checked", true)) {
				    reqs += "&#8226;Character Creation Only<br>";
				}
    			$('<td />', {
			    	'html': reqs
			    }).appendTo(row);
    			break;
			case "physical_trait_pos":
    			$('<td />', {
    				'class': 'center',
			    	'text': $("#feat_cost_val").val()
			    }).appendTo(row);
				break;
			case "physical_trait_neg":
    			$('<td />', {
    				'class': 'center',
			    	'text': $("#feat_bonus_val").val()
			    }).appendTo(row);
				break;
			case "morale_trait":
    			$('<td />', {
			    	'text': $("#feat_pos_state_val").val()
			    }).appendTo(row);
    			$('<td />', {
			    	'text': $("#feat_neg_state_val").val()
			    }).appendTo(row);
				break;
		}
		var edit = $('<td />', {
	    }).appendTo(row);
	    var btn = $('<span />', {
	    	'class': 'glyphicon glyphicon-edit'
	    }).appendTo(edit);
	    var name = $("#feat_name_val").val();
	    btn.on("click", function(){
	    	editFeat(name.replaceAll("'",""));
	    });
	    return row;
	}

	// add award val to modal inputs
	function awardXP() {
		// get xp val
		var xp = $("#xp_val").val() == "" ? 0 : $("#xp_val").val();
		$("#xp_val").val("");
		// get checked box ids
		$(".xp-checkbox").each(function(){
			if (!isNaN(this.id) && $(this).is(":checked")) {
				// get current award value
				var current = $("#award_"+this.id).html();
				$("#award_"+this.id).html(parseInt(current) + parseInt(xp));
			}
		});
	}

	// update xp award value in table and database
	function updateXP() {
		var conf = confirm("Award XP to characters?");
		if (conf) {
			var users = [];
			var awards = [];
			$(".xp-checkbox").each(function(){
				var id = this.id.split("select_")[1];
				if (!isNaN(id)) {
					// get award value for user
					var award = parseInt($("#total_"+id).html());
					if (award != 0) {
						users.push(id);
						awards.push(award)
						// update table display
						var xp_text_val = $("#xp_"+id).html();
						var text_parts = xp_text_val.split(" (");
						var xp_val = text_parts.length > 1 ? parseInt(text_parts[0]) : parseInt(xp_text_val);
						var award_val = text_parts.length > 1 ? award + parseInt(text_parts[1]) : award;
						$("#xp_"+id).html(xp_val + (award_val > 0 ? " (+"+award_val+")" : " ("+award_val+")"));
					}
				}
			});
			$("#xp_modal").modal("hide");

			// add xp awards to database
			$.ajax({
			  url: '/scripts/set_xp_awards.php',
			  data: { 'users' : users, 'awards' : awards },
			  ContentType: "application/json",
			  type: 'POST',
			  success: function(response) {
			  	// do nothing
			  }
			});
		}
	}

	// submit campaign settings to ajax
	function saveCampaignSettings() {
		$.ajax({
		  url: '/scripts/update_campaign.php',
		  data: $("#campaign_form").serialize(),
		  ContentType: "application/json",
		  type: 'POST',
		  success: function(response){
		  	if (response == 'ok') {
		  		// do nothing
		  	}
		  }
		});
	}

	function enable(e, type) {
		$("."+type).attr("disabled", !e.checked);
	}

	function checkAll(e, type) {
		$("."+type).prop("checked", e.checked);
	}

	function capitalize(string) {
		return string.charAt(0).toUpperCase() + string.slice(1);
	}

	function scrollToTop() {
		$("html, body").animate({ scrollTop: 0 }, "fast");
	}

	window.onscroll = function() {
		if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
			$("#scroll_top").removeClass("no-vis")
		} else {
			$("#scroll_top").addClass("no-vis");
		}
	};

	$(document).ready(function() {
		$("input:checkbox").change(function(){
			saveCampaignSettings();
		});
	});

</script>