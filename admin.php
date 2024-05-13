<?php

	session_set_cookie_params(604800);
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

	// get all login users and campaign membership status
	$login_data = [];
	$login_campaigns = [];
	$login_ids = [];
	$sql = "SELECT * FROM login WHERE 1 ORDER BY email ASC";
	$result_1 = $db->query($sql);
	if ($result_1) {
		while($row = $result_1->fetch_assoc()) {
			$login = $row;
			$login['active'] = false;
			$login['admin'] = false;

			$sql = "SELECT * FROM login_campaign WHERE campaign_id = $campaign_id";
			$result_2 = $db->query($sql);
			if ($result_2) {
				while($row = $result_2->fetch_assoc()) {
					if ($row['login_id'] == $login['id']) {
						array_push($login_campaigns, $row);
						$login['active'] = true;
						$login['admin'] = $login['admin'] || $row['campaign_role'] == 1;
					}
				}
			}
			array_push($login_ids, $login['id']);
			array_push($login_data, $login);
		}
	}

	// sort active users at the top of the list
	usort($login_data, function($a, $b) {
    	return $b['active'] <=> $a['active'];
	});

	// get characters
	$users = [];
	$sql = "SELECT * FROM user WHERE campaign_id = $campaign_id AND login_id IN (".implode(',',$login_ids).")";
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

	$logins = [];
	foreach($login_data as $login) {
		$characters = "";
		foreach($users as $user) {
			if ($user['login_id'] == $login['id']) {
				$characters .= $user['character_name'].", ";
			}
		}
		$characters = rtrim($characters, ", ");
		$login['characters'] = $characters;
		array_push($logins, $login);
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
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = $campaign_id AND type = 'physical_trait' AND cost > 0";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['physical_pos_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = $campaign_id AND type = 'physical_trait' AND cost < 0";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['physical_neg_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = $campaign_id AND type = 'social_trait'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['social_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = $campaign_id AND type = 'morale_trait'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['morale_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = $campaign_id AND type = 'compelling_action'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['compelling_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = $campaign_id AND type = 'profession'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['profession_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = $campaign_id AND type = 'social_background'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['social_background_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_feat JOIN feat_or_trait ON feat_or_trait.id = campaign_feat.feat_id WHERE campaign_id = $campaign_id AND type = 'magic_talent'";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['magical_talent_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}

	// get feat active status
	$campaign_feats = [];
	$sql = "SELECT * FROM campaign_feat WHERE campaign_id = $campaign_id";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($campaign_feats, $row);
		}
	}

	$talents = [];
	$json_string = file_get_contents($keys['feat_list']);
	$talent_list_json = json_decode($json_string);

	// assign talent ID
	foreach($talent_list_json as $json) {
		foreach($feats as $feat) {
			if ($feat['name'] == $json->name) {
				$json->id = $feat['id'];
				array_push($talents, $json);
			}
		}
	}
	usort($talents, function($a, $b) {
    	return $a->name <=> $b->name;
	});
	
	// check if talent is active for the current campaign
	foreach($talents as $talent) {
		foreach ($campaign_feats as $campaign_feat) {
				if ($campaign_feat['feat_id'] == $talent->id) {
					$talent->active = true;
			}
		}
	}

	// get races
	$races = [];
	$race_data = [];
	$race_ids = [];
	$sql = "SELECT * FROM race ORDER BY name";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($race_data, $row);
			array_push($race_ids, $row['id']);
		}
	}

	// get race active status
	$campaign_races = [];
	$sql = "SELECT * FROM campaign_race WHERE campaign_id = $campaign_id";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($campaign_races, $row);
		}
	}
	$sql = "SELECT count(*) AS count FROM campaign_race WHERE campaign_id = $campaign_id";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$counts['race_count'] = $row['count'];
			$total_count += $row['count'];
		}
	}
	
	// check if race is active for the current campaign
	foreach($race_data as $race) {
		foreach ($campaign_races as $campaign_race) {
			if ($campaign_race['race_id'] == $race['id']) {
				$race['active'] = true;
			}
		}
		array_push($races, $race);
	}

	// get race traits and skills
	$race_traits = [];
	$sql = "SELECT * from race_trait WHERE race_id IN (".implode(',',$race_ids).")";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($race_traits, $row);
		}
	}
	$race_skills = [];
	$sql = "SELECT * from race_skill WHERE race_id IN (".implode(',',$race_ids).")";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($race_skills, $row);
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

<style type="text/css">
	body {
		overflow-x: auto;
	}
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
		width: 100%;
		max-width: 1200px;
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
	.table.fixed-width th:nth-of-type(1) {
		width: 120px;
		max-width: 120px;
	}
	.table.fixed-width th:nth-of-type(2) {
		width: 180px;
		max-width: 180px;
	}
	.container, .footer {
		min-width: 1200px;
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
	#xp_modal .modal-content, #edit_players_modal .modal-content {
		background-color: #cccccc;
	}
	#xp_modal .note {
		margin: 0 auto;
		margin-top: 15px;
		cursor: default;
		white-space: normal;
		width: 100%;
	}
	.modal-content .toggle-switchy {
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
	.modal-body {
		max-width: 100%;
		overflow-x: auto;
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
		display: flex;
		justify-content: space-between;
		background-color: transparent;
		border: none;
		box-shadow: none;
		-webkit-box-shadow: none;
		max-width: 1200px;
		padding: 0 250px;
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
	.btn-primary {
		margin: 0 10px;
	}
	.btn.btn-primary.btn-danger {
		background-color: red !important;
	}
	.highlight-hover:hover {
		text-shadow: 0px 0px 3px #404040;
	}

	/*@media (max-width: 868px) {
		.name-row, .select-row {
			display: none;
		}
		.mobile-name-row {
			display: table-row;
		}
		.table-row td {
			border-top: none !important;
		}
	}*/
	/*@media (max-width: 868px) {
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
			max-width: 300px;
		}
	}*/
	/* extra small for mobile? */
	/*@media (max-width: 767px) {
		#xp_modal .table>tbody>tr>th, #xp_modal .table>tbody>tr>td {
			padding: 5px;
			font-size: 13px;
		}
		th input {
			margin-top: -6px !important;
		}
		#xp_modal .modal-dialog {
			width: 465px;
		}
		#xp_modal .small {
			font-size: 63%;
		}
	}*/
</style>

<body>
	<div class="container">

		<input type="hidden" id="campaign_id" value="<?php echo $campaign_id?>">
		<input type="hidden" id="login_id" value="<?php echo $login_id?>">

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
			<option value="">Races</option>
			<option value="#section_standard">Standard Talents</option>
			<option value="#magical_talents">Magical Talents</option>
			<option value="#section_physical_trait_pos">Physical Traits (Positive)</option>
			<option value="#section_physical_trait_neg">Physical Traits (Negative)</option>
			<option value="#section_social_trait">Social Traits</option>
			<option value="#section_morale_trait">Morale Traits</option>
			<option value="#section_compelling_action">Compelling Actions</option>
			<option value="#section_profession">Professions</option>
			<option value="#section_social_background">Social Backgrounds</option>
		</select>

		<!-- character stats overview -->
		<?php
			if (count($users) == 0) {
				echo "<h2 class='center'>No Characters Yet!</h2>";
			}
		?>
		<div class="panel panel-default <?php if(count($users) == 0) { echo 'hidden'; } ?> ">
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
						// make sure player is active in campaign - user['login_id'] is in login_campaigns
						$active = false;
						foreach($login_campaigns as $login) {
							$active = $active || $login['login_id'] == $user['login_id'];
						}
						if (!$active) {
							continue;
						}

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
						"<tr class='table-row user-row'>
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
		<div class="btn-wrapper panel panel-default" <?php if(count($users) == 0) { echo 'hidden'; } ?>>
			<button id="xp_btn" data-toggle="modal" data-target="#xp_modal" <?php if(count($users) == 0) { echo 'disabled'; } ?>><i class="fa-solid fa-award"></i> Award XP</button>
			<button id="xp_btn" data-toggle="modal" data-target="#edit_players_modal">Add/Remove Players</button>
		</div>

		<form id="campaign_form">
			<input type="hidden" id="campaign_id" name="campaign_id" value="<?php echo $campaign['id'] ?>">

			<div class="title">
				<h4 class="table-heading" id="section_races">Races</h4>
				<label class="toggle-switchy" for="race_toggle" data-size="sm" data-text="false">
					<input checked type="checkbox" id="race_toggle" checked onclick="enable(this, 'race-check')">
					<span class="toggle">
						<span class="switch"></span>
					</span>
				</label>
			</div>
			<div class="panel panel-default">
				<table class="table" id="race_table">
					<tr>
						<th>Enabled <input type='checkbox' class="race-check" checked onclick="checkAll(this, 'race-check')"></th>
						<th>Name</th>
						<th>Size</th>
						<th>Traits</th>
						<th>Skills</th>
					</tr>
					<?php
						foreach($races as $race) {
							$traits = "";
							foreach($race_traits as $trait) {
								if ($trait['race_id'] == $race['id']) {
									$traits .= "<span class='highlight-hover'>".$trait['trait']."</span>, ";
								}
							}
							$traits = rtrim($traits, ", ");
							$skills = "";
							foreach($race_skills as $skill) {
								if ($skill['race_id'] == $race['id']) {
									$skills .= "<span class='highlight-hover'>".($skill['value'] == 0 ? 'Training: ' : 'Focus: ').$skill['skill'].($skill['input_required'] ? ' (Any)' : '').($skill['value'] == 0 ? '' : ' +'.$skill['value'])."</span>, ";
								}
							}
							$skills = rtrim($skills, ", ");
							echo 
							"<tr class='table-row' id='row_".$race['id']."'>
								<td class='center'><input id='".$race['id']."' class='race-check' type='checkbox' ".(isset($race['active']) || $counts['race_count'] == 0 ? 'checked' : '')." name='race_status[]' value='".$race['id']."'></td>
								<td class='highlight-hover'><label for='".$race['id']."'>".$race['name']."</label></td>
								<td class='highlight-hover'>".$race['size']."</td>
								<td>".$traits."</td>
								<td>".$skills."</td>
							</tr>";
						}
					?>
				</table>
			</div>

			<h4 class="table-heading" id="section_standard">Standard Talents</h4>
			<!-- <span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('feat')"></span> -->
			<div class="panel panel-default">
				<table class="table fixed-width" id="feat_table">
					<tr>
						<th>Enabled</th>
						<th>Name</th>
						<th>Description</th>
						<th>Requirements</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($talents as $talent) {
							$reqs = "";
							if ($talent->type == 'standard_talent') {
								// build requirement string
								foreach($talent->requirements as $req_set) {
									$reqs .= "<span class='highlight-hover'>";
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
									$reqs .= "</span>";
								}
								echo 
								"<tr class='table-row' id='row_".$talent->id."'>
									<td class='center'><input id='check_".$talent->id."' type='checkbox' ".(isset($talent->active) || $total_count == 0 ? 'checked' : '')." name='feat_status[]' value='".$talent->id."'></td>
									<td class='highlight-hover'><label for='check_".$talent->id."'>".$talent->name."</label></td>
									<td class='highlight-hover'>".$talent->description."</td>
									<td>".$reqs."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$talent->name)."\")'></td>
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
				<table class="table fixed-width" id="physical_trait_pos_table">
					<tr>
						<th>Enabled <input type='checkbox' class="magical-talent-check" checked onclick="checkAll(this, 'magical-talent-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<th>Requirements</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($talents as $talent) {
							$reqs = "";
							if ($talent->type == 'magic_talent' || $talent->type == 'school_talent') {
								// build requirement string
								foreach($talent->requirements as $req_set) {
									$reqs .= "<span class='highlight-hover'>";
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
									$reqs .= "</span>";
								}
								echo 
								"<tr class='table-row' id='row_".$talent->id."'>
									<td class='center'><input id='check_".$talent->id."' class='magical-talent-check' type='checkbox' ".(isset($talent->active) || $counts['magical_talent_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$talent->id."'></td>
									<td class='highlight-hover'><label for='check_".$talent->id."'>".$talent->name."</label></td>
									<td class='highlight-hover'>".$talent->description."</td>
									<td>".$reqs."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$talent->name)."\")'></td>
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
				<table class="table fixed-width" id="physical_trait_pos_table">
					<tr>
						<th>Enabled <input type='checkbox' class="physical-trait-pos-check" checked onclick="checkAll(this, 'physical-trait-pos-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<th class="center">Cost</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($talents as $talent) {
							if ($talent->type == 'physical_trait' && $talent->cost > 0) {
								echo 
								"<tr class='table-row' id='row_".$talent->id."'>
									<td class='center'><input id='check_".$talent->id."' class='physical-trait-pos-check' type='checkbox' ".(isset($talent->active) || $counts['physical_pos_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$talent->id."'></td>
									<td class='highlight-hover'><label for='check_".$talent->id."'>".$talent->name."</label></td>
									<td class='highlight-hover'>".$talent->description."</td>
									<td class='center highlight-hover'>".$talent->cost."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$talent->name)."\")'></td>
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
				<table class="table fixed-width" id="physical_trait_neg_table">
					<tr>
						<th>Enabled <input type='checkbox' class="physical-trait-neg-check" checked onclick="checkAll(this, 'physical-trait-neg-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<th class="center">Bonus</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($talents as $talent) {
							if ($talent->type == 'physical_trait' && $talent->cost < 0) {
								echo 
								"<tr class='table-row' id='row_".$talent->id."'>
									<td class='center'><input id='check_".$talent->id."' class='physical-trait-neg-check' type='checkbox' ".(isset($talent->active) || $counts['physical_neg_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$talent->id."'></td>
									<td class='highlight-hover'><label for='check_".$talent->id."'>".$talent->name."</label></td>
									<td class='highlight-hover'>".$talent->description."</td>
									<td class='center highlight-hover'>".(intval($talent->cost)*-1)."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$talent->name)."\")'></td>
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
				<table class="table fixed-width" id="social_trait_table">
					<tr>
						<th>Enabled <input type='checkbox' class="social-trait-check" checked onclick="checkAll(this, 'social-trait-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($talents as $talent) {
							if ($talent->type == 'social_trait') {
								echo 
								"<tr class='table-row' id='row_".$talent->id."'>
									<td class='center'><input id='check_".$talent->id."' class='social-trait-check' type='checkbox' ".(isset($talent->active) || $counts['social_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$talent->id."'></td>
									<td class='highlight-hover'><label for='check_".$talent->id."'>".$talent->name."</label></td>
									<td class='highlight-hover'>".$talent->description."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$talent->name)."\")'></td>
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
				<table class="table fixed-width" id="morale_trait_table">
					<tr>
						<th>Enabled <input type='checkbox' class="morale-trait-check" checked onclick="checkAll(this, 'morale-trait-check')"></th>
						<th>Name</th>
						<th>Positive State</th>
						<th>Negative State</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($talents as $talent) {
							if ($talent->type == 'morale_trait') {
								$pos_state = explode('Positive State: ', $talent->description)[1];
								$pos_state = explode('; Negative State: ', $pos_state)[0];
								$neg_state = explode('Negative State: ', $talent->description)[1];
								echo 
								"<tr class='table-row' id='row_".$talent->id."'>
									<td class='center'><input id='check_".$talent->id."' class='morale-trait-check' type='checkbox' ".(isset($talent->active) || $counts['morale_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$talent->id."'></td>
									<td class='highlight-hover'><label for='check_".$talent->id."'>".$talent->name."</label></td>
									<td class='highlight-hover'>".$pos_state."</td>
									<td class='highlight-hover'>".$neg_state."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$talent->name)."\")'></td>
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
				<table class="table fixed-width" id="compelling_action_table">
					<tr>
						<th>Enabled <input type='checkbox' class="compelling-action-check" checked onclick="checkAll(this, 'compelling-action-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($talents as $talent) {
							if ($talent->type == 'compelling_action') {
								echo 
								"<tr class='table-row' id='row_".$talent->id."'>
									<td class='center'><input id='check_".$talent->id."' class='compelling-action-check' type='checkbox' ".(isset($talent->active) || $counts['compelling_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$talent->id."'></td>
									<td class='highlight-hover'><label for='check_".$talent->id."'>".$talent->name."</label></td>
									<td class='highlight-hover'>".$talent->description."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$talent->name)."\")'></td>
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
				<table class="table fixed-width" id="profession_table">
					<tr>
						<th>Enabled <input type='checkbox' class="profession-check" checked onclick="checkAll(this, 'profession-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($talents as $talent) {
							if ($talent->type == 'profession') {
								echo 
								"<tr class='table-row' id='row_".$talent->id."'>
									<td class='center'><input id='check_".$talent->id."' class='profession-check' type='checkbox' ".(isset($talent->active) || $counts['profession_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$talent->id."'></td>
									<td class='highlight-hover'><label for='check_".$talent->id."'>".$talent->name."</label></td>
									<td class='highlight-hover'>".$talent->description."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$talent->name)."\")'></td>
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
				<table class="table fixed-width" id="social_background_table">
					<tr>
						<th>Enabled <input type='checkbox' class="social_background-check" checked onclick="checkAll(this, 'social_background-check')"></th>
						<th>Name</th>
						<th>Description</th>
						<!-- <th>Edit</th> -->
					</tr>
					<?php
						foreach($talents as $talent) {
							if ($talent->type == 'social_background') {
								echo 
								"<tr class='table-row' id='row_".$talent->id."'>
									<td class='center'><input id='check_".$talent->id."' class='social_background-check' type='checkbox' ".(isset($talent->active) || $counts['social_background_count'] == 0 ? 'checked' : '')." name='feat_status[]' value='".$talent->id."'></td>
									<td class='highlight-hover'><label for='check_".$talent->id."'>".$talent->name."</label></td>
									<td class='highlight-hover'>".$talent->description."</td>
								</tr>";
								// <td><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$talent->name)."\")'></td>
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
						<li>Adjust which Talents, Traits, and Races are available for your campaign.<br>
						<i class="small">Note: Anything other than Standard or Magical Talents are only available to players during character creation</i></li><br>
					</ul>
					<div class="button-bar">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- invite new players -->
	<div class="modal" id="invite_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
		  <div class="modal-content">
		    <div class="modal-header">
		      <h4 class="modal-title">Invite New Player</h4>
		    </div>
		    <div class="modal-body">

				<p>Enter the email address below of the player you'd like to invite.<br><br>Once they complete the registration process they will automatically be added to your campaign. </p>
				<input class="form-control" type="email" id="invite_email">

		    	<div class="button-bar">
		        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
		        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="sendInvite()">Invite</button>
		    	</div>

		    </div>
		  </div>
		</div>
	</div>

	<!-- add/remove players modal -->
	<div class="modal" id="edit_players_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md modal-dialog-centered" role="document">
		  <div class="modal-content">
		    <div class="modal-header">
		      <h4 class="modal-title">Edit Party</h4>
		    </div>
		    <div class="modal-body">

				<div class="panel">
					<table class="table center">
						<tr>
							<th>Party Member?</th>
							<th>Player</th>
							<th>Characters</th>
						</tr>
						<?php
							foreach($logins as $login) {
								echo "
								<tr>
									<td class='select-row'>
										<label class='toggle-switchy' for='select_".$login['id']."' data-size='sm' data-text='false'>
											<input class='active-checkbox' type='checkbox' id='select_".$login['id']."' ".($login['active'] ? 'checked' : '')." ".($login['admin'] ? 'disabled' : '').">
											<span class='toggle'>
												<span class='switch'></span>
											</span>
										</label>
									</td>
									<td>".$login['email']."</td>
									<td>".($login['characters'] == "" ? "<strong>NONE</strong>" : $login['characters'])."</td>
								</tr>
								";
							}
						?>
					</table>
				</div>

		    	<div class="button-bar">
		        	<button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
		        	<button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#invite_modal"><i class="fa-regular fa-paper-plane"></i> Invite New Players</button>
		    	</div>
		    </div>
		  </div>
		</div>
	</div>

	<!-- award xp modal -->
	<div class="modal" id="xp_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md modal-dialog-centered" role="document">
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
								<!-- <th>Chips</th> -->
								<th>Total</th>
							</tr>
							<?php
								foreach($users as $user) {

									// make sure player is active in campaign - user['login_id'] is in login_campaigns
									$active = false;
									foreach($login_campaigns as $login) {
										$active = $active || $login['login_id'] == $user['login_id'];
									}
									if (!$active) {
										continue;
									}

									echo "
									<tr class='xp-row table-row' id='".$user['id']."'>
										<td class='select-row'>
										<label class='toggle-switchy' for='select_".$user['id']."' data-size='sm' data-text='false'>
											<input class='xp-checkbox' type='checkbox' id='select_".$user['id']."' checked>
											<span class='toggle'>
												<span class='switch'></span>
											</span>
										</label>
										</td>
										<td class='name-row'><label for='select_".$user['id']."' class='xp-label min'>".$user['character_name']."</label></td>
										<td>
											<span class='award' id='award_".$user['id']."'>0</span>
										</td>
										<td><input type='checkbox' class='costume-chk' id='costume_".$user['id']."'></td>
										<!-- <td><input type='number' value='0' min='0' class='form-control chips' id='chips_".$user['id']."'></td> -->
										<td>
											<span class='total' id='total_".$user['id']."'>0</span>
										</td>
									</tr>";
								}
							?>
						</table>
					</div>

					<div class="center note">
						<i class="small">Note: XP that has been awarded to characters will be added to their total the next time that player views their character.</i>
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
		<!-- <p class="link col-md-4" data-toggle="modal" data-target="#suggestion_modal"><span class="glyphicon glyphicon-envelope"></span> Suggestion Box</p> -->
		<p class=" col-md-4">© <?php echo date("Y"); ?> CrabAgain.com</p>
	</div>

</body>

<!-- JavaScript -->
<script async src="https://www.google.com/recaptcha/api.js?render=6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi"></script>
<script src="/assets/jquery/jquery-3.5.1.min.js"></script>
<script src="/assets/bootstrap/js/bootstrap.min.js"></script>
<script src="/assets/jquery/jquery-ui-1.12.1.min.js"></script>
<script src="/assets/admin_edit_talents.js"></script>
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

	$(".costume-chk").on("change", function() {
		adjustTotals();
	});

	$(".xp-checkbox").on("change", function() {
		adjustTotals();
		var id = this.id.split("select_")[1];
		$("#mobile_select_"+id).prop("checked", $(this).is(":checked"));
		// $("#chips_"+id).attr("disabled", !$(this).is(":checked"));
		$("#costume_"+id).attr("disabled", !$(this).is(":checked"));
	});

	$(".xp-checkbox-mobile").on("change", function() {
		var id = this.id.split("mobile_select_")[1];
		$("#select_"+id).trigger("click");
	});

	$(".active-checkbox").on("change", function() {
		let login_id = this.id.split("select_")[1];
		let campaign_id = $("#campaign_id").val();
		if ($(this).is(":checked")) {
			// insert login_campaign
			$.ajax({
				url: '/scripts/insert_database_object.php',
				data: { 'table' : 'login_campaign', 'data' : {'login_id':login_id, 'campaign_id':campaign_id}, 'columns' : ['login_id', 'campaign_id'], 'user_id' : "" },
				ContentType: "application/json",
				type: 'POST',
				success: function(response) {
					// console.log(response);
					let login_campaign = {'id':response, 'campaign_id':campaign_id, 'login_id':login_id};
					login_campaigns.push(login_campaign);
					updateTables();
				}
			});
		} else {
			// delete login_campaign
			var id = 0;
			for (var i in login_campaigns) {
				if (login_campaigns[i]['login_id'] == login_id) {
					id = login_campaigns[i]['id'];
					login_campaigns.splice(i,1);
					updateTables();
					break;
				}
			}
			$.ajax({
				url: '/scripts/delete_database_object.php',
				data: { 'table' : 'login_campaign', 'id' : id },
				ContentType: "application/json",
				type: 'POST',
				success: function(response) {
					// console.log(response);
				}
			});
		}
	});

	function updateTables() {

		$(".user-row").remove();
		$(".xp-row").remove();

		for (var i in users) {
			let user = users[i];
			var active = false;
			for (var j in login_campaigns) {
				active = active || login_campaigns[j]['login_id'] == user['login_id'];
			}
			if (!active) {
				continue;
			}

			// get character level
			let levels = [];
			var xp_total = 0;
			var level = 1;
			for (var j = 1; j < 25; j++) {
				xp_total += 20 * j;
				levels.push(xp_total);
			}
			for (var j in levels) {
				if (user['xp'] >= levels[j]) {
					level += 1;
				}
			}

			// get resilience, damage and wounds
			let resilience = user['fortitude'] >= 0 ? 3 + Math.floor(user['fortitude']/2) : 3 + Math.ceil(user['fortitude']/3);
			var damage = user['damage'];
			var wounds = 0;
			while (damage >= resilience) {
				wounds += 1;
				damage -= resilience;
			}

			// get size modifier, dodge, defend, toughness
			let size_modifier = user['size'] == "Small" ? 2 : (user['size'] == "Large" ? -2 : 0);
			var dodge = user['agility'] >= 0 ? Math.floor(user['agility']/2) : (Math.ceil(user['agility']/3) == 0 ? 0 : Math.ceil(user['agility']/3));
			dodge = dodge + size_modifier + user['dodge_mod'];
			let toughness = user['strength'] >= 0 ? Math.floor(user['strength']/2) : (Math.ceil(user['strength']/3) == 0 ? 0 : Math.ceil(user['strength']/3));
			let defend = parseInt(user['agility']) + 10 + parseInt(size_modifier) + parseInt(user['defend_mod']);

			// update user table
			let row_user = $('<tr />', {
				'class': 'user-row table-row',
			}).appendTo($(".user-table"));

			$('<td />', {
				'class': "name-row",
				'html': "<a href='/?campaign="+campaign['id']+"&user="+user['id']+"'><strong>"+user['character_name']+"</strong></a>"
			}).appendTo(row_user);

			$('<td />', {
				'id': "xp_"+user['id'],
				'html': user['xp'] + (user['xp_award'] == 0 ? '' : (user['xp_award'] > 0 ? " (+"+user['xp_award']+")" : " ("+user['xp_award']+")"))
			}).appendTo(row_user);

			$('<td />', {
				'id': "level_"+user['id'],
				'html': level
			}).appendTo(row_user);

			$('<td />', {
				'html': "<input class='short-input form-control' id='damage_"+user['id']+"' min='0' type='number' value='"+damage+"'> / "+resilience
			}).appendTo(row_user);

			$('<td />', {
				'html': "<input class='short-input form-control' id='wounds_"+user['id']+"' max='3' min='0' type='number' value='"+wounds+"'> / 3"
			}).appendTo(row_user);

			$('<td />', {
				'html': user['primary']+"/"+user['secondary']
			}).appendTo(row_user);

			$('<td />', {
				'html': toughness + (user['toughness_bonus'] > 0 ? " (+"+user['toughness_bonus']+")" : "")
			}).appendTo(row_user);

			$('<td />', {
				'html': defend + (user['defend_bonus'] > 0 ? " (+"+user['defend_bonus']+")" : "")
			}).appendTo(row_user);

			$('<td />', {
				'html': dodge
			}).appendTo(row_user);

			$('<td />', {
				'html': user['awareness']
			}).appendTo(row_user);

			$('<td />', {
				'html': user['vitality']
			}).appendTo(row_user);


			// update xp table
			let row_xp = $('<tr />', {
				'id': user['id'],
				'class': 'xp-row table-row',
			}).appendTo($(".xp-table"));

			$('<td />', {
				'class': 'select-row',
				'html': "<label class='toggle-switchy' for='select_"+user['id']+"' data-size='sm' data-text='false'>"+
				"<input class='xp-checkbox' type='checkbox' id='select_"+user['id']+"' checked>"+
				"<span class='toggle'><span class='switch'></span></span></label>"
			}).appendTo(row_xp);

			$('<td />', {
				'class': 'name-row',
				'html': "<label for='select_"+user['id']+"' class='xp-label min'>"+user['character_name']+"</label>"
			}).appendTo(row_xp);

			$('<td />', {
				'html': "<span class='award' id='award_"+user['id']+"'>0</span>"
			}).appendTo(row_xp);

			$('<td />', {
				'html': "<input type='checkbox' class='costume-chk' id='costume_"+user['id']+"'>"
			}).appendTo(row_xp);

			$('<td />', {
				'html': "<span class='total' id='total_"+user['id']+"'>0</span>"
			}).appendTo(row_xp);

		}

	}

	// $(".chips").on("input", function() {
	// 	// get number value
	// 	var num = parseInt($(this).val());
	// 	if (isNaN(num)) {
	// 		num = 0;
	// 	}
	// 	$(this).val(num);
	// 	adjustTotals();
	// });

	function adjustTotals() {
		// select_id
		$(".xp-row").each(function(){
			var is_mobile = $("#select_"+this.id).is(":hidden");
			var selected = is_mobile ? $("#mobile_select_"+this.id).is(":checked") : $("#select_"+this.id).is(":checked");
			var level = parseInt($("#level_"+this.id).html());
			var base = parseInt($("#award_"+this.id).html());
			var costume = $("#costume_"+this.id).is(":checked");
			// var chips = parseInt($("#chips_"+this.id).val());
			let chips = 0;
			$("#total_"+this.id).html( selected ? base + (costume ? level : 0) + (chips * level) : 0 );
		});
	}

	// get feat counts
	var total_count = <?php echo json_encode($total_count); ?>;
	var counts = <?php echo json_encode($counts); ?>;
	if (total_count != 0) {
		if (counts['physical_pos_count'] == 0) {
			$("#physical_trait_pos_toggle").trigger("click");
		}
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
		if (counts['magical_talent_count'] == 0) {
			$("#magical_talents_toggle").trigger("click");
		}
		if (counts['race_count'] == 0) {
			$("#race_toggle").trigger("click");
		}
	}

	// get feat list and requirements
	var campaign = <?php echo json_encode($campaign); ?>;
	var talents = <?php echo json_encode($talents); ?>;
	var logins = <?php echo json_encode($logins); ?>;
	var users = <?php echo json_encode($users); ?>;
	var login_campaigns = <?php echo json_encode($login_campaigns); ?>;

	// set feat list for autocomplete
	var feats = [];
	for (var i in talents) {
		if (talents[i]['type'] == 'standard_talent') {
			feats.push(talents[i]['name']);
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

	// on modal close, reset inputs
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

	// invite new player to campaign
	function sendInvite() {
		// get invite email
		let email = $("#invite_email").val();
		// check if it's a valid email
		if (validateEmail(email)) {
			// submit to ajax
			$.ajax({
				url: '/scripts/send_invite.php',
				data: {'email':email, 'campaign_id':$("#campaign_id").val(), 'login_id':$("#login_id").val()},
				ContentType: "application/json",
				type: 'POST',
				success: function(response){
					if (response == 1) {
						alert("Your invitation has been sent");
					}
				}
			});
		} else {
			alert("Please enter a valid email address");
		}
	}

	function validateEmail(email) {
		return String(email)
		.toLowerCase()
		.match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
	};

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

	// hide/show scroll to top button
	window.onscroll = function() {
		if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
			$("#scroll_top").removeClass("no-vis")
		} else {
			$("#scroll_top").addClass("no-vis");
		}
	};

	// save settings on input change
	$(document).ready(function() {
		$("input:checkbox").change(function(){
			saveCampaignSettings();
		});
	});

</script>