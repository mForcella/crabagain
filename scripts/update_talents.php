<?php

	include_once('../config/keys.php');

	// $keys['feat_list'] = 'assets/feat_list_v24_07_25.json';
	$talents_file_location = $keys['feat_list'];
	// $talents_file_location = '../assets/talents.json';

	$talents = $_POST['talents'];

	// write talents to json file
	file_put_contents('../'.$talents_file_location, json_encode($talents, JSON_PRETTY_PRINT));

?>