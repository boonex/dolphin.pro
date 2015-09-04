<?php
/**
 * ZendAuthenticator.php
 *
 * Copyright 2003-2014, Moxiecode Systems AB, All rights reserved.
 */

$zendLoaderPath = MOXMAN_Util_LibraryLocator::locate("ZendAuthenticator.library_path", array(
	"vendor/zendframework"
));

$cwd = getcwd();
chdir($zendLoaderPath . '/../../');
require 'init_autoloader.php';
Zend\Mvc\Application::init(require 'config/application.config.php');
chdir($cwd);

/*
 * This class handles authentication for the Zend 2 framework.
 */
class MOXMAN_ZendAuthenticator_Plugin implements MOXMAN_Auth_IAuthenticator {
	public function authenticate(MOXMAN_Auth_User $user) {
		$sessionContainerName = MOXMAN::getConfig()->get("ZendAuthenticator.session_container");

		if ($sessionContainerName) {
			$session = new Zend\Session\Container($sessionContainerName);
		} else {
			$session = new Zend\Session\Container();
		}

		$config = MOXMAN::getConfig();
		$loggedInKey = $config->get("ZendAuthenticator.logged_in_key", "loggedin");

		if (isset($session->{$loggedInKey}) && ($session->{$loggedInKey} === true || strtolower($session->{$loggedInKey}) === "true")) {
			// Extend config with session prefixed sessions
			$sessionConfig = array();
			$configPrefix = $config->get("ZendAuthenticator.config_prefix");
			if ($configPrefix) {
				foreach ($session as $key => $value) {
					if (strpos($key, $configPrefix) === 0) {
						$sessionConfig[substr($key, strlen($configPrefix) + 1)] = $value;
					}
				}
			}

			// Extend the config with the session config
			$config->extend($sessionConfig);

			// Replace ${user} with all config items
			$key = $config->get("ZendAuthenticator.user_key");
			if ($key && isset($session->{$key})) {
				$config->replaceVariable("user", $session->{$key});
				$user->setName($session->{$key});
			}

			return true;
		}

		return false;
	}
}

MOXMAN::getAuthManager()->add("ZendAuthenticator", new MOXMAN_ZendAuthenticator_Plugin());
?>