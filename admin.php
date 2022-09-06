<?php

	// establish database connection
	include_once('db_config.php');
	include_once('keys.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// make sure campaign ID is set
	if (!isset($_GET["campaign"])) {
		// redirect to campaign select page
		header('Location: /select_campaign.php');
	}
	$campaign;
	$sql = "SELECT * FROM campaign WHERE id = ".$_GET["campaign"];
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			$campaign = $row;
		}
	}

	// get characters
	$users = [];
	$sql = "SELECT * FROM user WHERE campaign_id = ".$_GET["campaign"];
	$result_u = $db->query($sql);
	if ($result_u) {
		while($row_u = $result_u->fetch_assoc()) {
			$user = $row_u;

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
			if ($user['weapon_1'] != NULL && $user['weapon_1'] != "") {
				$sql = "SELECT * FROM user_weapon WHERE user_id = ".$user["id"]." AND name = '".$user['weapon_1']."'";
				$result_w = $db->query($sql);
				if ($result_w) {
					while($row_w = $result_w->fetch_assoc()) {
						$defend_bonus += $row_w['defend'] == NULL ? 0 : $row_w['defend'];
					}
				}
			}
			if ($user['weapon_2'] != NULL && $user['weapon_2'] != "") {
				$sql = "SELECT * FROM user_weapon WHERE user_id = ".$user["id"]." AND name = '".$user['weapon_2']."'";
				$result_w = $db->query($sql);
				if ($result_w) {
					while($row_w = $result_w->fetch_assoc()) {
						$defend_bonus += $row_w['defend'] == NULL ? 0 : $row_w['defend'];
					}
				}
			}
			if ($user['weapon_3'] != NULL && $user['weapon_3'] != "") {
				$sql = "SELECT * FROM user_weapon WHERE user_id = ".$user["id"]." AND name = '".$user['weapon_3']."'";
				$result_w = $db->query($sql);
				if ($result_w) {
					while($row_w = $result_w->fetch_assoc()) {
						$defend_bonus += $row_w['defend'] == NULL ? 0 : $row_w['defend'];
					}
				}
			}
			$user['defend_bonus'] = $defend_bonus;

			array_push($users, $user);
		}
	}
  
	// get feat list
	$feats = [];
	$sql = "SELECT * FROM feat_or_trait ORDER BY name";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($feats, $row);
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
		if ($feat['type'] == 'feat') {
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
	.glyphicon-plus-sign {
		width: 100%;
		text-align: center;
		margin-bottom: 20px;
	}
	.modal label {
		margin-top: 15px;
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
		display: none;
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
		width: 75px;
		background-color: #e6e6e6;
		border: 1px solid black;
		border-radius: 5px;
	}
	#home .fa {
		position: absolute;
		top: 15px;
		left: 22px;
		font-size: 30px;
	}
	#home p {
		position: absolute;
		top: 45px;
		left: 15px;
		font-weight: bold;
		text-transform: uppercase;
	}
	.btn-wrapper {
		text-align: center;
	}
	#xp_btn {
		margin-bottom: 15px;
	}
	.xp-label {
		margin-left: 10px;
		max-width: 190px;
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
	.award {
		float: right;
		margin-top: 10px;
		width: 50px;
	}
	.name-row {
		max-width: 200px;
	}
</style>

<body>
	<div class="container">

		<input type="hidden" id="campaign_id" value="<?php echo $campaign['id'] ?>">
		<input type="hidden" id="admin_password" value="<?php echo isset($_GET['auth']) ? $_GET['auth'] : '' ?>">

		<!-- button - scroll to top of page -->
		<button id="scroll_top" class="no-vis"><span class="glyphicon glyphicon-arrow-up" onclick="scrollToTop()"></span></button>

		<!-- home button -->
		<div id="home_wrapper">
			<a id="home" href="<?php echo '/?campaign='.$campaign['id'] ?>"><i class="fa fa-brands fa-fort-awesome"></i><p>Home</p></a>
		</div>

		<select class="form-control section-select" id="section_links">
			<option value="">Standard Feats</option>
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
					<th></th>
					<th>XP</th>
					<th>Lvl</th>
					<th>Res</th>
					<th>Wnd</th>
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

						// get size modifier
						$size_modifier = $user['size'] == "Small" ? 2 : ($user['size'] == "Large" ? -2 : 0);

						// get dodge
						$dodge = $user['agility'] >= 0 ?
								floor($user['agility']/2) :
								(ceil($user['agility']/3) == 0 ? 0 : ceil($user['agility']/3));
						$dodge += $size_modifier;

						// get toughness
						$toughness = $user['strength'] >= 0 ?
								floor($user['strength']/2) :
								(ceil($user['strength']/3) == 0 ? 0 : ceil($user['strength']/3));
						$toughness += $user['toughness_bonus'];

						// get defend
						$defend = isset($user) ? 10 + $user['agility'] : 10;
						$defend += $size_modifier;
						$defend += $user['defend_bonus'];

						echo 
						"<tr class='table-row'>
							<td class='name-row'><a href='/?campaign=".$campaign['id']."&user=".$user['id']."'><strong>".$user['character_name']."</strong></a></td>
							<td id='xp_".$user['id']."'>".$user['xp'].($user['xp_awarded'] == 0 ? '' : ($user['xp_awarded'] > 0 ? ' (+'.$user['xp_awarded'].')' : ' ('.$user['xp_awarded'].')'))."</td>
							<td id='level_".$user['id']."'>".$level."</td>
							<td><input class='short-input' id='damage_".$user['id']."' min='0' type='number' value='".$user['damage']."'> / ".$resilience."</td>
							<td><input class='short-input' id='wounds_".$user['id']."' max='3' min='0' type='number' value='".$user['wounds']."'> / 3</td>
							<td>".$toughness."</td>
							<td>".$defend."</td>
							<td>".$dodge."</td>
							<td>".$user['awareness']."</td>
							<td>".$user['vitality']."</td>
						</tr>";
					}
				?>
			</table>
		</div>

		<!-- distrubute xp modal -->
		<div class="btn-wrapper" <?php if(count($users) == 0) { echo 'hidden'; } ?>>
			<button id="xp_btn" data-toggle="modal" data-target="#xp_modal">Award XP</button>
		</div>

		<h4 class="table-heading" id="section_feat">Standard Feats</h4>
		<span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('feat')"></span>
		<div class="panel panel-default">
			<table class="table">
				<tr>
					<th>Name</th>
					<th>Description</th>
					<th>Requirements</th>
					<!-- <th>Edit</th> -->
					<!-- <th>Available</th> -->
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
							"<tr class='table-row'>
								<td>".$feat['name']."</td>
								<td>".$feat['description']."</td>
								<td>".$reqs."</td>
								<td hidden><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
								<td class='center' hidden><input type='checkbox' checked></td>
							</tr>";
						}
					}
				?>
			</table>
		</div>

		<div class="title">
			<h4 class="table-heading" id="section_physical_trait_pos">Physical Traits (Positive)</h4>
			<label class="toggle-switchy" for="physical-trait-pos-toggle" data-size="sm" data-text="false">
				<input checked type="checkbox" id="physical-trait-pos-toggle" checked onclick="enable(this, 'physical-trait-pos-check')">
				<span class="toggle">
					<span class="switch"></span>
				</span>
			</label>
		</div>
		<span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('physical_trait_pos')"></span>
		<div class="panel panel-default">
			<table class="table">
				<tr>
					<th>Name</th>
					<th>Description</th>
					<th class="center">Cost</th>
					<!-- <th>Edit</th> -->
					<!-- <th>Available <input type='checkbox' class="physical-trait-pos-check" checked onclick="checkAll(this, 'physical-trait-pos-check')"></th> -->
				</tr>
				<?php
					foreach($feat_list as $feat) {
						if ($feat['type'] == 'physical_trait' && $feat['cost'] > 0) {
							echo 
							"<tr class='table-row'>
								<td>".$feat['name']."</td>
								<td>".$feat['description']."</td>
								<td class='center'>".$feat['cost']."</td>
								<td hidden><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
								<td class='center' hidden><input class='physical-trait-pos-check' type='checkbox' checked></td>
							</tr>";
						}
					}
				?>
			</table>
		</div>

		<div class="title">
			<h4 class="table-heading" id="section_physical_trait_neg">Physical Traits (Negative)</h4>
			<label class="toggle-switchy" for="physical-trait-neg-toggle" data-size="sm" data-text="false">
				<input checked type="checkbox" id="physical-trait-neg-toggle" checked onclick="enable(this, 'physical-trait-neg-check')">
				<span class="toggle">
					<span class="switch"></span>
				</span>
			</label>
		</div>
		<span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('physical_trait_neg')"></span>
		<div class="panel panel-default">
			<table class="table">
				<tr>
					<th>Name</th>
					<th>Description</th>
					<th class="center">Bonus</th>
					<!-- <th>Edit</th> -->
					<!-- <th>Available <input type='checkbox' class="physical-trait-neg-check" checked onclick="checkAll(this, 'physical-trait-neg-check')"></th> -->
				</tr>
				<?php
					foreach($feat_list as $feat) {
						if ($feat['type'] == 'physical_trait' && $feat['cost'] < 0) {
							echo 
							"<tr class='table-row'>
								<td>".$feat['name']."</td>
								<td>".$feat['description']."</td>
								<td class='center'>".(intval($feat['cost'])*-1)."</td>
								<td hidden><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
								<td class='center' hidden><input class='physical-trait-neg-check' type='checkbox' checked></td>
							</tr>";
						}
					}
				?>
			</table>
		</div>

		<div class="title">
			<h4 class="table-heading" id="section_social_trait">Social Traits</h4>
			<label class="toggle-switchy" for="social-trait-toggle" data-size="sm" data-text="false">
				<input checked type="checkbox" id="social-trait-toggle" checked onclick="enable(this, 'social-trait-check')">
				<span class="toggle">
					<span class="switch"></span>
				</span>
			</label>
		</div>
		<span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('social_trait')"></span>
		<div class="panel panel-default">
			<table class="table">
				<tr>
					<th>Name</th>
					<th>Description</th>
					<!-- <th>Edit</th> -->
					<!-- <th>Available <input type='checkbox' class="social-trait-check" checked onclick="checkAll(this, 'social-trait-check')"></th> -->
				</tr>
				<?php
					foreach($feat_list as $feat) {
						if ($feat['type'] == 'social_trait') {
							echo 
							"<tr class='table-row'>
								<td>".$feat['name']."</td>
								<td>".$feat['description']."</td>
								<td hidden><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
								<td class='center' hidden><input class='social-trait-check' type='checkbox' checked></td>
							</tr>";
						}
					}
				?>
			</table>
		</div>

		<div class="title">
			<h4 class="table-heading" id="section_morale_trait">Morale Traits</h4>
			<label class="toggle-switchy" for="morale-trait-toggle" data-size="sm" data-text="false">
				<input checked type="checkbox" id="morale-trait-toggle" checked onclick="enable(this, 'morale-trait-check')">
				<span class="toggle">
					<span class="switch"></span>
				</span>
			</label>
		</div>
		<span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('morale_trait')"></span>
		<div class="panel panel-default">
			<table class="table">
				<tr>
					<th>Name</th>
					<th>Positive State</th>
					<th>Negative State</th>
					<!-- <th>Edit</th> -->
					<!-- <th>Available <input type='checkbox' class="morale-trait-check" checked onclick="checkAll(this, 'morale-trait-check')"></th> -->
				</tr>
				<?php
					foreach($feat_list as $feat) {
						if ($feat['type'] == 'morale_trait') {
							$pos_state = explode('Positive State: ', $feat['description'])[1];
							$pos_state = explode('; Negative State: ', $pos_state)[0].".";
							$neg_state = explode('Negative State: ', $feat['description'])[1];
							echo 
							"<tr class='table-row'>
								<td>".$feat['name']."</td>
								<td>".$pos_state."</td>
								<td>".$neg_state."</td>
								<td hidden><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
								<td class='center' hidden><input class='morale-trait-check' type='checkbox' checked></td>
							</tr>";
						}
					}
				?>
			</table>
		</div>

		<div class="title">
			<h4 class="table-heading" id="section_compelling_action">Compelling Actions</h4>
			<label class="toggle-switchy" for="compelling-action-toggle" data-size="sm" data-text="false">
				<input checked type="checkbox" id="compelling-action-toggle" checked onclick="enable(this, 'compelling-action-check')">
				<span class="toggle">
					<span class="switch"></span>
				</span>
			</label>
		</div>
		<span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('compelling_action')"></span>
		<div class="panel panel-default">
			<table class="table">
				<tr>
					<th>Name</th>
					<th>Description</th>
					<!-- <th>Edit</th> -->
					<!-- <th>Available <input type='checkbox' class="compelling-action-check" checked onclick="checkAll(this, 'compelling-action-check')"></th> -->
				</tr>
				<?php
					foreach($feat_list as $feat) {
						if ($feat['type'] == 'compelling_action') {
							echo 
							"<tr class='table-row'>
								<td>".$feat['name']."</td>
								<td>".$feat['description']."</td>
								<td hidden><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
								<td class='center' hidden><input class='compelling-action-check' type='checkbox' checked></td>
							</tr>";
						}
					}
				?>
			</table>
		</div>

		<div class="title">
			<h4 class="table-heading" id="section_profession">Professions</h4>
			<label class="toggle-switchy" for="profession-toggle" data-size="sm" data-text="false">
				<input checked type="checkbox" id="profession-toggle" checked onclick="enable(this, 'profession-check')">
				<span class="toggle">
					<span class="switch"></span>
				</span>
			</label>
		</div>
		<span class="glyphicon glyphicon-plus-sign" onclick="newFeatModal('profession')"></span>
		<div class="panel panel-default">
			<table class="table">
				<tr>
					<th>Name</th>
					<th>Description</th>
					<!-- <th>Edit</th> -->
					<!-- <th>Available <input type='checkbox' class="profession-check" checked onclick="checkAll(this, 'profession-check')"></th> -->
				</tr>
				<?php
					foreach($feat_list as $feat) {
						if ($feat['type'] == 'profession') {
							echo 
							"<tr class='table-row'>
								<td>".$feat['name']."</td>
								<td>".$feat['description']."</td>
								<td hidden><span class='glyphicon glyphicon-edit' onclick='editFeat(\"".str_replace('\'','',$feat['name'])."\")'></td>
								<td class='center' hidden><input class='profession-check' type='checkbox' checked></td>
							</tr>";
						}
					}
				?>
			</table>
		</div>

	</div>

	<!-- welcome modal -->
	<div class="modal" id="welcome_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Settings</h4>
				</div>
				<div class="modal-body">
					Welcome to your campaign settings page! From here you can:<br><br>
					<p>- See an overview of your players' stats</p>
					<p>- Distribute XP to your players</p>
					<p>- Adjust which feats/traits are available to your players, or you can disable specific trait categories entirely (note, anything other than Standard Feats are only available to players during initial character creation)</p>
					<p>- Create new feats/traits</p>
					<div class="button-bar">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- award xp modal -->
	<div class="modal" id="xp_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Award XP</h4>
				</div>
				<div class="modal-body">
					<input type="checkbox" class="xp-checkbox" id="select_all"><label class="xp-label" for="select_all">SELECT ALL</label>
					<?php
						foreach($users as $user) {
							echo 
							"<div>
							<input class='xp-checkbox' type='checkbox' id='".$user['id']."'>
							<label class='xp-label' for='".$user['id']."'>".$user['character_name']."</label>
							<input type='number' value='0' class='award' id='award_".$user['id']."'>
							</div>";
						}
					?>
					<div class="row">
						<div class="col-xs-2 no-pad">
							<label class="control-label">XP:</label>
						</div>
						<div class="col-xs-3 no-pad">
							<input class="form-control xp-input" type="number" id="xp_val">
						</div>
						<div class="col-xs-7 no-pad">
							<button class="xp-btn" onclick="awardXP()">+ TO SELECTED</button>
						</div>
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
							<button type="button" class="btn btn-primary" onclick="newFeat()" id="update_feat_btn">Ok</button>
							<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
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

	<!-- GM edit modal -->
	<div class="modal" id="gm_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
		  <div class="modal-content">
		    <div class="modal-header">
		      <h4 class="modal-title">Admin Settings</h4>
		    </div>
		    <div class="modal-body">
		    	<h4 class="control-label center">What's the secret word?</h4>
		    	<input class="form-control" type="text" id="gm_password">
		    	<div class="button-bar">
		        	<button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
		        	<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script async src="https://www.google.com/recaptcha/api.js?render=6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript">

	$(".table-row").hover(function(){
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
		$(".xp-checkbox").prop("checked", this.checked);
	});

	var users = <?php echo json_encode($users); ?>;
	var campaign = <?php echo json_encode($campaign); ?>;

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
		$(".award").val("0");
		$("#xp_val").val("");
		$(".xp-checkbox").prop("checked", false);
	});
	$("#gm_modal").on('shown.bs.modal', function(){
		$("#gm_password").focus();
	});
	$("#gm_modal").on('hidden.bs.modal', function(){
		GMModalClose();
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
				$("#update_feat_btn").html("Update");
				$("#feat_id").val(feat_list[i]['id']);
				$("#feat_type_val").val(feat_list[i]['type']);
				// fill modal values and launch modal
				$("#feat_name_val").val(feat_list[i]['name']);
				if (feat_list[i]['type'] != "morale_trait") {
					$("#feat_description_val").val(feat_list[i]['description']);
				} else {
					var pos_state = feat_list[i]['description'].split("Positive State: ")[1].split("; Negative State")[0]+".";
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
		var conf = ($("#feat_id").val() != undefined && $("#feat_id").val() != "") ?
			confirm("Are you sure you want to update this feat?") : confirm("Are you sure you want to create a new feat?");
		if (conf) {
			// append admin password to form
		    $('<input />', {
		    	'type': 'hidden',
		    	'value': campaign['admin_password'],
		    	'name': 'admin_password'
		    }).appendTo($("#new_feat_form"));
			// submit form via ajax
			$.ajax({
                url: 'feat_submit.php',
                type: 'POST',
                data: $("#new_feat_form").serialize(),
                success:function(result){
                	// TODO can also get auth value from admin_password.val?
                	if (result != "") {
                		// reload page
                		window.location.replace("/admin.php?campaign="+$('#campaign_id').val()+"&auth="+result);
                	}
                }
            });
		}
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
				var current = $("#award_"+this.id).val();
				$("#award_"+this.id).val(parseInt(current) + parseInt(xp));
			}
		});
	}

	// update xp award value in table and database
	function updateXP() {
		var conf = confirm("Award XP to characters?");
		if (conf) {
			var users = [];
			var xp = [];
			var awards = [];
			$(".xp-checkbox").each(function(){
				if (!isNaN(this.id)) {
					// get award value for user
					var award = parseInt($("#award_"+this.id).val());
					var text_val = $("#xp_"+this.id).html();
					var parts = text_val.split(" (");
					var xp_val = parts.length > 1 ? parseInt(parts[0]) : parseInt(text_val);
					award += parts.length > 1 ? parseInt(parts[1]) : 0;
					// update table display
					$("#xp_"+this.id).html(award == 0 ? xp_val : xp_val + (award > 0 ? " (+"+award+")" : " ("+award+")"));
					users.push(this.id);
					xp.push(xp_val)
					awards.push(award)
				}
			});
			$("#xp_modal").modal("hide");
			// write new xp values to database
			$.ajax({
			  url: 'update_xp.php',
			  data: { 'users' : users, 'xp' : xp, 'awards' : awards, 'attribute_pts' : [] },
			  ContentType: "application/json",
			  type: 'POST',
			  success: function(response){
			  	// no action
			  }
			});
		}
	}

	// check admin password for settings page
	function GMModalClose() {
		// check password
		var password =  $("#gm_password").val();
		var hashed_password = $("#admin_password").val();
		$("#gm_password").val("");
		// no password entered - return home
		if (hashed_password == "" && password == "") {
			window.location.href = "/?campaign="+$('#campaign_id').val();
			return;
		}
		// check admin_password
		$.ajax({
		  url: 'check_admin_password.php',
		  data: { 'password' : password.toLowerCase().trim(), 'admin_password' : campaign['admin_password'], 'hashed_password' : hashed_password },
		  ContentType: "application/json",
		  type: 'POST',
		  success: function(response){
		  	if (response != 1) {
		  		// bad password - return home
				window.location.href = "/?campaign="+$('#campaign_id').val();
		  	} else {
		  		// set url auth value
		  		if ($("#admin_password").val() == "") {
		  			window.location.replace("/admin.php?campaign="+$('#campaign_id').val()+"&auth="+campaign['admin_password']);
		  		}
				// new campaign - show information dialog
				if (users.length == 0) {
					$("#welcome_modal").modal("show");
				}
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
		if ($("#admin_password").val() == "") {
			$("#gm_modal").modal("show");
		} else {
			GMModalClose();
		}
	});
	$(window).bind("unload", function() {
		// make sure admin password modal shows when page loads
	});

</script>