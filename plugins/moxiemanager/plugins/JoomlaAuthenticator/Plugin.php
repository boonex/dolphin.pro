<?php
/**
 * Plugin.php
 *
 * @author Moxiecode Systems AB
 * @copyright Copyright © 2013, Moxiecode Systems AB, All rights reserved.
 */

// Include Joomla bootstrap logic
chdir(MOXMAN_ROOT . "/../../../../../administrator");
define('_JEXEC', 1);
define('JPATH_BASE', getcwd());
define('DS', DIRECTORY_SEPARATOR);
require_once(JPATH_BASE . DS . 'includes' . DS . 'defines.php');
require_once(JPATH_BASE . DS . 'includes' . DS . 'framework.php');
chdir(MOXMAN_ROOT);

// This is a really ugly hack, blame Joomla
if (strpos($_SERVER['HTTP_REFERER'], "administrator/") > 1) {
	$app = JFactory::getApplication('administrator');	
} else {
	$app = JFactory::getApplication('site');
}

/**
 * This class handles Joomla Authentication.
 */
class MOXMAN_JoomlaAuthenticator_Plugin implements MOXMAN_Auth_IAuthenticator {
	public function authenticate(MOXMAN_Auth_User $user) {
		$config = MOXMAN::getConfig();
		$user = JFactory::getUser();

		// Not logged in
		if ($user->id == 0) {
			return false;
		}

		$config->replaceVariable("user", $user->username);

		return true;
	}
}

MOXMAN::getAuthManager()->add("JoomlaAuthenticator", new MOXMAN_JoomlaAuthenticator_Plugin());

?>