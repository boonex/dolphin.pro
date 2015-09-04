<?php
/**
 * Mime.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class will return mime types for files by extracting the extension and comparing it
 * to a Apache style mime types file.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_Mime {
	/** @ignore */
	private static $mimes = array();

	/**
	 * Returns the mime type of an path by resolving it agains a apache style "mime.types" file.
	 *
	 * @param string $path path to Map/get content type by
	 * @param String $mimeFile Absolute filepath to mime.types style file.
	 * @return String mime type of path or an empty string on failue.
	 */
	public static function get($path, $mimeFile = "") {
		$mime = "";
		$path = explode('.', $path);
		$ext = strtolower(array_pop($path));

		// Use cached mime type
		if (isset(self::$mimes[$ext])) {
			return self::$mimes[$ext];
		}

		// No mime type file specified
		if ($mimeFile === "") {
			$mimeFile = dirname(__FILE__) . '/mimes.txt';
		}

		// Open mime file and start parsing it
		if (($fp = fopen($mimeFile, "r"))) {
			while (!feof($fp)) {
				$line = trim(fgets($fp, 4096));
				$chunks = preg_split("/(\t+)|( +)/", $line);

				for ($i = 1, $l = count($chunks); $i < $l; $i++) {
					self::$mimes[$chunks[$i]] = $chunks[0];

					if (rtrim($chunks[$i]) == $ext) {
						$mime = $chunks[0];
					}
				}
			}

			fclose($fp);
		}

		if (!$mime) {
			$mime = "application/octet-stream";
		}

		return $mime;
	}
}

?>