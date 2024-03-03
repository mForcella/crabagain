<?php

	include_once('../config/keys.php');

	$secret_word = $_POST['secret_word'];
	if ($secret_word == $keys['nerd_test']) {
		echo 1;
	} else {
		echo 0;
	}

?>