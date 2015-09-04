<?php
/**
 * PathUtils.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class is an utility class for handling paths.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_PathUtils {
	/** @ignore */
	private static $sitePaths;

	/**
	 * Combines two paths into one path.
	 *
	 * @param string $path1 Path to be on the left side.
	 * @param string $path2 Path to be on the right side.
	 * @return String Combined path string.
	 */
	public static function combine($path1, $path2) {
		if (strlen($path2) === 0) {
			return self::toUnixPath($path1);
		}

		return preg_replace('/\\/$/', '', self::toUnixPath($path1)) . '/' . preg_replace('/^\\/|\\/$/', '', self::toUnixPath($path2));
	}

	/**
	 * Convert OS path into Unix style path.
	 *
	 * @param string $path Path to convert into Unix style.
	 * @return String Path converted into Unix style.
	 */
	public static function toUnixPath($path) {
		return str_replace('\\', '/', $path);
	}

	/**
	 * Returns an absolute path from a relative path relative to where the root of the application is.
	 * It will also convert any absolute path to unix style slashes like c:\ becomes c:/
	 *
	 * @param string $base Base path to combine the relative path with.
	 * @param string $path Path to convert into an absolute path.
	 * @return String Absolute file system path out of the relative path-
	 */
	public static function toAbsolute($base, $path) {
		$path = self::toUnixPath($path);

		// Check if the path is absolute already
		if (!preg_match('/^([^\\/]+\:|\\/)/', $path) || preg_match('/\\/\\.\\.?\\//', $path)) {
			$outputPathItems = array();
			$breakPoint = 0;
			$root = "";

			// Separate the root directory and the base path for the base
			$matches = array();
			if (preg_match('/^([^\\/]+\\:\\/|\\/)(.*)$/', $base, $matches)) {
				$root = $matches[1];
				$base = $matches[2];
			}

			// Split paths
			$base = preg_split('/\\//', self::toUnixPath($base), 0, PREG_SPLIT_NO_EMPTY);
			$path = preg_split('/\\//', $path, 0, PREG_SPLIT_NO_EMPTY);

			// Find break point and ignore . or empty
			for ($i = count($path) - 1; $i >= 0; $i--) {
				// Ignore .
				if ($path[$i] === ".") {
					continue;
				}

				// Is parent
				if ($path[$i] == '..') {
					$breakPoint++;
					continue;
				}

				// Move up
				if ($breakPoint > 0) {
					$breakPoint--;
					continue;
				}

				$outputPathItems[] = $path[$i];
			}

			$breakPoint = count($base) - $breakPoint;

			if ($breakPoint <= 0) {
				return $root . implode("/", array_reverse($outputPathItems));
			}

			// We are still inside the base
			return $root . self::combine(
				implode("/", array_slice($base, 0, $breakPoint)),
				implode("/", array_reverse($outputPathItems))
			);
		}

		return $path;
	}

	/**
	 * Returns the parent path of the specified path. An empty string will be returned
	 * when getting the parent of a root directory.
	 *
	 * @param string $path Parent path to get parent of.
	 * @return String Parent path of the specified path.
	 */
	public static function getParent($path) {
		$parent = self::toUnixPath(dirname($path));

		// Return empty string if the path didn't change for
		// example when getting a parent for a root path
		return $parent === $path ? "" : $parent;
	}

	/**
	 * Checks if the specified child is within the specified parent path. It will also
	 * return true if the child and parent is equal.
	 *
	 * @param string $child Child path to check if it's inside the parent.
	 * @param string $parent Parent path to check if the child is in.
	 * @return Boolean true/false if the child is within the parent.
	 */
	public static function isChildOf($child, $parent) {
		$child = strtolower(self::toUnixPath($child));
		$parent = strtolower(self::toUnixPath($parent));

		// It's the same path
		if ($parent === $child) {
			return true;
		}

		// If the parent is at the start of the child it's a valid child path
		$parent = self::combine($parent, "/");
		return strpos($child, $parent) === 0;
	}

	/**
	 * Returns the extension of a specified file path. The return string will be forced
	 * to lowercase. Example of output for "my.doc" is "doc".
	 *
	 * @param string $path Path to file to get the extension for.
	 * @return String Lowercase extension for the specified file or empty string if it wasn't found.
	 */
	public static function getExtension($path) {
		$path = basename($path);
		$pos = strrpos($path, '.');

		return $pos === false ? "" : strtolower(substr($path, $pos + 1));
	}

	/**
	 * getSitePaths
	 *
	 * @param string $file Site absolute path /var/www/dir/file
	 * @param string $script URL path. /dir/file
	 * @return Array With wwwroot and prefix.
	 */
	public static function getSitePaths($file = "", $script = "") {
		// @codeCoverageIgnoreStart
		if (self::$sitePaths && !defined('PHPUNIT')) {
			return self::$sitePaths;
		}
		// @codeCoverageIgnoreEnd

		// Check if we have a defined MOXMAN_API_FILE this might not be the case
		// if MOXMAN.php is loaded directly we then need to fallback to SCRIPT_FILENAME
		//if (!$file && defined("MOXMAN_API_FILE")) {
		//	$file = MOXMAN_API_FILE;
		//}

		$file = $file ? $file : MOXMAN_ROOT;
		$script = $script ? $script : dirname($_SERVER["SCRIPT_NAME"]);

		$file = explode("/", self::toUnixPath($file));
		$script = explode("/", self::toUnixPath($script));
		$u = count($file) - 1;
		for ($i = count($script) - 1; $i >= 0; $i--) {
			$val = $file[$u--];
			if (strtolower($val) != strtolower($script[$i])) {
				$u++; // To include last chunk
				break;
			}
		}

		$wwwroot = implode("/", array_slice($file, 0, $u + 1));
		$prefix = implode("/", array_slice($script, 0, $i + 1));

		self::$sitePaths = array(
			"wwwroot" => $wwwroot,
			"prefix" => $prefix
		);

		return self::$sitePaths;
	}

	/**
	 * Returns the configured temp dir or the system temp dir path.
	 *
	 * @param MOXMAN_Util_Config $config Optional config instance to get temp dir from.
	 * @return String Temp dir path.
	 */
	public static function getTempDir($config = null) {
		if (!$config) {
			$config = MOXMAN::getConfig();
		}

		// @codeCoverageIgnoreStart
		$path = $config->get("general.temp_dir");
		if (!$path) {
			$path = sys_get_temp_dir();

			// is_writeable can't be checked on temp dir.
		}
		// @codeCoverageIgnoreEnd

		return $path;
	}
}

?>