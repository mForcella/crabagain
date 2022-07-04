<?php

	$password = $_POST['password'];
	$admin_password = $_POST['admin_password'];

	if(password_verify(trim($password), $admin_password)) {
		echo 1;
	} else {
		echo 0;
	}

?>