<?php
/**
 * AutoLoader.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

if (!defined('MOXMAN_CLASSES')) {
	/**
	 * @ignore
	 */
	define('MOXMAN_CLASSES', dirname(__FILE__));
}

// @codeCoverageIgnoreStart

/**
 * This class registers a class auto loader and loads classes.
 *
 * @package MOXMAN
 */
class MOXMAN_AutoLoader {
	/** @ignore */
	static private $prefixPaths = array();

	/**
	 * Adds a specific path for a class prefix.
	 *
	 * @param string $prefix Class prefix to load class by.
	 * @param string $path Path to where to look for files for a specific prefix.
	 */
	static public function addPrefixPath($prefix, $path) {
		self::$prefixPaths[$prefix] = $path;
	}

	/**
	 * Registers PHPParser_Autoloader as an SPL autoloader.
	*/
	static public function register() {
		//ini_set('unserialize_callback_func', 'spl_autoload_call');
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}

	/**
	 * Handles autoloading of classes.
	 *
	 * @param string $class A class name.
	 */
	static public function autoload($class) {
		if (strpos($class, 'MOXMAN_') !== 0) {
			return;
		}

		// TODO: Remove this in the future
		if (strpos($class, 'MOXMAN_Core_') === 0) {
			if (strpos($class, 'Args') !== false) {
				$prefix = "MOXMAN_Vfs_";
			} else if (strpos($class, 'Command') !== false) {
				$prefix = "MOXMAN_Commands_";
			} else if (strpos($class, 'Handler') !== false) {
				$prefix = "MOXMAN_Handlers_";
			} else {
				$prefix = "...";
			}

			throw new Exception("Use " . str_replace('MOXMAN_Core_', $prefix, $class) . " instead of " . $class);
		}

		// Load prefix specifc class for example plugin classes
		$prefix = substr($class, 0, strpos($class, '_', strlen('MOXMAN_')));
		if (isset(self::$prefixPaths[$prefix])) {
			require self::$prefixPaths[$prefix] . '/' . strtr(substr($class, strlen($prefix)), '_', '/') . '.php';
			return;
		}

		// Load core API class
		require MOXMAN_CLASSES . '/' . strtr(substr($class, strlen('MOXMAN_')), '_', '/') . '.php';
	}
}

// @codeCoverageIgnoreEnd

?>