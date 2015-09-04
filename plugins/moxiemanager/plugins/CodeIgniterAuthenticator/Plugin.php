<?php
/**
 * Plugin.php
 *
 * Copyright 2003-2014, Moxiecode Systems AB, All rights reserved.
 */

$baseDir = dirname(MOXMAN_ROOT);
for ($i = 0; $i < 10 && !file_exists($baseDir . "/application"); $i++) {
	$baseDir = dirname($baseDir);
}

if (!file_exists($baseDir . "/application")) {
	die("Could not find CodeIgniter as a parent path of moxiemanager.");
}

chdir($baseDir);

define('ENVIRONMENT', MOXMAN::getConfig()->get("CodeIgniterAuthenticator.environment", "development"));

$system_path = 'system';
$application_folder = 'application';

if (realpath($system_path) !== FALSE) {
	$system_path = realpath($system_path) . '/';
}

$system_path = rtrim($system_path, '/') . '/';

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('EXT', '.php');
define('BASEPATH', str_replace("\\", "/", $system_path));
define('FCPATH', str_replace(SELF, '', __FILE__));
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));
define('CI_VERSION', '2.1.4');
define('CI_CORE', FALSE);
define('APPPATH', $application_folder . '/');

require(BASEPATH . 'core/Common.php');
require(APPPATH . 'config/constants.php');
require(BASEPATH . 'libraries/Session.php');

if (!is_php('5.3')) {
	@set_magic_quotes_runtime(0);
}

$CFG =& load_class('Config', 'core');
$UNI =& load_class('Utf8', 'core');
$IN	=& load_class('Input', 'core');

require BASEPATH . 'core/Controller.php';

function &get_instance() {
	return CI_Controller::get_instance();
}

$controller = new CI_Controller();

/**
 * This class handles MoxieManager CodeIgniterAuthenticator session authentication.
 */
class MOXMAN_CodeIgniterAuthenticator_Plugin implements MOXMAN_Auth_IAuthenticator {
	public function authenticate(MOXMAN_Auth_User $user) {
		$config = MOXMAN::getConfig();
		$session = new CI_Session();

		// Check logged in key
		$sessionValue = $session->userdata($config->get("CodeIgniterAuthenticator.logged_in_key", "loggedin"));
		if (!$sessionValue || $sessionValue === "false") {
			return false;
		}

		// Extend config with session prefixed sessions
		$sessionConfig = array();
		$configPrefix = $config->get("CodeIgniterAuthenticator.config_prefix", "moxiemanager");
		if ($configPrefix) {
			$allData = $session->all_userdata();
			foreach ($allData as $key => $value) {
				if (strpos($key, $configPrefix) === 0) {
					$sessionConfig[substr($key, strlen($configPrefix) + 1)] = $value;
				}
			}
		}

		// Extend the config with the session config
		$config->extend($sessionConfig);

		// Replace ${user} with all config items
		$key = $config->get("CodeIgniterAuthenticator.user_key");
		if ($key) {
			$value = $session->userdata($key);
			$config->replaceVariable("user", $value);
			$user->setName($value);
		}

		return true;
	}
}

MOXMAN::getAuthManager()->add("CodeIgniterAuthenticator", new MOXMAN_CodeIgniterAuthenticator_Plugin());

?>