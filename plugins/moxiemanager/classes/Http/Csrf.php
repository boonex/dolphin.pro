<?php
/**
 * Csrf.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Cross site request forgery helper class.
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_Csrf {
	/**
	 * Creates a new auth token.
	 *
	 * @param String $salt Salt to create timed token.
	 * @return String New auth token based on time and salt.
	 */
	public static function createToken($salt) {
		return hash("sha256", $salt . round((time() - date("Z")) / 3600));
	}

	/**
	 * Verfies a token by checking it agains the time and salt.
	 *
	 * @param String $salt Salt to verify timed token.
	 * @param String $token Token to verify.
	 * @return Boolean True/false if the specified token is valid or not.
	 */
	public static function verifyToken($salt, $token) {
		$startHour = floor((time() - date("Z")) / 3600) - 2;

		for ($i = 0; $i < 4; $i++) {
			if ($token === hash("sha256", $salt . ($startHour + $i))) {
				return true;
			}
		}

		return false;
	}
}

?>