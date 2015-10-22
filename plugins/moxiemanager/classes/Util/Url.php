<?php
/**
 * Url.php
 *
 * Copyright (c) 1999-2015 Ephox Corp. All rights reserved
 */

/**
 * Url helper class.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_Url {
	public static function buildUrl($url) {
		$result = "";

		$result .= $url["scheme"] . "://";
		$result .= $url["host"];

		if ($url["scheme"] == "http") {
			$defaultPort = 80;
		} else if ($url["scheme"] == "https") {
			$defaultPort = 443;
		}

		if (isset($url["port"]) && $url["port"] != $defaultPort) {
			$result .= ":" . $url["port"];
		}

		if (isset($url["path"])) {
			$result .= $url["path"];
		}

		if (isset($url["query"])) {
			$result .= "?" . $url["query"];
		}

		if (isset($url["fragment"])) {
			$result .= "#" . $url["fragment"];
		}

		return $result;
	}
}

?>