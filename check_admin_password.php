<?php

	$password = $_POST['password'];
	$admin_password = $_POST['admin_password'];
	$hashed_password = $_POST['hashed_password'];

	if(($hashed_password != "" && $hashed_password == $admin_password) || password_verify(trim($password), $admin_password)) {
		echo 1;
	} else {
		echo 0;
	}

?>