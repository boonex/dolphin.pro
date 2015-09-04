<?php
/**
 * Uri.php
 *
 * Copyright 2003-2014, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class contains methods for handling Uri:s.
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_Uri {
	public static function escapeUriString($str) {
		$str = rawurlencode($str);
		$str = str_replace('%2F', '/', $str);

		return $str;
	}
}

?>