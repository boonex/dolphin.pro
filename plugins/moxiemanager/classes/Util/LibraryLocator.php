<?php
/**
 * LibraryLocator.php
 *
 * Copyright 2003-2014, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class locates libraries/frameworks by looking up the directory tree for matching paths.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_LibraryLocator {
	public static function locate($optionName, $pathLocations) {
		$rootPath = MOXMAN_ROOT;

		$fullPath = MOXMAN::getConfig()->get($optionName);
		if ($fullPath) {
			return $fullPath;
		}

		while ($rootPath) {
			foreach ($pathLocations as $path) {
				$fullPath = MOXMAN_Util_PathUtils::combine($rootPath, $path);

				if (file_exists($fullPath)) {
					return $fullPath;
				}
			}

			if (dirname($rootPath) === $rootPath) {
				break;
			}

			$rootPath = dirname($rootPath);
		}

		throw new MOXMAN_Exception("Error could not locate library/framework. Please configure: " . $optionName);
	}
}

?>