<?php
	if (session_id() == '') {
		@session_start();
	}

	header("Content-type: application/json; charset=utf-8");

	$secretKey = ""; // Change this key to match the one in config

	if (!$secretKey) {
		die('{"error" : {"message" : "No secret key set.", "code" : 130}}');
	}

	if (!isset($_REQUEST["hash"]) || !isset($_REQUEST["seed"])) {
		die('{"error" : {"message" : "Error in input.", "code" : 120}}');
	}

	// Check authentication with your CMS
	if (!isset($_SESSION["isLoggedIn"]) || !$_SESSION["isLoggedIn"]) {
		die('{"error" : {"message" : "Not authenticated.", "code" : 180}}');
	}

	$hash = $_REQUEST["hash"];
	$seed = $_REQUEST["seed"];

	$localHash = hash_hmac('sha256', $seed, $secretKey);

	if ($hash == $localHash) {
		die(json_encode(array(
			"result" => array(
				// Override config options here
				//"filesystem.rootpath" => "/var/www"
			)
		)));
	} else {
		die('{"error" : {"message" : "Error in input.", "code" : 120}}');
	}
?>