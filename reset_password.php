<?php

	if (isset($_GET["token"])) {
		// establish database connection
		include_once('db_config.php');
		$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

		$user = [];
		$sql = "SELECT * from user WHERE reset_token = '".$_GET["token"]."'";
		$result = $db->query($sql);
		if ($result) {
			while($row = $result->fetch_assoc()) {
				$user = $row;
			}
		}
	} else {
		// no token found - redirect home
		header('Location: https://crabagain.com');
	}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, height=device-height,  initial-scale=1.0, user-scalable=no, user-scalable=0"/>
	<meta name="robots" content="noindex">
	<meta property="og:image" content="https://crabagain.com/assets/image/treasure-header-desaturated.jpg">
	<title>The Lost Password!</title>
	<link rel="icon" type="image/png" href="/assets/image/favicon.ico"/>

	<!-- Bootstrap -->
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Alegreya:ital,wght@0,400;1,400;1,600&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
	<!-- Custom Styles -->
	<link rel="stylesheet" type="text/css" href="/assets/style_v22_06_12.css">

	<style type="text/css">
		body {
			background-color: white;
		}
		body:before {
			background-image: url('assets/image/texture.jpeg');
			background-size: cover;
			content: ' ';
			position: absolute;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			opacity: 0.3;
		}
		form {
			max-width: 350px;
			margin: 50px auto;
			padding: 0 10px;
			text-align: center;
		}
		h4 {
			text-align: center;
			margin-bottom: 30px;
		}
		input {
			margin-bottom: 20px;
			position: relative;
		}
		button {
			background-color: gray !important;
			color: black !important;
			position: relative;
			margin: 5px;
		}
		
	</style>
</head>

<body>

	<div>
		<form id="reset_form">
			<h4>Hey <?php echo isset($user['character_name']) ? $user['character_name'] : "nerd" ?>!<br>Looks like you forgot your password! Let's set a new one for ya! Toot! Toot!</h4>
			<label class="control-label">New Password:</label>
			<input class="form-control" type="password" name="password" id="password">
			<label class="control-label">Confirm Password:</label>
			<input class="form-control" type="password" id="confirm_password">
			<input type="hidden" id="user_id" name="user_id" value="<?php echo isset($user['id']) ? $user['id'] : '' ?>">
			<input type="hidden" id="user_email" name="email">
			<button class="btn btn-primary" type="button" onclick="resetPassword()">Reset Password</button>
		</form>
	</div>

	<div class="modal" id="reset_modal" tabindex="-1" role="dialog">
	    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
		    <div class="modal-content searching-prompt">
		        <div class="modal-header">
		            <h4 class="modal-title">Reset Password</h4>
		        </div>
		        <div class="modal-body">
		        	<h4>Since you've proven yourself incapable of remembering strings of characters, if you'd like, you can enter your email address below and we can send you a copy of your new password.</h4>
		        	<input class="form-control" type="email" id="email">
		        	<div class="button-bar">
			        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="submitForm(false)">No thanks.<br>It won't happen again.</button>
			        	<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="submitForm(true)">Good idea!</button>
		        	</div>
		        </div>
		    </div>
	    </div>
	</div>

	<!-- JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script async src="https://www.google.com/recaptcha/api.js?render=6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js"></script>

	<script type="text/javascript">

		// token not valid - redirect home
		if ($("#user_id").val() == "") {
			alert("Sorry, looks like that reset token wasn't valid.");
			window.location.replace("https://crabagain.com");
		}

		function resetPassword() {
			// make sure password match
			var password = $("#password").val();
			var confirm_password = $("#confirm_password").val();
			if (password == "" || confirm_password == "") {
				alert("Please enter a password and confirmation value");
				return;
			}
			if (password != confirm_password) {
				alert("Sorry, those passwords don't match");
				$("#password").val("");
				$("#confirm_password").val("");
				return;
			}
			// show modal - ask for email
			$("#reset_modal").modal("show");
		}

		function submitForm(includeEmail) {
			if (includeEmail) {
				$("#user_email").val($("#email").val());
			}
			// submit form data to ajax
			$.ajax({
				url: 'submit_new_password.php',
				data: $("#reset_form").serialize(),
				ContentType: "application/json",
				type: 'POST',
				success: function(response){
					if (response == 'ok') {
						alert("Congrats! You've got a new password! Now go forth and adventure, ya dirty nerd!");
						window.location.replace("https://crabagain.com/?user="+$("#user_id").val());
					} else {
						console.log(response);
					}
				}
			});
		}

	</script>

</body>
</html>