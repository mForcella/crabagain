<?php

	// establish database connection
	include_once('db_config.php');
	include_once('keys.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// get campaign list for dropdown nav
	$campaigns = [];
	$sql = "SELECT * from campaign ORDER BY name";
	$result = $db->query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($campaigns, $row);
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

<style type="text/css">

	.container {
		width: 300px;
		text-align: center;
		margin-top: 50px;
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
</style>

<body>
	<div class="container">
		<label class="control-label">Select your campaign</label>
		<select class="form-control" onchange="setCampaign(this)">
			<option value=""></option>
			<?php
				foreach($campaigns as $option) {
					echo '<option value='.$option['id'].'>'.$option['name'].'</option>';
				}
			?>
		</select>
		<button class="btn btn-primary" type="button" data-toggle="modal" data-target="#new_campaign_modal"><i class="fa-solid fa-plus"></i> New Campaign!</button>
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
						<button type="button" class="btn btn-primary" data-dismiss="modal" disabled id="next_btn" data-toggle="modal" data-target="#set_passwords_modal">Next</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- set passwords modal - admin password & new character password -->
	<div class="modal" id="set_passwords_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content searching-prompt">
				<div class="modal-header">
					<h4 class="modal-title">New Campaign</h4>
				</div>
				<div class="modal-body">
					<label class="control-label">Please set an admin password for your campaign. This grants you GM privileges to edit and save characters.</label>
					<input class="form-control" type="text" id="admin_password">
					<h5><strong>Important! Make sure you retain this password for your records!</strong></h5>
					<div class="button-bar">
						<button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#new_campaign_modal">Back</button>
						<button type="button" class="btn btn-primary" data-dismiss="modal" disabled id="next_btn_2" data-toggle="modal" data-target="#confirm_nerd_modal">Next</button>
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
						<button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#set_passwords_modal">Back</button>
						<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="createCampaign()">Create Campaign</button>
					</div>
				</div>
			</div>
		</div>
	</div>

</body>

<!-- JavaScript -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script async src="https://www.google.com/recaptcha/api.js?render=6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript">

	var keys = <?php echo json_encode($keys); ?>;

	$("#campaign_name").on("input", function(){
		$("#next_btn").attr("disabled", $(this).val() == "");
	});

	$("#admin_password").on("input", function(){
		$("#next_btn_2").attr("disabled", $(this).val() == "");
	});

	// on campaign select - redirect to campaign page
	function setCampaign(e) {
		window.location.replace("/?campaign="+$(e).val());
	}

	function createCampaign() {
		// check secret word
		if ($("#secret_word").val().toLowerCase() == keys['nerd_test']) {
			// submit new campaign to ajax
			$.ajax({
			  url: 'new_campaign.php',
			  data: { 
			  	'name' : $("#campaign_name").val(),
			  	'admin_password' : $("#admin_password").val()
			  },
			  ContentType: "application/json",
			  type: 'POST',
			  success: function(response) {
			  	if (response != 0) {
					// redirect to new campaign page
					window.location.replace("/?campaign="+response);
			  	} else {
			  		// handle error?
			  	}
			  }
			});
		} else {
			alert("Sorry nerd, that's not it.");
		}
	}

</script>