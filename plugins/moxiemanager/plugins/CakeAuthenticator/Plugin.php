<?php
/**
 * Plugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

// Override this if you need to install the moxiemanager in some other directory
define('ROOT', dirname(dirname(MOXMAN_ROOT)));

define('APP_DIR', 'app');
define('DS', DIRECTORY_SEPARATOR);
define('WEBROOT_DIR', 'webroot');
define('WWW_ROOT', ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS);
define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'lib');

// Bootstrap Cake and load CakeSession
require_once(ROOT . DS . "lib/Cake/bootstrap.php");
App::uses('CakeSession', 'Model/Datasource');

/**
 * This class handles authentication with Cake PHP.
 */
class MOXMAN_CakeAuthenticator_Plugin implements MOXMAN_Auth_IAuthenticator {
	public function authenticate(MOXMAN_Auth_User $user) {
		$config = MOXMAN::getConfig();

		// Check logged in key
		$sessionValue = CakeSession::read($config->get("CakeAuthenticator.logged_in_key", "loggedin"));
		if (!$sessionValue || $sessionValue === "false") {
			return false;
		}

		// Extend config with session prefixed sessions
		$configPrefix = $config->get("CakeAuthenticator.config_prefix", "moxiemanager");
		if ($configPrefix && CakeSession::check($configPrefix)) {
			$configItems = CakeSession::read($configPrefix);
			$config->extend($this->flattenArray($configItems));
		}

		// Replace ${user} with all config items
		$key = $config->get("CakeAuthenticator.user_key");
		if ($key && CakeSession::check($key)) {
			$config->replaceVariable("user", CakeSession::read($key));
		}

		// The user is authenticated so let them though
		return true;
	}

	private function flattenArray($array, $prefix = "") {
		$result = array();

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$result = array_merge($result, $this->flattenArray($value, $prefix . $key . '.'));
			} else {
				$result[$prefix . $key] = $value;
			}
		}

		return $result;
	}
}

MOXMAN::getAuthManager()->add("CakeAuthenticator", new MOXMAN_CakeAuthenticator_Plugin());
?>