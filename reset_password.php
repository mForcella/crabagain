<?php

	session_set_cookie_params(604800);
	ini_set('session.cookie_lifetime', 604800);
	ini_set('session.gc_maxlifetime', 604800);
	$doc_root = dirname(__FILE__);
	$path = str_replace('/public_html', '', $doc_root).'/session';
	ini_set('session.save_path', $path);
	session_start();

	include_once('config/db_config.php');
	include_once('config/keys.php');

	// establish database connection
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check for form submission
	if (isset($_POST['password']) && isset($_POST['login_id'])) {
	    $password =  $_POST['password'];
	    $login_id =  $_POST['login_id'];
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);

	    // update password and reset token
	    $sql = "UPDATE login SET password = '$hashed_password', reset_token = '' WHERE id = $login_id";
	    $db->query($sql);
	    
		$save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'reset_password.php', 'update', $login_id)";
		$db->query($save_sql);

	    // redirect to login page
	    $_SESSION['msg'] = "<h4 class='success'>Your password has been successfully reset</h4>";
		header('Location: login.php');

	} else if (isset($_GET["token"])) {

		$login = [];
		$sql = "SELECT * from login WHERE reset_token = '".$_GET["token"]."'";
		$result = $db->query($sql);
		if ($result) {
			while($row = $result->fetch_assoc()) {
				$login = $row;
			}
		}
	} else {
		// no submission and no token found - redirect home
	    $_SESSION['msg'] = "<h4 class='warning'>Your reset link is invalid or expired</h4>";
		header('Location: login.php');
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
	<title>The Lost Password!</title>
	<link rel="icon" type="image/png" href="/assets/image/favicon-pentacle.ico"/>

	<!-- Bootstrap -->
	<link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<!-- <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"> -->
	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Alegreya:ital,wght@0,400;1,400;1,600&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
	<!-- Custom Styles -->
	<link rel="stylesheet" type="text/css" href="<?php echo $keys['styles'] ?>">

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

	<div>
		<form id="reset_form" method="POST" action="">
			<h4>Hey nerd!<br>Looks like you forgot your password! Let's set a new one for ya! Toot! Toot!</h4>
			<label class="control-label">New Password:</label>
			<input class="form-control" type="password" name="password" id="password" required>
			<label class="control-label">Confirm Password:</label>
			<input class="form-control" type="password" id="confirm_password" required>
			<input type="hidden" id="login_id" name="login_id" value="<?php echo isset($login['id']) ? $login['id'] : '' ?>">
			<button class="btn btn-primary" type="button" onclick="resetPassword()">Reset Password</button>
		</form>
	</div>

	<!-- JavaScript -->
	<script async src="https://www.google.com/recaptcha/api.js?render=6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi"></script>
	<script src="/assets/jquery/jquery-3.5.1.min.js"></script>
	<script src="/assets/bootstrap/js/bootstrap.min.js"></script>

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
				alert("The passwords entered do not match");
				$("#password").val("");
				$("#confirm_password").val("");
				return;
			}

			// submit form
			$("#reset_form").submit();
		}

	</script>

</body>
</html>