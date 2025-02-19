<?php

session_set_cookie_params(604800);
ini_set('session.cookie_lifetime', 604800);
ini_set('session.gc_maxlifetime', 604800);
$doc_root = dirname(__FILE__);
$path = str_replace('/public_html', '', $doc_root).'/session';
ini_set('session.save_path', $path);
session_start();

// Initialize PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

// establish database connection
include_once('config/email_config.php');
include_once('config/db_config.php');
include_once('config/keys.php');
$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

$email = isset($_POST['email']) ? $_POST['email'] : "";
$password = isset($_POST['password']) ? $_POST['password'] : "";
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : "";
$invite_code = isset($_GET['invite']) ? $_GET['invite'] : "";

// get email and invitation from invite code
$invitation;
if ($invite_code != "") {
	$sql = "SELECT * FROM invitation WHERE invite_code = '$invite_code'";
	$result = $db->query($sql);
	while ($row = $result->fetch_assoc()) {
		$invitation = $row;
		$email = $row['email'];
	}
}

if ($email != "" && $password != "" && $password_confirm != "") {

	// get reCAPTCHA score from Google
	$recaptcha_secret = $keys['recaptcha_secret'];
	$recaptcha_response = $_POST['recaptcha_response'];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => $recaptcha_secret, 'response' => $recaptcha_response)));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$arrResponse = json_decode($response, true);
	// check response values
	if($arrResponse["success"] == '1' && $arrResponse["action"] == 'new_user' && $arrResponse["score"] >= 0.5) {
		// all good - continue with registration

		//to prevent from mysqli injection  
		$email = stripcslashes($email);
		$password = stripcslashes($password);
		$password_confirm = stripcslashes($password_confirm);
		$email = mysqli_real_escape_string($db, $email);
		$password = mysqli_real_escape_string($db, $password);
		$password_confirm = mysqli_real_escape_string($db, $password_confirm);

		// make sure email not already in use
		$count = 0;
		$sql = "SELECT count(*) as count FROM login WHERE email = '$email'";
		$result = $db->query($sql);
		while ($row = $result->fetch_assoc()) {
			$count = $row['count'];
		}
		if ($count > 0) {
			echo "<h4 class='warning'>Email, $email, already in use</h4>";
		}
		// make sure passwords match
		else if ($password != $password_confirm) {
			echo "<h4 class='warning'>Passwords must match</h4>";
		}
		// test email validity
		else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			echo "<h4 class='warning'>Not a valid email address</h4>";
		}
		// all good - create new account
		else {
			// generate confirmation code
			$confirmation_code = bin2hex(random_bytes(16));
			$hashed_password = password_hash($password, PASSWORD_DEFAULT);

			// insert new login
			$sql = "INSERT into login (email, password, confirmation_code) VALUES ('$email', '$hashed_password', '$confirmation_code')";
			$db->query($sql);
			$login_id = $db->insert_id;

			$save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'register.php', 'insert', $login_id)";
			$db->query($save_sql);

			// check for invite code - add user to campaign
			if ($invite_code != "") {
				$sql = "INSERT into login_campaign (login_id, campaign_id, campaign_role) VALUES (".$db->insert_id.", ".$invitation['campaign_id'].", 2)";
				$db->query($sql);

				$save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'register.php', 'insert', $login_id)";
				$db->query($save_sql);

				// delete invitation
				$sql = "DELETE FROM invitation WHERE id = ".$invitation['id'];
				$db->query($sql);

				$save_sql = "INSERT INTO sql_query (query, source, type, login_id) VALUES ('".addslashes($sql)."', 'register.php', 'delete', $login_id)";
				$db->query($save_sql);
			}

			// generate confirmation link
			$protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
			$domain = $_SERVER['HTTP_HOST'];
			$url = "<a href='".$protocol.$domain."/scripts/confirm_account.php?confirmation_code=".$confirmation_code."'>Confirm Your Account</a>";

			// email me with confirmation link
			$mail = new PHPMailer;
			// $mail->SMTPDebug = 1;
			$mail->Host = $email_config['host'];
			$mail->Port = $email_config['port'];
			$mail->SMTPSecure = 'tls';
			$mail->isSMTP();
			$mail->SMTPAuth = true;
			$mail->Username = $email_config['user'];
			$mail->Password = $email_config['password'];
			$mail->setFrom($mail->Username, "Gary, Dark Lord of Crabs");
			// $mail->addAddress('michael.forcella@gmail.com');
			$mail->addAddress($email);
			$mail->Subject = 'CrabAgain.com - Confirm Your Account';
			$msg = "Please follow the link below to confirm your account.<br>".$url;
			$mail->msgHTML($msg);
			$mail->send();

			// redirect to login page with success message
			$db->close();
			$_SESSION['msg'] = "<h4 class='success'>Success! Please check your email to confirm your account.</h4>";
			header('Location: /login.php');
		}

	} else {
		// Score less than 0.5 indicates suspicious activity. Return an error
		$db->close();
		$error_output = $response;
		$_SESSION['msg'] = "<h4 class='success'>Something went wrong. Please try again later</h4>";
		header('Location: /login.php');
		// $error_output = "Something went wrong. Please try again later";
	  // die($error_output);
	}

}

// $db->close();

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, height=device-height,  initial-scale=1.0, user-scalable=no, user-scalable=0"/>
	<meta name="robots" content="noindex">
	<meta property="og:image" content="https://crabagain.com/assets/image/treasure-header-desaturated.jpg">
	<title>New Account</title>
	<link rel="icon" type="image/png" href="/assets/image/favicon-pentacle.ico"/>

	<!-- Bootstrap -->
	<link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
	}
	.link:hover {
		color: black;
	}
	.warning {
		text-align: center;
		background-color: #f5abab;
		margin: 0;
		padding: 20px;
	}
	
</style>

<body>
	<div class="container">
		<form action="" method="POST" id="register_form">
			<div>
				<label class="control-label">Email</label>
				<input class="form-control" type="text" name="email" required value="<?php echo $email ?>" <?php echo $invite_code == "" ? "" : "disabled" ?>>
			</div>
			<div class="container-row">
				<label class="control-label">Password</label>
				<input class="form-control" type="password" name="password" required>
			</div>
			<div class="container-row">
				<label class="control-label">Confirm Password</label>
				<input class="form-control" type="password" name="password_confirm" required>
			</div>
			<input type="hidden" id="invite_code" value="<?php echo $invite_code ?>">
			<input type="hidden" name="recaptcha_response" id="recaptcha_response">
			<button class="btn btn-primary" type="button" onclick="registerForm()">Register</button>
		</form>
	</div>
	<div class="link-container">
		<a class="link" href="/login.php">Already registered? Login!</a>
	</div>

  <div class="modal" id="new_register_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">New Registration</h4>
        </div>
        <div class="modal-body">
        	<h5 class="center">Before we can let you pass, we just need to make sure you're not a robot, or a Russki, or both. Please enter the most secret of the secret codes.</h5>
        	<input class="form-control" type="text" name="nerd_test" id="nerd_test">
        	<div class="button-bar">
		        <button type="button" class="btn btn-primary" id="" data-dismiss="modal" onclick="newRegister()">Ok</button>
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

	function registerForm() {
		// check for invite code
		let inviteCode = $("#invite_code").val();
		// if no code, require secret word
		if (inviteCode == "") {
			$("#new_register_modal").modal("show");
		} else {
			// submit form
			$("#register_form").submit();
		}
	}

	// validate the form
	function newRegister() {
		// check secret code in ajax
		$.ajax({
			url: '/scripts/check_secret_word.php',
			data: { 'secret_word' : $("#nerd_test").val().toLowerCase().trim() },
			ContentType: "application/json",
			type: 'POST',
			success: function(response) {
				if (response == 1) {
					// $("#register_form").submit();
					// get recaptcha token before submit
					grecaptcha.ready(function () {
						grecaptcha.execute('6Lc_NB8gAAAAAF4AG63WRUpkeci_CWPoX75cS8Yi', { action: 'new_user' }).then(function (token) {
							$("#recaptcha_response").val(token);
							$("#register_form").submit();
						});
					});
				} else {
					alert("That's not the secret word, nerd");
				}
			}
		});
	}

</script>