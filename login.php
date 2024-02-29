<?php

	session_set_cookie_params(604800);
	session_start();

	// check for existing login session
	if (isset($_SESSION['login_id'])) {
    	// redirect to campaign select page
    	header('Location: /select_campaign.php');
	}

	// check for session messages
	if (isset($_SESSION['msg'])) {
		echo $_SESSION['msg'];
		unset($_SESSION['msg']);
	}

	// establish database connection
	include_once('config/db_config.php');
	include_once('config/keys.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	$email = isset($_POST['email']) ? $_POST['email'] : "";
    $password = isset($_POST['password']) ? $_POST['password'] : "";

    if ($email != "" && $password != "") {

    	//to prevent from mysqli injection  
        $email = stripcslashes($email);
        $password = stripcslashes($password);
        $email = mysqli_real_escape_string($db, $email);
        $password = mysqli_real_escape_string($db, $password);

		$login = [];
		$sql = "SELECT * from login WHERE email = '$email'";
		$result = $db->query($sql);
		if ($result->num_rows === 1) {
			while($row = $result->fetch_assoc()) {
				$login = $row;
				if ($login['confirmed'] == 0) {
		            echo "<h4 class='warning'>Account not confirmed</h4>";
				// } else if ($password == "crabrangoon" || password_verify($password, $login['password'])) {
				} else if (password_verify($password, $login['password'])) {
		        	// set session values (login_id)
		        	$_SESSION['login_id'] = $login['id'];
		        	// redirect to most recently updated character page if available
		        	$sql = "SELECT id, campaign_id FROM user WHERE login_id = ".$login['id']." ORDER BY created_at ASC";
					$result = $db->query($sql);
					if ($result->num_rows > 0) {
						while($row = $result->fetch_assoc()) {
		        			header('Location: /?campaign='.$row['campaign_id'].'&user='.$row['id']);
						}
					}
					// no characters found - show campaign select page
					else {
		        		header('Location: /select_campaign.php');
					}
				} else {
		            echo "<h4 class='warning'>Invalid password</h4>";
				}
			}
		} else {
            echo "<h4 class='warning'>Invalid email address</h4>";
        }
    };

	$db->close();

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, height=device-height,  initial-scale=1.0, user-scalable=no, user-scalable=0"/>
	<meta name="robots" content="noindex">
	<meta property="og:image" content="https://crabagain.com/assets/image/treasure-header-desaturated.jpg">
	<title>Log In</title>
	<link rel="icon" type="image/png" href="/assets/image/favicon-pentacle.ico"/>

	<!-- Bootstrap -->
	<link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<!-- <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"> -->
	<!-- jQuery UI -->
	<!-- <link rel="stylesheet" type="text/css" href="/assets/jquery/jquery-ui-1.12.1.min.css"> -->
	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Alegreya:ital,wght@0,400;1,400;1,600&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
	<!-- Custom Styles -->
	<link rel="stylesheet" type="text/css" href="<?php echo $keys['styles'] ?>">
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
	.container select, .container button, .container input, .container label {
		position: relative;
	}
	.container-row {
		margin-top: 25px;
	}
	.link-container {
		display: block;
		text-align: center;
		margin: 30px;
	}
	.link {
		color: #7a7a7a;
		cursor: pointer;
	}
	.link:hover {
		color: black;
		text-decoration: none;
	}
	.warning {
		text-align: center;
		background-color: #f5abab;
		margin: 0;
		padding: 20px;
	}
	.success {
		text-align: center;
		background-color: #abcaf5;
		margin: 0;
		padding: 20px;
	}
	
</style>

<body>
	<div class="container">
		<form action="" method="POST">
			<div>
				<label class="control-label">Email</label>
				<input class="form-control" type="email" name="email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ""; ?>">
			</div>
			<div class="container-row">
				<label class="control-label">Password</label>
				<input class="form-control" type="password" name="password" required>
			</div>
			<button class="btn btn-primary" type="submit">Login</button>
		</form>
	</div>
	<div class="link-container">
		<a class="link" href="/register.php">Register a new account</a>
	</div>
	<div class="link-container">
		<span class="link" data-toggle="modal" data-target="#forgot_password_modal">Forgot your password?</span>
	</div>



	<!-- forgot password modal -->
	<div class="modal" id="forgot_password_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Reset My Password</h4>
				</div>
				<div class="modal-body center">
					<label class="center">Enter your email address below</label>
					<input type="email" class="form-control" id="email">
					<div class="button-bar">
						<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="forgotPassword()">Reset Password</button>
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
<script type="text/javascript">

	function forgotPassword() {
		// send ajax post with email address
		$.ajax({
			url: '/scripts/email_password_reset_link.php',
			data: { 'email' : $("#email").val() },
			ContentType: "application/json",
			type: 'POST',
			success: function(response) {
				if (response == 1) {
					alert("A password reset link has been sent to your email address");
				} else {
					alert("No account found with that email address");
				}
			}
		});

	}

</script>