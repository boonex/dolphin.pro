<?php
/**
 * Plugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles MoxieManager SessionAuthenticator stuff.
 */
class MOXMAN_SessionAuthenticator_Plugin implements MOXMAN_Auth_IAuthenticator {
	public function authenticate(MOXMAN_Auth_User $user) {
		$config = MOXMAN::getConfig();
		$session = MOXMAN_Http_Context::getCurrent()->getSession();

		// Check logged in key
		$sessionValue = $session->get($config->get("SessionAuthenticator.logged_in_key"), false);
		if (!$sessionValue || $sessionValue === "false") {
			return false;
		}

		// Extend config with session prefixed sessions
		$sessionConfig = array();
		$configPrefix = $config->get("SessionAuthenticator.config_prefix");
		if ($configPrefix) {
			foreach ($_SESSION as $key => $value) {
				if (is_object($value)) {
					continue;
				}

				if (strpos($key, $configPrefix) === 0) {
					$sessionConfig[substr($key, strlen($configPrefix) + 1)] = $value;
				}
			}
		}

		// Extend the config with the session config
		$config->extend($sessionConfig);

		// Replace ${user} with all config items
		$key = $config->get("SessionAuthenticator.user_key");
		if ($key && isset($_SESSION[$key])) {
			$value = $session->get($key);
			if (is_string($value)) {
				$config->replaceVariable("user", $value);
				$user->setName($value);
			}
		}

		// The user is authenticated so let them though
		return true;
	}

	public static function startSession() {
		$sessionName = MOXMAN::getConfig()->get("SessionAuthenticator.session_name");
		if ($sessionName) {
			@session_name($sessionName);
		}

		if (session_id() == '') {
			@session_start();
		}
	}
}

MOXMAN::getAuthManager()->add("SessionAuthenticator", new MOXMAN_SessionAuthenticator_Plugin());
MOXMAN_SessionAuthenticator_Plugin::startSession();

?>