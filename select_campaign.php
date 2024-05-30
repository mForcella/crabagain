<?php

	session_set_cookie_params(604800);
	ini_set('session.cookie_lifetime', 604800);
	ini_set('session.gc_maxlifetime', 604800);
	session_start();

	// check for logout
	if (isset($_POST['logout'])) {
	  	session_destroy();
	  	header('Location: /login.php');
	  	exit();
	}

	// make sure we are logged in - check for existing session
	if (!isset($_SESSION['login_id'])) {
        header('Location: /login.php');
	  	exit();
	}
	$login_id = $_SESSION['login_id'];

	// establish database connection
	include_once('config/db_config.php');
	include_once('config/keys.php');
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

	// get full list of campaign names
	$campaign_names = [];
	$sql = "SELECT name from campaign WHERE 1";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($campaign_names, $row['name']);
		}
	}

	// show available campaigns for login_id
	$campaign_ids = [];
	$login_campaigns = [];
	$sql = "SELECT * from login_campaign WHERE login_id = $login_id";
	// $sql = "SELECT * from login_campaign WHERE 1";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($campaign_ids, $row['campaign_id']);
			array_push($login_campaigns, $row);
		}
	}
	$campaigns = [];
	$sql = "SELECT * from campaign WHERE id IN (".implode(',',$campaign_ids).")";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($campaigns, $row);
		}
	}

	// get user list for redirecting to character pages on campaign select
	$user_ids = [];
	$sql = "SELECT id, campaign_id from user WHERE login_id = $login_id";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($user_ids, $row);
		}
	}

	// get list of all login users for adding to new campaigns
	$users = [];
	$sql = "SELECT id,email from login WHERE 1";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($users, $row);
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
	<title>Select Campaign</title>
	<link rel="icon" type="image/png" href="/assets/image/favicon-pentacle.ico"/>

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

	.container {
		width: 300px;
		text-align: center;
		margin-top: 100px;
		padding-top: 20px;
		position: relative;
		border: 2px solid #999999;
		border-radius: 5px;
		border-style: groove;
		background-color: #f2f2f2;
	}
	.container:before {
		background-image: url('assets/image/texture.jpeg');
		background-size: cover;
		content: ' ';
		display: block;
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		opacity: 0.3;
	}
	.container label {
		font-size: 1.2em;
	}
	.container button {
		margin: 30px;
	}
	.container select, .container button {
		position: relative;
	}
	.toggle {
		border: 1px solid black;
		border-radius: 10px !important;
	}
	.switch {
		border-radius: 10px !important;
	}
	.toggle-switchy {
		margin-right: 20px;
		margin-bottom: 5px;
		transform: scale(0.7);
	}
	.nav-items {
		position: absolute;
		top: 30px;
		right: 50px;
		text-align: right;
	}
	#add_users_modal .modal-sm {
		max-width: 400px !important;
	}
</style>

<body>
	<input type="hidden" id="login_id" value="<?php echo $login_id; ?>">

	<div class="nav-items">
		<span class="nav-item-label"><i class="fa-solid icon-crab custom-icon"></i> <?php echo explode("@", $login['email'])[0]; ?></span>
		<form id="logout" method="post">
			<button class="glyphicon" type="submit" name="logout"><span class="nav-item-label"><i class="fa-solid icon-log custom-icon"></i> Logout</span></button>
		</form>
	</div>

	<div class="container">
		<label class="control-label">Select your campaign</label>
		<select class="form-control" onchange="setCampaign(this)">
			<?php
				if (count($campaigns) == 0) {
					echo '<option value="" disabled selected>No campaigns available</option>';
				} else {
					echo '<option value="" disabled selected>Select your campaign</option>';
				}
				foreach($campaigns as $option) {
					echo '<option value='.$option['id'].'>'.$option['name'].'</option>';
				}
			?>
		</select>
		<button class="btn btn-primary" type="button" data-toggle="modal" data-target="#new_campaign_modal"><i class="fa-solid fa-plus"></i> New Campaign!</button>
		<!-- TODO add option to request to join a campaign? -->
	</div>

	<!-- new campaign modal -->
	<div class="modal" id="new_campaign_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content searching-prompt">
				<div class="modal-header">
					<h4 class="modal-title">New Campaign</h4>
				</div>
				<div class="modal-body">
					<label class="control-label">What's the name of your campaign?</label>
					<input class="form-control" type="text" id="campaign_name">
					<div class="button-bar">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-primary" data-dismiss="modal" disabled id="next_btn" data-toggle="modal" data-target="#add_users_modal">Next</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- add users modal -->
	<div class="modal" id="add_users_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content searching-prompt">
				<div class="modal-header">
					<h4 class="modal-title">New Campaign</h4>
				</div>
				<div class="modal-body">

					<label class="control-label">Go ahead and select some players to add to your campaign. We'll notify them by email. And don't worry, you can add (or remove) players from your campaign later on as well.</label><br><br>

					<?php
						foreach($users as $user) {
							if ($user['id'] != $login_id) {
								echo '
									<div class="row">
										<label class="toggle-switchy" for="'.$user['id'].'" data-size="sm" data-text="false">
											<input class="user-toggle" type="checkbox" id="'.$user['id'].'">
											<span class="toggle">
												<span class="switch"></span>
											</span>
										</label>
										<label class="control-label" for="'.$user['id'].'">'.$user['email'].'</label>
									</div>
								';
							}
						}
					?>

					<br><label class="control-label">You can also invite new players by entering their email addresses below. Once they complete the registration process they will automatically be added to your campaign. <strong><i>Separate emails with a comma.</i></strong> </label><br><br>

					<textarea class="form-control" id="email_invites"></textarea>

					<div class="button-bar">
						<button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#new_campaign_modal">Back</button>
						<button type="button" class="btn btn-primary" data-dismiss="modal" id="next_btn_2" data-toggle="modal" data-target="#confirm_nerd_modal">Next</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- confirm nerd modal -->
	<div class="modal" id="confirm_nerd_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content searching-prompt">
				<div class="modal-header">
					<h4 class="modal-title">New Campaign</h4>
				</div>
				<div class="modal-body">
					<h5>Ok, let's just make sure you're authorized to be here.</h5>
					<label class="control-label">What's the secret nerd word, nerd?</label>
					<input class="form-control" type="text" id="secret_word">
					<div class="button-bar">
						<button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#add_users_modal">Back</button>
						<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="createCampaign()">Create Campaign</button>
					</div>
				</div>
			</div>
		</div>
	</div>

</body>

<!-- JavaScript -->
<script async src="https://www.google.com/recaptcha/api.js?render=6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi"></script>
<script src="/assets/jquery/jquery-3.5.1.min.js"></script>
<script src="/assets/bootstrap/js/bootstrap.min.js"></script>
<script src="/assets/font-awesome/font-awesome-6.1.1-all.min.js"></script>
<script type="text/javascript">

	let user_ids = <?php echo json_encode($user_ids); ?>;
	let login_campaigns = <?php echo json_encode($login_campaigns); ?>;
	let campaign_names = <?php echo json_encode($campaign_names); ?>;
	let users = [];

	$("#campaign_name").on("input", function(){
		$("#next_btn").attr("disabled", $(this).val() == "");
	});

	// enable/disable button on checkbox toggle
	$(".user-toggle").on("change", function() {
		if (this.checked) {
			users.push(this.id);
		} else {
			var index = users.indexOf(this.id);
			if (index !== -1) {
				users.splice(index, 1);
			}
		}
		// $("#next_btn_2").attr("disabled", users.length == 0);
	});

	// on campaign select - redirect to campaign page
	function setCampaign(e) {
		if ($(e).val() == "") {
			return;
		}

		// check if user is campaign admin
		var role = 0;
		for (var i in login_campaigns) {
			if (login_campaigns[i]['campaign_id'] == $(e).val() && login_campaigns[i]['campaign_role'] == 1) {
				role = 1;
			}
		}

		// look for character id to set
		var user_id = 0;
		for (var i in user_ids) {
			if (user_ids[i]['campaign_id'] == $(e).val()) {
				user_id = user_ids[i]['id'];
				break;
			}
		}

		// finalize redirect url
		var redirect = "/?campaign="+$(e).val();
		if (role == 1 && user_id == 0) {
			redirect = "/admin.php?campaign="+$(e).val();
		} else if (user_id != 0) {
			redirect += "&user="+user_id;
		}

		window.location.href = redirect;
	}

	function createCampaign() {
		// make sure campaign name doesn't already exist
		// TODO this might be confusing if the user can't see the other campaign names...
		// only need to check against campaigns where user is admin?

		for (var i in campaign_names) {
			if (campaign_names[i] == $("#campaign_name").val()) {
				alert("Campaign name already in use");
				return;
			}
		}
		
		// submit new campaign to ajax
		$.ajax({
			url: '/scripts/new_campaign.php',
			data: { 
				'name' : $("#campaign_name").val(),
				'users' : users,
				'admin_id' : $("#login_id").val(),
				'secret_word': $("#secret_word").val().toLowerCase().trim()
			},
			ContentType: "application/json",
			type: 'POST',
			success: function(response) {
				if (response != 0) {
					let campaign_id = response;

					// check for invites
					let invites = $("#email_invites").val().split(",");
					for (var i in invites) {
						if (invites[i] != "") {
							// validate email and send invite
							let email = invites[i].trim();
							if (validateEmail(email)) {
								// submit to ajax
								$.ajax({
									url: '/scripts/send_invite.php',
									data: {'email':email, 'campaign_id':campaign_id, 'login_id':$("#login_id").val()},
									ContentType: "application/json",
									type: 'POST',
									success: function(response){
										if (response == 1) {
											// email ok
										}
									}
								});
							} else {
								// invalid email address
							}
						}
					}

					// redirect to campaign admin page
					window.location.href = "/admin.php?campaign="+campaign_id;
				} else {
					alert("Sorry nerd, that's not it.");
				}
			}
		});
	}

	function validateEmail(email) {
		return String(email)
		.toLowerCase()
		.match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
	};

</script>